# Photo Collage

Svelte + Spectrum Web Components frontend, PHP 8.4 + MySQL backend. Local-first drafts with IndexedDB. Exports 13×10 in PNG at 300 ppi (3900×3000 px).

## Dev
- Install Node 20 and pnpm.
- Install deps and build:
  - `pnpm -C frontend install`
  - `pnpm -C frontend dev` (dev server)
  - `pnpm -C frontend build` (outputs to `public/assets`)

## Backend
- Create MySQL DB and user, then run `sql/schema.sql`.
- Configure environment via server env vars (recommended) or `config/.env` symlinked from `/var/www/photo-collage/shared/.env` in production.
- Apache DocumentRoot: `/var/www/photo-collage/current/public`

## Security
- Sessions: HttpOnly, SameSite=Lax; set `APP_DEBUG=false` in production.
- CSRF: obtain `/api/auth.php?action=csrf` and send `X-CSRF-Token` on mutating requests.
- Headers set in `public/.htaccess` (CSP, X-Content-Type-Options, Referrer-Policy, X-Frame-Options, Permissions-Policy).

## CI/CD
- Set GitHub secrets: `VPS_HOST`, `VPS_USER`, `VPS_SSH_KEY`, `VPS_PATH`.
- Push to `main` to deploy. Release is rsynced to `releases/<timestamp>` and `current` symlink is switched.

## Printing
- Use Export PNG (300 ppi). For other sizes, choose presets; pixels = inches × 300.


