# Requirements — Backend Image Handling Optimization

## Introduction

Optimize image processing and delivery for Product and Gallery models to improve frontend performance while maintaining professional showcase quality. Currently, the system generates a single "optimized" WebP conversion at quality 90 with no size constraints. This feature implements responsive image sizes, improved compression, and local optimization to reduce file sizes by 20-30% while preserving visual quality.

## Alignment with Product Vision

From `specs/product.md`:
- **Image-heavy by nature** — galleries are the core content and primary conversion tool for the business
- **Image processing performance directly impacts frontend experience** — faster loads improve user engagement and SEO
- **Professional showcase** — must balance web performance with visual quality expectations for portfolio work
- **Target audiences** include wedding couples and professional clients who expect high-quality visual presentation

This optimization supports:
1. **Faster page loads** via responsive image sizes appropriate for viewport (thumb for listings, large for detail views)
2. **Reduced bandwidth costs** for hosting provider and users
3. **Improved SEO** through Core Web Vitals performance metrics
4. **Better mobile experience** by serving appropriately sized images

## Requirements

### REQ-1: Responsive Image Sizes

**User Story**: As a frontend developer consuming the API, I want multiple image sizes available for each media item, so that I can serve appropriately sized images based on viewport and layout context.

**Acceptance Criteria**:
- Product and Gallery models generate three image conversions for each uploaded media:
  - `thumb`: 400×400px, center-cropped, WebP quality 80, for admin lists and preview grids
  - `medium`: 1200px max width, WebP quality 85, for standard gallery displays
  - `large`: 2000px max width, WebP quality 85, for lightbox/detail views
- Original images above max width are downscaled proportionally (aspect ratio preserved except thumb)
- All conversions use WebP format
- All conversions processed synchronously (nonQueued) to maintain current behavior

### REQ-2: Improved Compression Quality

**User Story**: As a site administrator, I want images compressed more efficiently, so that storage costs decrease and page load times improve without visible quality loss.

**Acceptance Criteria**:
- Quality reduced from 90 to 85 for standard conversions (medium, large)
- Quality 80 for thumbnails where file size matters more than subtle detail
- Visual quality remains professional and suitable for portfolio/showcase use
- Expected file size reduction: 15-25% compared to quality 90

### REQ-3: Local Image Optimization

**User Story**: As a backend system, I want to apply additional lossless optimization to images, so that file sizes are minimized beyond basic WebP compression.

**Acceptance Criteria**:
- `spatie/image-optimizer` package installed and configured
- All media conversions use `->optimize()` method to apply optimization binaries (jpegoptim, pngquant, etc.)
- Optimization runs locally on server (no external API dependencies)
- Expected additional file size reduction: 5-10% beyond WebP quality compression
- Optimization failures do not block image upload (graceful degradation)

### REQ-4: Enhanced Media API Response

**User Story**: As a Nuxt frontend consuming gallery and product media, I want access to all available image sizes via the API, so that I can implement responsive `<picture>` elements with appropriate sources.

**Acceptance Criteria**:
- `MediaResource` returns URLs for all three conversion sizes plus original metadata
- API response includes:
  - `id`: Media item identifier
  - `name`: Original filename
  - `file_name`: Sanitized filename
  - `mime_type`: Image MIME type
  - `size`: Original file size in bytes
  - `urls`: Object containing:
    - `thumb`: URL to 400×400 thumbnail
    - `medium`: URL to 1200px image
    - `large`: URL to 2000px image
    - `original`: URL to original uploaded file
- Response structure compatible with existing frontend media consumption patterns

### REQ-5: Backwards Compatibility

**User Story**: As a developer maintaining this system, I want existing API consumers to continue functioning, so that the frontend does not break during migration.

**Acceptance Criteria**:
- Existing media uploaded before this change continue to work (graceful degradation if conversions missing)
- `MediaResource` returns fallback to original URL if a conversion is unavailable
- No breaking changes to API response structure (additions only, no removals or renames at root level)
- Existing `$media->getUrl('optimized')` calls continue to function (deprecated but not removed)

### REQ-6: Batch Regeneration Support

**User Story**: As a system administrator, I want to regenerate media conversions for existing uploads, so that all images benefit from the new optimization settings.

**Acceptance Criteria**:
- Artisan command `php artisan media-library:regenerate` available (provided by Spatie MediaLibrary)
- Command regenerates all conversions for existing Product and Gallery media
- Command provides progress feedback and error reporting
- Regeneration can be scoped to specific models or collections if needed

## Non-Functional Requirements

### Architecture
- Maintain single-action controller pattern — no changes to upload controllers required
- Media conversion logic isolated to model `registerMediaConversions()` methods
- `MediaResource` transformation is the single source of truth for API media responses
- Local image optimization binaries must be available on production server (document in deployment checklist)

### Performance
- Image conversions remain synchronous (nonQueued) to maintain predictable admin UX
- Total conversion time per upload should not exceed 5 seconds for typical 5MB source images
- Frontend benefits from responsive sizes should outweigh any marginal upload delay
- CDN/browser caching headers should be configured for all media URLs (separate concern)

### Security
- No changes to file upload validation (existing 10MB max, allowed MIME types remain)
- Uploaded media continues to be stored outside public web root via Spatie MediaLibrary
- URL signing for media access remains unchanged (if implemented)

### Reliability
- Image optimization failures (missing binaries, corrupt files) must not block image upload
- Graceful fallback: if optimization binary unavailable, proceed with WebP conversion only
- Test suite must verify all three conversions are generated for valid uploads
- Test suite must verify MediaResource returns expected URL structure

### Usability
- No admin interface changes required — conversions happen transparently
- Documentation updated in `CLAUDE.md` medialibrary-development skill reference
- Production deployment checklist includes verification of image optimization binaries
