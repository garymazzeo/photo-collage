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
- Configure environment via server env vars (recommended) or `config/.env` symlinked from your `VPS_PATH/shared/.env` in production.

### Apache Configuration

**CRITICAL:** Apache's `DocumentRoot` must point to the `public` subdirectory, not the project root.

#### Option 1: VirtualHost Configuration (Recommended)

Edit your Apache site configuration (usually in `/etc/apache2/sites-available/`):

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    # IMPORTANT: Point to the 'public' subdirectory
    DocumentRoot /home/username/example.com/photo-collage/current/public

    <Directory /home/username/example.com/photo-collage/current/public>
        AllowOverride All
        Require all granted
        Options -Indexes  # Disable directory listings
    </Directory>

    # Optional: Logging
    ErrorLog ${APACHE_LOG_DIR}/photo-collage-error.log
    CustomLog ${APACHE_LOG_DIR}/photo-collage-access.log combined
</VirtualHost>
```

Then enable the site:

```bash
sudo a2ensite your-site-config-name
sudo systemctl reload apache2
```

#### Option 2: .htaccess in Project Root (If you can't edit vhost)

If you can't modify the VirtualHost, create a `.htaccess` in your project root (`$VPS_PATH/current/.htaccess`):

```apache
RewriteEngine On
RewriteCond %{REQUEST_URI} !^/public/
RewriteRule ^(.*)$ /public/$1 [L]
```

**Note:** This is less secure and less efficient than setting DocumentRoot correctly.

#### Verify Configuration

After configuring, test:

1. Visit `http://your-domain.com` - should show the app, not a directory listing
2. Check browser console for any 404 errors on `/assets/index.js` or `/assets/style.css`
3. If you see a white screen, check:
   - Are assets built? (`ls $VPS_PATH/current/public/assets/`)
   - Is DocumentRoot correct? (`apache2ctl -S` shows DocumentRoot)
   - Is mod_rewrite enabled? (`sudo a2enmod rewrite`)

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
   sudo nano /home/user-name/config/.env
   # or any path outside the project
   ```

2. Set restrictive permissions:

   ```bash
   sudo chmod 600 /home/user-name/config/.env
   sudo chown www-data:www-data /home/user-name/config/.env
   ```

3. Add `ENV_FILE` as a GitHub secret with the full path:
   - Go to your GitHub repo → Settings → Secrets and variables → Actions
   - Add secret: `ENV_FILE` = `/home/user-name/config/.env`
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

### Initial Server Setup

Before first deployment, set up the directory structure on your VPS. **The base path (`VPS_PATH`) must exist and be owned by your SSH user.**

#### Option 1: Directory in Your Home Directory (Recommended if no sudo)

If your SSH user doesn't have sudo privileges, use a path in your home directory:

```bash
# Use a path in your home directory (you already own this)
VPS_PATH="$HOME/example.com/photo-collage"  # or any path you own
SSH_USER="username"  # Your SSH username

# Create directory structure (no sudo needed)
mkdir -p $VPS_PATH/{releases,shared/uploads}

# Verify ownership
ls -la $VPS_PATH
# Should show your SSH user as owner
```

#### Option 2: Ask VPS Admin to Set Up Directory

If you need to use a system path like `/var/www/photo-collage`, ask your VPS administrator to:

```bash
# Admin runs these commands:
sudo mkdir -p /var/www/photo-collage/{releases,shared/uploads}
sudo chown -R your-ssh-user:your-ssh-user /var/www/photo-collage
sudo chmod -R 755 /var/www/photo-collage
```

#### Option 3: Use Existing Directory You Own

If a directory already exists and you own it, just create the subdirectories:

```bash
# If the base path already exists and you own it:
mkdir -p $VPS_PATH/{releases,shared/uploads}
```

**Important:** The workflow will fail if:

- `VPS_PATH` doesn't exist
- Your SSH user doesn't own `VPS_PATH` (check with `ls -la $VPS_PATH`)
- Your SSH user can't create subdirectories in `VPS_PATH/releases`

**To check ownership:**

```bash
ls -ld $VPS_PATH
# The owner (3rd column) should be your SSH username
```

### GitHub Secrets

Set the following secrets in your GitHub repository (Settings → Secrets and variables → Actions):

- `VPS_HOST` - Your VPS hostname or IP
- `VPS_USER` - SSH username (must have write access to `VPS_PATH`)
- `VPS_SSH_KEY` - Private SSH key for authentication
- `VPS_PATH` - Base path on server (e.g., `/var/www/photo-collage` or `/home/user-name/example.com/photo-collage`)
- `ENV_FILE` (optional) - Custom `.env` location (e.g., `/home/user-name/config/.env`)

### Deployment

- Push to `main` to deploy automatically, or use "Run workflow" for manual deployment
- Release is rsynced to `releases/<timestamp>` and `current` symlink is switched atomically
- **Environment setup:**
  - Use `ENV_FILE` secret for custom location, or
  - Place your `.env` in `shared/.env` before first deploy, or
  - Configure system environment variables
- After first deploy, adjust ownership to `www-data` if needed:
  ```bash
  sudo chown -R www-data:www-data /var/www/photo-collage/shared
  sudo chmod 600 /var/www/photo-collage/shared/.env
  # Or for custom location:
  sudo chmod 600 /home/user-name/config/.env
  sudo chown www-data:www-data /home/user-name/config/.env
  ```

## Printing

- Use Export PNG (300 ppi). For other sizes, choose presets; pixels = inches × 300.
