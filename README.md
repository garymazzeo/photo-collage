# Photo Collage

Svelte + Spectrum Web Components frontend, PHP 8.4 + MySQL backend. Local-first drafts with IndexedDB. Exports 13×10 in PNG at 300 ppi (3900×3000 px).

## Local Development

Frontend (choose npm or pnpm):

- npm:
  - `cd frontend`
  - `npm install`
  - Build: `npm run build` (outputs to `../public/assets`)
  - Dev (optional): `npm run dev` (requires separate PHP server; app is optimized for build + PHP server)
- pnpm:
  - `pnpm -C frontend install`
  - `pnpm -C frontend build`

Backend/PHP server:

- Ensure PHP 8.4 is installed locally.
- Start a local PHP server pointing to `public/`:
  - `php -S 127.0.0.1:8080 -t public`
- Visit `http://127.0.0.1:8080` after building the frontend.

Database:

- Start MySQL locally and create a database and user, then run:
  - `mysql -u root -p < sql/schema.sql`
- Set environment variables for DB connection (recommended) or create `config/.env` (for dev only):
  - `APP_ENV=development`
  - `APP_DEBUG=true`
  - `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`
  - `CSRF_SECRET` to a random value

## Backend

- Create MySQL DB and user, then run `sql/schema.sql`.
- Configure environment via server env vars (recommended) or `config/.env` symlinked from `/var/www/photo-collage/shared/.env` in production.
- Apache DocumentRoot: `/var/www/photo-collage/current/public`

### Apache vhost example

```
<VirtualHost *:80>
    ServerName your-domain
    DocumentRoot /var/www/photo-collage/current/public
    <Directory /var/www/photo-collage/current/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Required env vars (production)

- `APP_ENV=production`, `APP_DEBUG=false`
- `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`
- `SESSION_NAME` (optional), `CSRF_SECRET` (required)

## Security

- Sessions: HttpOnly, SameSite=Lax; set `APP_DEBUG=false` in production.
- CSRF: obtain `/api/auth.php?action=csrf` and send `X-CSRF-Token` on mutating requests.
- Headers set in `public/.htaccess` (CSP, X-Content-Type-Options, Referrer-Policy, X-Frame-Options, Permissions-Policy).

## CI/CD

- Set GitHub secrets: `VPS_HOST`, `VPS_USER`, `VPS_SSH_KEY`, `VPS_PATH`.
- Push to `main` to deploy. Release is rsynced to `releases/<timestamp>` and `current` symlink is switched.
- Server should have `/var/www/photo-collage/{current,releases,shared}`; place your `.env` in `shared/.env`.
- After first deploy, adjust ownership to `www-data` if needed.

## Printing

- Use Export PNG (300 ppi). For other sizes, choose presets; pixels = inches × 300.
