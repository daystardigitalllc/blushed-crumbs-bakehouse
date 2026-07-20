# BakeBox Multi-Tenant SaaS Engine (Bakesy Competitor) & Blushed Crumbs Bakehouse

A high-performance **Laravel Multi-Tenant SaaS Platform** built to empower home bakeries, cake artists, and dessert shops with custom branded storefronts and an all-in-one mobile admin dashboard.

---

## 🚀 Key Features

### 1. Client Storefront (`blushedcrumbsbakehouse.com`)
- **Whimsical Bakery Aesthetics**: Soft pink cloud dividers (`#ffd6e0`, `#b35978`), Google Fonts (`Great Vibes`, `Poppins`), interactive menu cards.
- **Integrated 12-Step Cake & Treat Order Builder**: Step-by-step selection for cake sizes, treats, luxury flavors, frosting, fillings, delivery address toggle, allergy disclaimers, social media discount calculations (-$5 per follow), inspiration image uploads, terms & conditions check, and contact details.
- **Dynamic Reviews & Gallery**: Displays customer feedback and cake portfolio pictures directly from the database.

### 2. Bakesy-Style Baker Mobile Admin Portal (`/admin`)
- **📅 Priority Order Queue (Sorted by Due Date)**: Automatically highlights orders sorted chronologically by due date so bakers never miss an order deadline.
- **💳 Digital Invoicing & 3rd Party Payment Links**: Auto-generates itemized invoices with 50% non-refundable deposit breakdowns. Supports Venmo (`@Blushed_Crumbs`), CashApp (`$BlushedCrumbs`), PayPal, Zelle, and Stripe.
- **🧁 No-Code Product & Pricing Editor**: Bakers can add new items or edit prices directly on mobile without technical tickets.
- **⭐ Review & Gallery Manager**: Publish customer reviews and cake photos directly from mobile.
- **💬 Developer Support Concierge**: In-app support request form for custom features ($50/mo Pro Tier perk).

---

## ☁️ Cloudways Deployment Instructions

1. Log into your **Cloudways Dashboard**.
2. Click **Applications** -> **Add Application** -> Select **Custom PHP / Laravel**.
3. Name the application `blushedcrumbs-saas`.
4. Navigate to **Application Management** -> **Deployment via Git**.
5. Paste your GitHub Repository URL: `https://github.com/austi/blushed-crumbs-bakehouse.git`.
6. Click **Start Deployment**.
7. Run migrations and seeders on Cloudways:
   ```bash
   php artisan migrate --seed
   ```
8. Point domain `blushedcrumbsbakehouse.com` to your Cloudways Application IP address.

---

## 💳 SaaS Subscription Pricing Model

- **Standard Tier ($29/month)**: Full website storefront, 12-step cake builder, due-date order queue, mobile admin portal, invoices & payment links, product manager.
- **Pro Tier ($50/month)**: Standard features + automated SMS reminders via Twilio, custom domain support, custom feature request concierge.
