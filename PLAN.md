# YourShout — Implementation Plan

## Project Overview

Shared consumables management for coworkers. Users create consumables (e.g. milk), join them, and record purchases with quantities. The system tracks participation totals per member — the person with the lowest total is next to bring the consumable. Low stock reporting triggers notifications to the next person. i18n: English + German.

---

## Phase 1: Database & Models

### Migrations
1. **`create_consumables_table`** — `id`, `name` (string), `unit` (string, default 'item'), `created_at`, `updated_at`
2. **`create_consumable_user_table`** (pivot) — `consumable_id`, `user_id`, `total_quantity` (integer, default 0)
3. **`create_purchase_records_table`** — `id`, `consumable_id` (FK), `user_id` (FK), `quantity` (integer, default 1), `reported_at` (nullable timestamp), `created_at`, `updated_at`

### Models
4. **`Consumable`** — relationships: `users()` (belongsToMany via pivot), `purchases()` (hasMany)
5. **`PurchaseRecord`** — relationship: `consumable()` (belongsTo)

---

## Phase 2: i18n (English + German)

6. Create `lang/en/` and `lang/de/` directories
7. Translation files: `consumables.php`, `notifications.php`, `validation.php`
8. Translate all existing UI strings (`Dashboard`, `Settings`, etc.) in views
9. **Locale switcher** — dropdown in sidebar user menu (EN/DE) using `session(['locale' => 'de'])` + `App::setLocale()`

---

## Phase 3: Livewire Components

10. **`Consumables/Index`** — List all consumables the user joined, shows participation totals per member, highlights "next person" (lowest total), low-stock badge
11. **`Consumables/Create`** — Form to create a new consumable with unit label
12. **`Consumables/Show`** — Detail view: members table with totals, purchase history, "report low stock" button
13. **`Consumables/Join`** — Action to join/leave a consumable
14. **`Consumables/RecordPurchase`** — Form to record a purchase (quantity)
15. **`Consumables/ReportLowStock`** — Action to report low stock → triggers notification

---

## Phase 4: Notifications

16. Install `laravel-notification-channels/webpush`
17. Add `HasPushSubscriptions` trait to User model
18. Publish & run migration for push_subscriptions table
19. Generate VAPID keys via `php artisan webpush:vapid`
20. **`NextToBringNotification`** — WebPush + Email notification class
21. Route to store/delete push subscriptions (JS service worker)

---

## Phase 5: Routing & Navigation

22. Add `/consumables` routes to `web.php` (index, create, show)
23. Add "Consumables" item to sidebar navigation
24. Replace dashboard placeholder with consumable summary cards

---

## Phase 6: Frontend

25. Push subscription JS — service worker + API calls to store/delete subscriptions
26. Wire up Livewire component views with Flux components (tables, buttons, badges, modals)

---

## Phase 7: Testing & Polish

27. Feature tests for Consumables CRUD, join/leave, purchase recording, low stock reporting
28. Run `vendor/bin/pint --format agent` and verify all tests pass

---

## Key Design Decisions

| Decision | Choice |
|----------|--------|
| Scoring | Quantity per record (e.g. 2 bottles = quantity 2). Next person = lowest total quantity. |
| Locales | English (default) + German |
| Low stock | Badge on consumable + notify next person via WebPush + Email |
| Framework | Livewire 4 class-based components, Flux UI |
| Auth | Fortify with passkeys and 2FA |
