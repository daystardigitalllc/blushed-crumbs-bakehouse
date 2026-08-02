---
name: new-storefront-theme
description: Build a complete new Doughmain/blushed-crumbs-bakehouse storefront theme (blade files + storefront CSS + admin dashboard CSS) from a reference image or style description, fully wired into the existing theme system with no follow-up fixes needed. Use this whenever the user drops a screenshot, mockup, or description of a visual style and asks for a new theme, a new storefront design, a new template, or to "make it look like this" for the bakery storefront — even if they don't say the word "theme" explicitly. Also use when asked to fix theme-related bugs (a new theme's header/nav not overlaying correctly, admin dashboard buttons not matching the selected theme, colors not restyling on theme switch) since those bugs share root causes this skill documents.
---

# Building a new storefront theme

This skill exists because building a theme "by eye" reliably produces something that looks right in isolation but breaks in ways only visible after a real screenshot or a second look at the admin dashboard: an uneditable hardcoded section, a header that doesn't overlay the hero, an admin sidebar that's unreadable, buttons that silently ignore the new theme's color. Every gotcha below was hit for real in earlier sessions. Read this whole file before writing any code — the fixes are cheap up front and expensive after the theme is live.

## Before you touch anything: re-read the actual codebase

The file layout described here was accurate as of the last time this skill was updated, but this codebase's CSS architecture has already changed shape once mid-project (a single `style.css` became `storefront-base.css` + per-theme `css/themes/{id}.css` files). Don't trust this document over the live files. Before writing anything, read:

- `app/Models/Tenant.php` — `getAllThemes()`, `getStarterThemes()`, `getDefaultSectionSettings()`, `siteContentSchema()`, `getDefaultSiteContent()`, `themeView()`, `themeCssPath()`
- One existing theme closest in spirit to the reference (check `resources/views/storefront/themes/*/index.blade.php` — `playful_treats`, `sunny_whisk`, and `daily_batch` are good structural references)
- `public/css/storefront-base.css` (shared storefront CSS) and `public/css/style.css` (shared admin CSS) — specifically, search each for an existing theme's block to see the current pattern
- `resources/views/admin/dashboard.blade.php`'s "Admin dashboard, themed to match" comment blocks in `style.css` for an example admin override set

If any of the file paths or patterns below don't match what you find, trust the live code and adapt — then update this skill afterward so the next run doesn't repeat the mismatch.

## Step 1 — Get the spec

If the user hasn't given a theme name, id, or tier, don't block on asking — pick something fitting the reference image and proceed; tell them what you picked. Do ask about pro vs. starter tier specifically if it's genuinely unclear, since that's a real product decision, not a style detail (Pro is the default if unstated — most recently-added themes are Pro).

## Step 2 — Register the theme

Add an entry to `Tenant::getAllThemes()`: `id`, `name` (with an emoji, matching the existing style), `subtitle`, `preview_bg`, `preview_accent`. Leave it out of `getStarterThemes()`'s `$starterKeys` array for Pro tier; add it there for starter/free.

## Step 3 — Build the storefront

**Content rules — the single most important section here.** Every visible string on the new theme must trace back to one of:
- `$tenant->getSiteContent($key, $default)` — the key must already exist in `Tenant::siteContentSchema()`. If the design genuinely needs new copy that doesn't map to an existing key, add the key to `siteContentSchema()` *and* a sensible default to `getDefaultSiteContent()` before using it — never invent a key that only your new blade file knows about.
- The `Product` model (menu items), `GalleryItem` model (gallery photos), or `Review` model (homepage reviews, falls back to `site_content['reviews']` when there are no DB reviews yet).
- Tenant columns (`phone`, `address_line1/2`, `city`, `state`, `postal_code`, `instagram_url`, `facebook_url`, `logo_path`) — already editable via the "Business Info & SEO" card in the admin Settings tab.

The reason this matters: a hardcoded string looks identical to a themed one in a code review, but it's invisible and uneditable to the baker forever, and it's exactly how a real bug shipped once (an "About" section hardcoded outside the section loop for months). If the reference image shows something with no backing data model or registered section — a review-platform badge row, a structured per-day hours table — skip it rather than inventing fake data or a parallel storage mechanism, and say so when you report back.

**Homepage sections must loop, not hardcode.** `index.blade.php` must do:
```blade
@php $sections = $tenant->getOrderedSections(); @endphp
@foreach($sections as $secId => $sec)
    @if(!empty($sec['enabled']))
        @if($secId === 'hero') ... @elseif($secId === 'about') ... @endif
    @endif
@endforeach
```
Implement every section already in `Tenant::getDefaultSectionSettings()` (hero, about, highlights, promo_video, categories, whimsical, how_it_works, reviews, faq, cta_banner, featured_gallery as of this writing — check the live method) even if the reference image doesn't show all of them; reskin creatively to fit the new visual style rather than omitting sections, since a tenant may have any of them enabled. If the design genuinely calls for a section type that doesn't exist in the registry yet, that's a bigger change: add it to `getDefaultSectionSettings()`, add matching accordion fields to the Page Builder tab in `dashboard.blade.php`, add the field to `AdminController::saveSectionSettings()`'s content merge, and only then wire it into the template — in that order. Skipping straight to step 4 reproduces the exact bug this rule exists to prevent.

**Contact info** goes through `@include('storefront.partials.footer_nap')` in the footer, matching every current theme — don't build a custom contact block, the partial already reads the tenant columns above.

**Files to create:** `resources/views/storefront/themes/{theme_id}/index.blade.php`, `about.blade.php`, `menu.blade.php`, `gallery.blade.php`. `policy.blade.php`/`privacy.blade.php`/`terms.blade.php` are optional — missing ones fall back to `sweet_elegant`'s via `Tenant::themeView()`, which is normal and expected, not a gap to fill unless asked.

**CSS:** every theme's `<head>` loads `storefront-base.css` then `$tenant->themeCssPath()`. Create `public/css/themes/{theme_id}.css`, every rule scoped under `body.theme-{theme_id}`. Add a footer color-variable block to `storefront-base.css` (find the labeled per-theme section near the top — `--footer-bg`/`--footer-text`/`--footer-border-top`/`--footer-link-*`).

One specific known gap worth checking every time: `storefront-base.css` is the *intended* single source of shared layout (header, nav, base buttons), but at least once it was found to be missing the base `.site-header`/`.header-container`/`.nav-links` rules entirely — they'd only survived by accident, duplicated inside a couple of older themes' CSS files from before the CSS-split migration. If your new theme's header doesn't render as a proper flex nav bar overlaid transparently on the hero, this is the first thing to check — not something wrong with your new theme's own CSS. The overlay itself is a `:has()` selector list in `storefront-base.css`:
```css
.site-header:has(+ #storefront-view .hero-section),
.site-header:has(+ #storefront-view .playful-hero),
...
```
Add your new theme's hero wrapper class to that list.

## Step 4 — Theme the admin dashboard too

This is the step that's easy to forget entirely, because the admin portal is a completely separate CSS universe from the storefront. `resources/views/admin/dashboard.blade.php` extends `layouts/app.blade.php`, which loads *only* `public/css/style.css` — not `storefront-base.css`, not your new `css/themes/{id}.css` file. None of the storefront theming you just wrote reaches the admin dashboard. It needs its own block.

Add to `public/css/style.css` (search for an existing theme's `body.theme-{id} { --primary: ...; }` block to copy the pattern, then recolor):

- The variable block itself: `--primary`, `--primary-hover`, `--pink-bg`, `--dark-text`, `--theme-heading-font`, `--theme-card-bg`, `--theme-section-bg`, `--theme-accent-bg`. **Keep `--theme-accent-bg` dark** regardless of the theme's actual brand palette — `.admin-sidebar` hardcodes white sidebar text, so a light or bright accent-bg makes the whole sidebar unreadable. Let `--primary` and `--theme-section-bg` carry the theme's real color into the buttons, active nav tab, and main content background instead.
- If `--primary` ends up light or bright (e.g. a pastel or yellow), explicitly override text color to dark on `.admin-nav-item.active` and `.admin-sidebar-brand .badge-pro` — both hardcode white text against `var(--primary)`, which is illegible on a light color.
- **The one override every theme needs and it's easy to skip:** there's a global, non-scoped `.btn-primary { background: ... !important }` rule in `style.css` that every plain `<button class="btn btn-primary">` with no inline style falls back to — things like "Add Product to Catalog," "Block Date," bare Save buttons scattered across every tab. Without a matching override, these buttons silently stay pink regardless of the selected theme, and it's easy to miss because a handful of *other* buttons (the ones with their own inline `style="background:..."`) might look fine, creating a false impression that theming worked. Add:
  ```css
  body.theme-{id} .btn-primary,
  body.theme-{id} .nav-order-btn {
      background: var(--primary) !important;
      color: <dark-or-white, whichever contrasts>  !important;
      border-radius: 30px !important;
      box-shadow: ... !important;
  }
  body.theme-{id} .btn-primary:hover,
  body.theme-{id} .nav-order-btn:hover { background: var(--primary-hover) !important; ... }
  ```
- The rest of the standard admin override set, copied from an existing theme's "Admin dashboard, themed to match" block and recolored: `.admin-mobile-header`, `.admin-main-content input/select/textarea` (border + `:focus`), `.admin-main-content label`, `.status-select:focus`, `.btn-outline` (+ hover), `.reorder-btn` (+ hover), `.category-card-exact`/`.cloud-review-card`/`.category-image-frame`/`.form-builder-card`/`.order-card`/`.add-review-box`/`.payment-methods-card`, `.order-modal-card`, `.sticky-order-summary-bar`, `.product`/`.option-chip` (+ `.selected`), `.next-btn`.

**Don't reskin genuinely semantic colors** to match the new theme, even though they live in the same file: delete/danger buttons (red), success/confirmation flash messages and "Active" subscription status text (green), and the "Upgrade to Doughmain Pro" CTAs (fixed purple — that's platform monetization branding, not bakery branding, and should look the same for every bakery regardless of their theme). If you're not sure whether a color is decorative or semantic, check what else shares that exact hex value in the file before changing it — a color reused across an unrelated success-toast and a card border almost always means the card border was arbitrary, not that the toast should be reskinned.

## Step 5 — Verify before reporting done

Don't skip this and don't just eyeball two or three buttons — the whole reason this skill exists is that partial-looking-right is how bugs shipped before.

1. `php -l` every new blade file.
2. Render all 5 page types via `php artisan tinker`, temporarily setting `theme_id` on a real in-memory tenant (never call `->save()`):
   ```php
   $t = App\Models\Tenant::first();
   $orig = $t->theme_id;
   $t->theme_id = 'your_new_theme';
   foreach (['home'=>'index','about'=>'about','menu'=>'menu','gallery'=>'gallery','policy'=>'policy'] as $page=>$view) {
       try {
           $html = view($t->themeView($view), ['tenant'=>$t,'reviews'=>collect(),'products'=>collect(),'gallery'=>collect()])->render();
           echo "$page: OK (" . strlen($html) . " bytes)\n";
       } catch (\Throwable $e) { echo "$page: ERROR - {$e->getMessage()} @ {$e->getFile()}:{$e->getLine()}\n"; }
   }
   $t->theme_id = $orig;
   ```
3. Render `admin.dashboard` the same way for both `plan_tier = 'pro'` and `'free'` — this needs `app('view')->share('errors', new \Illuminate\Support\ViewErrorBag)` plus every variable `AdminController::dashboard()` passes via `compact()` (check that method for the current list). Confirm `body.theme-{id}` is present in the output and nothing throws.
4. Check `storage/logs/laravel.log` for anything new after rendering.
5. For the new CSS file, a quick brace-balance check (`{` count == `}` count) catches the cheapest class of syntax mistake since there's no CSS linter wired up here.
6. Grep your own admin CSS block for `.btn-primary` and `.nav-order-btn` specifically — this is the step that's been missed twice before. If they're not there, generic buttons across the dashboard will look unthemed even though everything else you built looks right.

Even after all of that, expect the user to spot something real from an actual screenshot the first time — image fallback chains that go blank when a tenant only has one photo, a contrast issue, spacing. When that happens: fix the root cause, and before calling it done, check whether the same root cause affects *other* existing themes too. Twice in this project's history, a one-theme bug report turned out to be a gap in the shared base files affecting several themes at once — the right fix was restoring the shared rule, not patching around it in just the reported theme.

## Step 6 — Commit and push

Ask before committing, and ask before pushing — every time, not just the first time in a session. Stage only the files you actually touched (this repo tends to have unrelated untracked directories like `public/uploads/tenants/` sitting around; don't sweep those in).
