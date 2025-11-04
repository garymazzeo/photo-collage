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
- Configure environment variables (see [Environment Variables](#environment-variables) below)

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

## Environment Variables

The application uses environment variables for configuration. These can be set via:

1. **Local development**: Create `config/.env` (copy from `config/.env.example`)
2. **Production**: System environment variables (recommended) or symlink `shared/.env` to `config/.env`

### Variable Reference

#### Required Variables

| Variable      | Description                          | Example                                              |
| ------------- | ------------------------------------ | ---------------------------------------------------- |
| `CSRF_SECRET` | Strong random secret for CSRF tokens | Generate: `php -r "echo bin2hex(random_bytes(32));"` |
| `DB_HOST`     | MySQL host                           | `127.0.0.1` or `localhost`                           |
| `DB_PORT`     | MySQL port                           | `3306`                                               |
| `DB_NAME`     | Database name                        | `photo_collage`                                      |
| `DB_USER`     | Database username                    | `photo_collage`                                      |
| `DB_PASSWORD` | Database password                    | (your secure password)                               |

#### Application Configuration

| Variable       | Description                                 | Default       | Required                  |
| -------------- | ------------------------------------------- | ------------- | ------------------------- |
| `APP_ENV`      | Environment (`development` or `production`) | `development` | No                        |
| `APP_DEBUG`    | Enable debug mode (`true` or `false`)       | `false`       | No                        |
| `APP_URL`      | Base URL for password reset links           | Auto-detected | Recommended in production |
| `SESSION_NAME` | PHP session cookie name                     | `photo_sess`  | No                        |

#### Email Configuration (Optional)

| Variable        | Description                   | Default              |
| --------------- | ----------------------------- | -------------------- |
| `MAIL_FROM`     | From address for emails       | `noreply@{hostname}` |
| `MAIL_REPLY_TO` | Reply-to address              | Same as `MAIL_FROM`  |
| `MAIL_TEST`     | Test email address (dev only) | `test@example.com`   |

### Secure Storage Practices

#### Local Development

1. **Copy the example file:**

   ```bash
   cp config/.env.example config/.env
   ```

2. **Edit `config/.env` with your values:**

   ```bash
   nano config/.env  # or use your preferred editor
   ```

3. **Ensure `.env` is in `.gitignore`** (already configured)

#### Production (VPS)

**Option 1: System Environment Variables (Recommended)**

Set via your web server or systemd service:

```bash
# Apache: In VirtualHost or .htaccess (if using mod_env)
SetEnv DB_HOST 127.0.0.1
SetEnv DB_PASSWORD your-secure-password

# PHP-FPM: In pool config (/etc/php/8.4/fpm/pool.d/www.conf)
env[DB_HOST] = 127.0.0.1
env[DB_PASSWORD] = your-secure-password

# Systemd service: In /etc/systemd/system/photo-collage.service
[Service]
Environment="DB_HOST=127.0.0.1"
Environment="DB_PASSWORD=your-secure-password"
```

**Option 2: Shared .env File (Deployment-friendly)**

1. Create secure file outside web root:

   ```bash
   sudo mkdir -p /var/www/photo-collage/shared
   sudo nano /var/www/photo-collage/shared/.env
   ```

2. Set restrictive permissions:

   ```bash
   sudo chmod 600 /var/www/photo-collage/shared/.env
   sudo chown www-data:www-data /var/www/photo-collage/shared/.env
   ```

3. The deployment script (`deploy/scripts/post_deploy.sh`) automatically symlinks `shared/.env` to `config/.env`

**Option 3: Custom .env Location (Outside Project Directory)**

Store your `.env` file completely outside the project tree for maximum security:

1. Create the file in your desired location:

   ```bash
   sudo nano /home/gmazzeo/config/.env
   # or any path outside the project
   ```

2. Set restrictive permissions:

   ```bash
   sudo chmod 600 /home/gmazzeo/config/.env
   sudo chown www-data:www-data /home/gmazzeo/config/.env
   ```

3. Add `ENV_FILE` as a GitHub secret with the full path:
   - Go to your GitHub repo → Settings → Secrets and variables → Actions
   - Add secret: `ENV_FILE` = `/home/gmazzeo/config/.env`
   - The deployment script will automatically use this location

**Note:** If `ENV_FILE` is not set, the script falls back to `$VPS_PATH/shared/.env`

**Security Best Practices:**

- ✅ Never commit `.env` to version control (already in `.gitignore`)
- ✅ Use strong, unique passwords for `DB_PASSWORD` and `CSRF_SECRET`
- ✅ Restrict file permissions: `chmod 600` for `.env` files
- ✅ Use system environment variables in production when possible
- ✅ Rotate secrets regularly, especially `CSRF_SECRET`
- ✅ Use different credentials for development and production
- ✅ Review `.env` file access: only `www-data` should read it

**Generating Secure Secrets:**

```bash
# CSRF secret (32 bytes = 64 hex characters)
php -r "echo bin2hex(random_bytes(32));"

# Database password (use a password manager or generate)
openssl rand -base64 32
```

### Email Setup

The app uses PHP's `mail()` function for password reset emails. To check if it works:

1. **Test on your VPS:**

   ```bash
   php -r "mail('your-email@example.com', 'Test', 'Test email'); echo 'Sent';"
   ```

   Check your inbox/spam. If it doesn't work:

2. **Check if sendmail/postfix is installed:**
   ```bash
   which sendmail
   # or
   which postfix
   ```
3. **If missing, install postfix (Ubuntu/Debian):**

   ```bash
   sudo apt-get update
   sudo apt-get install postfix
   ```

   Choose "Internet Site" during setup and configure your domain.

4. **Alternative: Use SMTP (requires additional PHP library)**
   - Install PHPMailer or similar
   - Update `src/Email.php` to use SMTP instead of `mail()`

**Note:** In development, the reset token is returned in the API response for testing. In production, only the email is sent.

## Security

- Sessions: HttpOnly, SameSite=Lax; set `APP_DEBUG=false` in production.
- CSRF: obtain `/api/auth.php?action=csrf` and send `X-CSRF-Token` on mutating requests.
- Headers set in `public/.htaccess` (CSP, X-Content-Type-Options, Referrer-Policy, X-Frame-Options, Permissions-Policy).

## CI/CD

- Set GitHub secrets: `VPS_HOST`, `VPS_USER`, `VPS_SSH_KEY`, `VPS_PATH`.
- Optional: Set `ENV_FILE` secret to use a custom `.env` location (e.g., `/home/gmazzeo/config/.env`)
- Push to `main` to deploy. Release is rsynced to `releases/<timestamp>` and `current` symlink is switched.
- Server should have `/var/www/photo-collage/{current,releases,shared}`.
- **Environment setup:**
  - Use `ENV_FILE` secret for custom location, or
  - Place your `.env` in `shared/.env` before first deploy, or
  - Configure system environment variables
- After first deploy, adjust ownership to `www-data` if needed:
  ```bash
  sudo chown -R www-data:www-data /var/www/photo-collage/shared
  sudo chmod 600 /var/www/photo-collage/shared/.env
  # Or for custom location:
  sudo chmod 600 /home/gmazzeo/config/.env
  sudo chown www-data:www-data /home/gmazzeo/config/.env
  ```

## Printing

- Use Export PNG (300 ppi). For other sizes, choose presets; pixels = inches × 300.
