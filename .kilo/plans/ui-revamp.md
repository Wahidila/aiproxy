# UI Revamp Plan: Intercom-Inspired Design System

## Overview
Revamp semua halaman dashboard (admin + user) dan auth pages agar sesuai dengan DESIGN.md (Intercom-inspired). Landing page (`welcome.blade.php`) di-skip karena sudah sesuai.

**Font**: Inter (pengganti Saans, sudah tersedia di Google Fonts)
**Scope**: 30+ Blade files (layouts, components, dashboard pages, admin pages, auth pages)

---

## Phase 1: Foundation (Tailwind Config + CSS + Layouts)

### 1.1 Update `tailwind.config.js`
- Ganti font `Figtree` → `Inter`
- Tambahkan custom colors sesuai DESIGN.md:
  - `canvas: '#faf9f6'` (warm off-white background, mengganti `bg-gray-100`)
  - `off-black: '#111111'` (primary text)
  - `fin-orange: '#ff5600'` (brand accent, mengganti `indigo-600`)
  - `oat: '#dedbd6'` (warm border, mengganti `border-gray-200`)
  - `muted: '#7b7b78'` (muted text)
  - `surface: '#ffffff'` (card backgrounds)
- Tambahkan custom border-radius: `btn: '4px'`, `card: '8px'`
- Tambahkan custom letter-spacing untuk headings (negative tracking)
- Tambahkan report palette colors (blue, green, red, pink, lime, orange)

### 1.2 Update `resources/css/app.css`
- Tambahkan base styles setelah Tailwind directives:
  - Typography utility classes (heading-display, heading-section, heading-sub, heading-card, heading-feature)
  - Button hover animation: `scale(1.1)` hover, `scale(0.85)` active
  - Mono label utility class (uppercase, wide tracking)
  - Custom scrollbar styling (warm tones)
  - Transition utilities

### 1.3 Update `resources/views/layouts/app.blade.php`
- Ganti font link: `Figtree` → `Inter:300,400,500,600,700`
- Ganti `bg-gray-100` → `bg-canvas` (warm off-white `#faf9f6`)
- Ganti header `bg-white shadow` → `bg-surface border-b border-oat`
- Minimal shadows, depth through borders

### 1.4 Update `resources/views/layouts/guest.blade.php`
- Ganti font link → Inter
- Ganti `bg-gray-100` → `bg-canvas`
- Ganti card `bg-white shadow-md sm:rounded-lg` → `bg-surface border border-oat rounded-lg`
- Center layout properly
- Add brand logo with Fin Orange accent

### 1.5 Update `resources/views/layouts/navigation.blade.php`
- Ganti `bg-white border-b border-gray-100` → `bg-surface border-b border-oat`
- Logo: keep Zap icon with `#ff5600`, update font to Inter with negative tracking
- Nav links: ganti `indigo-400` active border → `fin-orange` active border
- Dropdown: warm styling, `rounded-[4px]` buttons
- Mobile menu: warm tones
- User dropdown: warm styling

---

## Phase 2: Blade Components (12 files)

### 2.1 `components/nav-link.blade.php`
- Active: `border-b-2 border-fin-orange text-off-black`
- Inactive: `border-transparent text-muted hover:text-off-black hover:border-oat`

### 2.2 `components/responsive-nav-link.blade.php`
- Active: `border-l-4 border-fin-orange bg-canvas text-off-black`
- Inactive: `border-transparent text-muted hover:bg-canvas`

### 2.3 `components/primary-button.blade.php`
- `bg-off-black text-white rounded-[4px] hover:bg-white hover:text-off-black hover:scale-110 active:scale-85`
- Remove `uppercase tracking-widest`
- Font: `text-sm font-medium`

### 2.4 `components/secondary-button.blade.php`
- `bg-transparent border border-off-black text-off-black rounded-[4px] hover:scale-110 active:scale-85`

### 2.5 `components/danger-button.blade.php`
- `bg-[#c41c1c] text-white rounded-[4px] hover:scale-110 active:scale-85`

### 2.6 `components/text-input.blade.php`
- `border-oat rounded-[4px] focus:border-fin-orange focus:ring-fin-orange bg-surface`
- Remove `indigo` focus colors

### 2.7 `components/input-label.blade.php`
- `text-off-black font-medium text-sm`

### 2.8 `components/input-error.blade.php`
- `text-[#c41c1c]` (report red)

### 2.9 `components/dropdown.blade.php`
- `bg-surface border border-oat rounded-lg shadow-sm`

### 2.10 `components/dropdown-link.blade.php`
- `text-off-black hover:bg-canvas`

### 2.11 `components/modal.blade.php`
- `bg-surface border border-oat rounded-lg`
- Backdrop: warm tint

### 2.12 `components/application-logo.blade.php`
- Update jika perlu (Zap icon + "AIMurah" text)

---

## Phase 3: Auth Pages (6 files)

### 3.1 `auth/login.blade.php`
- Checkbox: `text-fin-orange focus:ring-fin-orange` (ganti indigo)
- Links: `text-muted hover:text-off-black` (ganti indigo focus ring)
- Overall warm styling dari guest layout

### 3.2 `auth/register.blade.php`
- Same pattern as login

### 3.3 `auth/forgot-password.blade.php`
- Same warm styling

### 3.4 `auth/reset-password.blade.php`
- Same warm styling

### 3.5 `auth/verify-email.blade.php`
- Same warm styling

### 3.6 `auth/confirm-password.blade.php`
- Same warm styling

---

## Phase 4: User Dashboard Pages (7 files)

### 4.1 `dashboard.blade.php` (User Dashboard)
**Cards:**
- Ganti `bg-white shadow-sm sm:rounded-lg border border-gray-200` → `bg-surface border border-oat rounded-lg`
- No shadows (depth through borders)
- Stat labels: `text-xs font-medium text-muted uppercase tracking-wide` (keep, tapi ganti gray → muted)

**Colors:**
- `text-green-600` balance → keep (report green)
- `text-indigo-600` balance → `text-fin-orange`
- `bg-indigo-600` buttons → `bg-off-black rounded-[4px]`
- `bg-indigo-500` chart bars → `bg-fin-orange`

**Tables:**
- `bg-gray-50` thead → `bg-canvas`
- `divide-gray-200` → `divide-oat`
- `text-gray-500` → `text-muted`
- `text-gray-900` → `text-off-black`

**Headings:**
- `text-lg font-semibold text-gray-900` → `text-lg font-semibold text-off-black tracking-tight`

**Badges:**
- Keep semantic colors (green for success, red for error)
- Update border-radius to `rounded-[4px]`

### 4.2 `api-keys/index.blade.php`
- Same card/table pattern as dashboard
- Tab buttons: active `border-fin-orange text-fin-orange` (ganti indigo)
- Guide boxes: keep semantic colors (indigo→off-black, purple→keep, blue→keep)
- Create form: `focus:border-fin-orange focus:ring-fin-orange`
- Radio buttons: `text-fin-orange focus:ring-fin-orange` for paid tier
- Keep green for free tier radio
- Action buttons: `rounded-[4px]`

### 4.3 `usage/index.blade.php`
- Same card pattern
- Chart bars: `bg-fin-orange` (ganti indigo)
- Progress bars: `bg-fin-orange`
- Filter form inputs: warm styling
- Export button: `bg-off-black text-white rounded-[4px]`

### 4.4 `donations/index.blade.php`
- Same card pattern
- Upload area: warm styling with oat borders
- Submit button: `bg-off-black rounded-[4px]`

### 4.5 `donations/history.blade.php`
- Same table/card pattern
- Status badges: keep semantic colors

### 4.6 `profile/edit.blade.php`
- Same card pattern
- Form inputs: warm styling

### 4.7 `profile/partials/*.blade.php` (3 files)
- Update form styling to warm theme
- Buttons: `bg-off-black rounded-[4px]`
- Danger zone: keep red semantic color

---

## Phase 5: Admin Pages (6 files)

### 5.1 `admin/dashboard.blade.php`
**Stat Cards:**
- `bg-indigo-50` icon backgrounds → `bg-canvas`
- `text-indigo-600` icons → `text-fin-orange`
- Card: `bg-surface border border-oat rounded-lg` (no shadow)

**Quick Links:**
- `hover:border-indigo-300` → `hover:border-fin-orange`
- `bg-indigo-100` icon bg → `bg-[#fff0e6]` (light orange tint)
- `text-indigo-600` icons → `text-fin-orange`

**Chart:**
- `bg-indigo-500` bars → `bg-fin-orange`

**Proxy Control:**
- Keep semantic green/red for online/offline status
- Toggle switch: `bg-green-500` → keep (semantic)
- `focus:ring-indigo-500` → `focus:ring-fin-orange`

**Pending Donations / Recent Users:**
- `text-indigo-600` links → `text-fin-orange`
- Avatar circles: `bg-indigo-100 text-indigo-600` → `bg-[#fff0e6] text-fin-orange`
- Approve button: keep green (semantic)

### 5.2 `admin/users/index.blade.php`
- Same table/card pattern
- Search input: warm styling
- Pagination: warm styling
- Admin badge: `bg-orange-100 text-orange-700` → keep (already warm)
- View/action links: `text-fin-orange`

### 5.3 `admin/users/show.blade.php`
- Same card/table pattern
- All indigo references → fin-orange or off-black
- Balance adjustment form: warm styling
- Ban/unban buttons: keep semantic red/green

### 5.4 `admin/donations/index.blade.php`
- Same table/card pattern
- Filter tabs: `text-fin-orange border-fin-orange` active
- Approve/reject buttons: keep semantic green/red
- Proof image modal: warm styling

### 5.5 `admin/model-pricing/index.blade.php`
- Same table/card pattern
- Create/edit form: warm styling
- Delete button: keep semantic red

### 5.6 `admin/settings/index.blade.php`
- Same card/form pattern
- Save button: `bg-off-black rounded-[4px]`
- QRIS upload: warm styling

---

## Color Mapping Summary

| Current (Tailwind default) | New (DESIGN.md) |
|---|---|
| `bg-gray-100` (page bg) | `bg-[#faf9f6]` / `bg-canvas` |
| `bg-white` (cards) | `bg-white` / `bg-surface` |
| `border-gray-200` | `border-[#dedbd6]` / `border-oat` |
| `text-gray-900` | `text-[#111111]` / `text-off-black` |
| `text-gray-500` | `text-[#7b7b78]` / `text-muted` |
| `bg-indigo-600` (primary btn) | `bg-[#111111]` / `bg-off-black` |
| `text-indigo-600` (links/accent) | `text-[#ff5600]` / `text-fin-orange` |
| `border-indigo-400` (active nav) | `border-[#ff5600]` / `border-fin-orange` |
| `focus:ring-indigo-500` | `focus:ring-[#ff5600]` / `focus:ring-fin-orange` |
| `bg-indigo-50/100` (icon bg) | `bg-[#fff0e6]` (light orange tint) |
| `rounded-md` (buttons) | `rounded-[4px]` |
| `rounded-lg` (cards) | `rounded-lg` (8px, keep) |
| `shadow-sm` / `shadow` | Remove or minimal |

## Semantic Colors (KEEP as-is)
- Green: success, online, active, approve, positive balance
- Red: error, offline, danger, reject, ban, negative
- Yellow/amber: warning, pending, toggle off
- Orange badge for admin: keep

---

## Implementation Order

1. **tailwind.config.js** — add custom theme tokens
2. **resources/css/app.css** — add utility classes + animations
3. **layouts/app.blade.php** — font + base styling
4. **layouts/guest.blade.php** — font + base styling
5. **layouts/navigation.blade.php** — nav warm styling
6. **12 Blade components** — update all component styles
7. **6 Auth pages** — warm styling (mostly inherits from components)
8. **User dashboard** — dashboard.blade.php
9. **API Keys** — api-keys/index.blade.php
10. **Usage** — usage/index.blade.php
11. **Donations** — donations/index.blade.php + history.blade.php
12. **Profile** — profile/edit.blade.php + 3 partials
13. **Admin Dashboard** — admin/dashboard.blade.php
14. **Admin Users** — admin/users/index.blade.php + show.blade.php
15. **Admin Donations** — admin/donations/index.blade.php
16. **Admin Model Pricing** — admin/model-pricing/index.blade.php
17. **Admin Settings** — admin/settings/index.blade.php
18. **Build & Test** — `npm run build`, visual check

**Estimated files to modify: ~33 files**
**No backend/controller changes needed — purely frontend/Blade changes**
