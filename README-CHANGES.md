# Storefront upgrade: Interactive Product Feature Hotspots + Quick View

## What this adds

Two things, working together:

1. **Hover hotspots on the product grid.** Hover a product card and numbered
   dots fade onto the thumbnail, each pointing at a real feature of that
   product. Hover a dot (or tap it on mobile) and a small callout names the
   feature, the same visual grammar as an exploded parts diagram, just
   overlaid on the actual product photo instead of a separate illustration.
2. **Quick View.** Click the product image (or the expand icon that appears
   on hover) to open a larger modal: full-size image with the same numbered
   dots, plus a matching list of every feature down the side. Hovering a
   list row highlights its dot on the image and vice versa.

Products with no hotspots defined render exactly as before — the layer
simply doesn't exist in the markup, so there's zero visual or performance
cost for products you haven't annotated yet.

## Why this shape, not a literal "exploded diagram"

Your reference image (the QR hologram label) is a 6-layer exploded diagram —
that works when you control every layer as a separate flat asset. A real
product photo (a shoe, a keyboard) doesn't have separate layer images, so
the honest equivalent is **annotation, not decomposition**: numbered
callouts pinned to real coordinates on the one photo you have, which is
also the pattern actual e-commerce sites use for this exact job (Apple's
product pages, Nike's "sustainable materials" callouts, etc). Quick View is
where the two ideas meet: it's the moment described as "click the product,
then hover to see the layer" — click opens the bigger canvas, hover reveals
each feature on it.

## Files

**New**
- `database/product_features.sql` — new `product_features` table (label,
  description, `pos_x`/`pos_y` as percentages so hotspots stay correctly
  placed at any image size) + optional demo seed data.
- `admin/save-feature.php` / `admin/delete-feature.php` — AJAX endpoints
  the admin hotspot editor calls.
- `shop/quickview-modal.php` — the Quick View markup, included once by
  each storefront page.

**Modified (additive — nothing existing was removed or restyled)**
- `includes/helpers.php` — added `getProductFeatures($pdo, $id)`.
- `assets/css/shop.css` — appended a hotspot + Quick View section at the
  bottom of the file, reusing your existing tokens (`--accent`, `--surface`,
  `--font-head`, etc.) rather than introducing new ones, so it reads as the
  same design system, not a bolted-on widget.
- `assets/js/shop.js` — appended the hotspot/Quick View interaction logic;
  the cart engine at the top is untouched.
- `shop/catalog.php`, `shop/index.php` — each product card now carries a
  `data-product` JSON attribute (name, price, description, features…) so
  Quick View opens instantly with no extra request, plus the hotspot layer
  markup on the thumbnail. The commented-out add-to-cart buttons were left
  exactly as they were.
- `products/edit.php` — added a "Feature hotspots" card: click the product
  image to drop a numbered pin, name it in a small popover, and it saves
  immediately via AJAX. Existing hotspots list below with delete buttons.

## Install

1. Run the new migration (after `ims.sql` and `shop.sql`):
   ```
   mysql -u root -p ims_db < database/product_features.sql
   ```
2. Drop in the modified/new files above at their matching paths in your
   repo root.
3. That's it — no config changes. Existing products with no hotspots are
   unaffected.

## Using it as an admin

Go to **Products → Edit** on any product that has an image. A new
"Feature hotspots" panel sits under the image uploader:

- Click anywhere on the image → a small form pops up → type a feature name
  (and optional short description) → **Add**.
- Each hotspot appears numbered on the image immediately and in the list
  below, where you can delete it.
- Open the storefront (Quick View or the catalog hover) to see it live —
  no caching, no rebuild step.

## What I verified before handing this off

I didn't just write this and hope — I stood up a real MySQL database, ran
all three SQL files against it, and served the actual PHP through PHP's
built-in server to click through it:

- `php -l` on every new/modified PHP file — no syntax errors.
- Logged in as the seeded admin, loaded the dashboard, products list, and
  **`products/edit.php`** for a product with hotspots, one without an
  image, and one with neither — all render 200 with no PHP warnings or
  fatals in the output.
- Loaded `shop/index.php` and `shop/catalog.php` (plain, filtered by
  category, and searched) and confirmed the hotspot markup, `data-product`
  JSON, and the Quick View modal's element IDs all come out correct and
  matched to what `shop.js` expects.
- Called `admin/save-feature.php` and `admin/delete-feature.php` for real
  over HTTP: a valid request inserts/removes the row in MySQL as expected;
  an unauthenticated request redirects instead of crashing; a forged CSRF
  token gets a 403; a blank label or an out-of-range position gets a clean
  JSON error instead of a fatal.
- Checked `shop.css` and `shop.js` for balanced braces/parens and ran
  `node --check` against the JS (including the PHP-rendered inline
  `<script>` blocks in `products/edit.php`) to confirm there's no stray
  syntax left over from the templating.

## A couple of things worth knowing

- **Mobile:** dots on the small grid thumbnail only reveal on hover, which
  touch devices don't reliably have — so on screens ≤768px the "N
  features" hint badge and the Quick View expand icon are always visible
  (not hover-gated), making Quick View the mobile entry point. Inside
  Quick View, dots are always shown at full size and tappable.
- **Pre-existing quirk, not something I introduced:** in the original
  `shop.css`, the "New" and "Low stock" badges both position at
  `top:12px;left:12px`, so a product that's both new and low-stock will
  show overlapping badges. Unrelated to this feature — flagging it in case
  you want it fixed separately.
