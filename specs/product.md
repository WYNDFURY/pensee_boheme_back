# Product Vision — Pensée Bohème

## What It Is

Brochure + portfolio website for **Pensée Bohème**, a boutique eco-responsible florist run by Cécile Devaux in Beuzeville-la-Grenier, Normandy (76210). Appointment-only business, all creations made to order.

The site is **not** an e-commerce store. Its goal is to **showcase work, communicate values, and generate contact/booking inquiries**.

## Target Audiences

1. **Couples planning weddings** — primary audience. Looking for custom floral decoration, bouquets, venue setup. Package starts at ~900EUR.
2. **Bachelorette party organizers (EVJF)** — book creative workshops (min. 4 people) where participants make dried flower accessories.
3. **Professional clients** — shops, boutiques, enterprises wanting storefront decoration, seasonal arrangements, bouquet subscriptions, or team-building workshops.
4. **Eco-conscious consumers** — people who specifically seek sustainable, local, and seasonal floral options.

## Core Business Values

- **Eco-responsibility**: 70%+ vegetation from rational production, member of Collectif de la Fleur Française, minimal floral foam, waste reduction
- **Personalization**: every creation is bespoke, reflecting client personality and color choices
- **Artisanal craftsmanship**: boho-chic aesthetic, poetic and nature-inspired

## Service Lines

| Service | Description | Key Page |
|---------|-------------|----------|
| Weddings | Full decoration: bouquets, centerpieces, venue setup, custom accessories | `/univers/mariages` |
| Dried Flower Accessories | Handcrafted crowns, combs, hair accessories, jewelry | `/univers/accessoires-fleurs-sechees` |
| Guest Gifts | Personalized favors and floral tokens for events | `/univers/cadeaux-invites` |
| Creative Workshops | EVJF and corporate groups make accessories guided by Cécile | `/ateliers-creatifs` |
| Professional Services | Window displays, seasonal decoration, bouquet subscriptions, team-building | `/univers/professionnels` |
| Rentals | Arches, vases, candles, chandeliers — delivery/setup/removal included. Only available to floral service clients | `/locations` |

## Site Goals (in priority order)

1. **Build trust & showcase quality** — galleries are the primary conversion tool. Visitors see real work, get inspired, then contact.
2. **Communicate eco-responsibility** — the `/engagement` page is central to brand differentiation.
3. **Drive contact inquiries** — forms on `/infos-pratiques` and custom creation/event forms. The site converts visitors to appointment requests.
4. **SEO visibility** — rank for "fleuriste normandie", "décoration mariage normandie", "fleurs séchées normandie", "EVJF normandie". LocalBusiness schema, structured data on every page.
5. **Demonstrate professionalism** — the site itself is a signal of quality for a small artisan business.

## Brand Identity

- **Language**: French-only, no i18n planned
- **Tone**: Warm, poetic, professional. Nature metaphors welcome.
- **Fonts**: Josefin Slab (brand serif), Kumbh Sans (headings), Source Serif 4 (quotes)
- **Colors**: Cream background (`#FFF9F2`), green tones for nature, purple accent (`#603E65`), soft orange and pink
- **Aesthetic**: Bohemian, artisanal, organic — not corporate or minimalist

## Business Operations

- **Hours**: Mon-Fri 9h-16h (closed Wednesday), Sat 9h-13h, Sun closed
- **Model**: Appointment-only, commission-based
- **Contact**: 06 14 64 35 84 / penseeboheme76@gmail.com
- **Social**: Facebook + Instagram (feed integrated on site)
- **Geography**: Normandy primary (Le Havre, Rouen, Seine-Maritime). Travels to client venues for weddings and workshops.

## Technical Constraints

- Laravel 10 REST API serving the Nuxt 3 frontend (static site generation consumes the API at build time)
- Image storage and processing via Spatie MediaLibrary with automatic WebP conversion (50MB max upload)
- Authentication: Laravel Sanctum (admin-only, no public user accounts)
- No cart, no payment processing — the API serves content (galleries, products, categories, pages) and processes contact form submissions
- Instagram integration via Meta Graph API v22.0 with token refresh
- Email delivery via Mailgun (production) / Mailpit (dev) for contact form notifications
- Image-heavy by nature — galleries are the core content. Image processing, sizing, and delivery performance directly impact the frontend experience
