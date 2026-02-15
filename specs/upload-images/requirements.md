# Upload Images — Requirements

## 1. Introduction

Add image upload capability to the API so that authenticated admins can attach images when creating/updating Products and Galleries. Spatie MediaLibrary is already installed, configured, and integrated at the model level — but the Store/Update controllers do not accept file inputs. This feature bridges that gap.

## 2. Alignment with Product Vision

From `specs/product.md`:
- **"Galleries are the primary conversion tool"** — visitors see real work, get inspired, then contact. Uploading gallery images directly through the API is essential for content management.
- **"Image-heavy by nature — galleries are the core content. Image processing, sizing, and delivery performance directly impact the frontend experience"** — the upload pipeline must produce optimized WebP images via the existing conversion.
- Products ("Creations") are showcased with images — each product needs at least one image to serve its purpose on the site.

## 3. Requirements

### REQ-1: Upload a single image when creating a Product

**User Story:** As an admin, I want to attach an image when creating a product, so that the product is immediately visible with its image on the site.

**Acceptance Criteria:**
- `POST /api/products` accepts a `multipart/form-data` request with an `image` file field alongside existing JSON fields
- The uploaded file is added to the `product_images` media collection via Spatie MediaLibrary
- The `optimized` WebP conversion is generated automatically (existing `registerMediaConversions`)
- Validation: `image` field is optional, must be an image file (jpeg, png, webp, gif), max 10MB
- On success, the response includes the product with its media (using existing `ProductResource` + `MediaResource`)
- The image field is optional — products can still be created without an image

### REQ-2: Upload images when updating a Product

**User Story:** As an admin, I want to add or replace images when updating a product, so that I can keep product visuals current.

**Acceptance Criteria:**
- `PATCH /api/products/{product}` (changed to `POST` with `_method=PATCH` for multipart support) accepts an `image` file field
- When a new image is provided, it is added to the `product_images` collection
- Existing images are preserved unless explicitly removed (see REQ-5)
- Same validation rules as REQ-1

### REQ-3: Upload multiple images when creating a Gallery

**User Story:** As an admin, I want to upload multiple images at once when creating a gallery, so that I can quickly populate a photo gallery.

**Acceptance Criteria:**
- `POST /api/galleries` accepts `multipart/form-data` with an `images[]` array field
- Each uploaded file is added to the `gallery_images` media collection
- Validation: each file must be an image (jpeg, png, webp, gif), max 10MB per file, max 20 files per request
- On success, the response includes the gallery with all its media
- The images field is optional — galleries can be created without images

### REQ-4: Upload additional images when updating a Gallery

**User Story:** As an admin, I want to add more images to an existing gallery, so that I can expand it over time.

**Acceptance Criteria:**
- `PATCH /api/galleries/{gallery:slug}` (via `POST` with `_method=PATCH`) accepts `images[]`
- New images are appended to the existing `gallery_images` collection
- Existing images are preserved unless explicitly removed (see REQ-5)
- Same validation rules as REQ-3

### REQ-5: Delete individual media items

**User Story:** As an admin, I want to remove specific images from a product or gallery, so that I can curate the content.

**Acceptance Criteria:**
- `DELETE /api/media/{media}` endpoint removes a single media item
- Only authenticated admins can delete media (auth:sanctum)
- The media file and its conversions are removed from disk
- Returns 200 with confirmation message
- Returns 404 if media not found

### REQ-6: Media cleanup on model deletion

**User Story:** As an admin, when I delete a product or gallery, I expect all associated images to be cleaned up automatically.

**Acceptance Criteria:**
- When a Product is soft-deleted, its media is preserved (recoverable)
- When a Product is force-deleted, its media files are removed from disk
- When a Gallery is soft-deleted, its media is preserved
- When a Gallery is force-deleted, its media files are removed from disk
- Note: Spatie MediaLibrary handles this automatically via model events — verify it works correctly with soft deletes

## 4. Non-Functional Requirements

### Architecture
- Follow existing single-action controller pattern for the new `DELETE /api/media/{media}` route
- Keep upload logic inline in controllers (no service extraction needed — Spatie handles the complexity)
- Use `$request->validate()` inline as per existing convention (no Form Request classes)
- Add the media delete route in a new `routes/api/media.php` file, included from `routes/api.php`

### Performance
- Max file size per upload: 10MB (stricter than the 50MB MediaLibrary config — practical limit for web images)
- Max files per batch: 20 (prevents timeout on large gallery uploads)
- WebP conversions run synchronously (`nonQueued`) as already configured — acceptable for single/small batch uploads
- Ensure PHP `upload_max_filesize` and `post_max_size` are sufficient (document in README if needed)

### Security
- All upload/delete endpoints require `auth:sanctum` middleware (existing pattern)
- Validate MIME types server-side — never trust client-provided content type
- File size limits enforced both at validation and MediaLibrary config level
- No user-controlled file paths — Spatie's `DefaultPathGenerator` handles storage paths
- Throttle applies via existing `throttle:60,1` middleware

### Reliability
- Failed uploads (validation, disk errors) should not leave orphaned files
- If multiple images are uploaded and one fails validation, reject the entire request (atomic)
- Database transactions should wrap model creation + media attachment to prevent partial state

### Usability
- Error messages clearly indicate which file failed and why (e.g., "images.2 must be an image")
- Response format consistent with existing API patterns (`ProductResource`, `GalleryResource`)
- Support `multipart/form-data` with `_method=PATCH` override for update endpoints (standard Laravel approach for file uploads on PATCH/PUT)
