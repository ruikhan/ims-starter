# Deploying IMS (ims-starter) to Render via GitHub

Render has no native "PHP" runtime button and no managed MySQL — so this
project deploys as a **Docker web service** on Render, talking to an
**external MySQL** database (Aiven's free tier is the easiest match, and
`config/db.php` already has Aiven-style env vars baked in).

## 0. Where these files go
Put `Dockerfile`, `.dockerignore`, `.gitignore`, `render.yaml`, and the
`docker/` folder at the **repo root** — the same level as `login.php`,
`config/`, `includes/`, `admin/`, `products/`, `shop/`, etc.

## 1. Push to GitHub
```bash
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/<you>/ims-starter.git
git push -u origin main
```

## 2. Create the MySQL database (Aiven)
1. Sign up at aiven.io → create a **MySQL** service (free plan is fine).
2. From the service overview, copy: **Host**, **Port**, **User**, **Password**,
   and download the **CA certificate** (`ca.pem`).
3. Import your schema against that database — using a MySQL client or
   Aiven's web console/phpMyAdmin-equivalent, run in order:
   - `database/ims.sql`
   - `database/shop.sql`
   - the `order_status_log` / `notifications_queue` tables referenced in
     `includes/workflow.php` (create these if you haven't already — the
     order-status workflow engine needs them)

## 3. Create the Web Service on Render
1. Render Dashboard → **New** → **Blueprint** (this repo already has a
   `render.yaml`, so Render will read it automatically) — or **New Web
   Service** → connect your GitHub repo → Render detects the `Dockerfile`.
2. Plan: Free (fine for testing) or a paid plan for anything real.

## 4. Set environment variables
In the service's **Environment** tab, fill in the values `render.yaml`
left blank (`sync: false`):

| Key | Value |
|---|---|
| `DB_HOST` | Aiven host, e.g. `mysql-xxxx.aivencloud.com` |
| `DB_PORT` | Aiven port, e.g. `12345` |
| `DB_USER` | Aiven username |
| `DB_PASS` | Aiven password |
| `DB_NAME` | `ims_db` (already set in render.yaml) |
| `BASE_URL` | leave empty string (already set) |

## 5. Upload the MySQL CA certificate as a Secret File
Render Dashboard → your service → **Environment** → **Secret Files** →
add a file named `ca.pem` with the contents of the CA cert you downloaded
from Aiven. Render mounts secret files at `/etc/secrets/<filename>`, which
matches the `DB_SSL_CA` path already set in `render.yaml`.

## 6. Deploy
Trigger the first deploy (or just push to `main` — `autoDeploy: true` is
set). Render builds the Docker image and starts Apache bound to its
dynamic `$PORT` via `docker/apache-start.sh`.

## 7. Log in
Visit the Render-assigned URL (e.g. `https://ims-starter.onrender.com`)
and log in with the seeded accounts from `ims.sql`:

| Email | Password | Role |
|---|---|---|
| admin@ims.com | Admin@123 | Admin |
| maria@ims.com | Admin@123 | Staff |

**Change these passwords immediately** in Users → Edit once you're in.

## Known limitations to plan around
- **Ephemeral disk**: anything written to `uploads/products/` (product
  images uploaded via the admin UI) is lost on every redeploy or restart,
  because Render's filesystem for web services isn't persistent. For a
  real deployment, swap `admin/upload-image.php` /
  `products/remove-image.php` to write to S3-compatible object storage
  (Render also offers a paid **persistent disk** add-on if you'd rather
  keep the current file-based approach).
- **No managed MySQL on Render**: this guide uses Aiven; PlanetScale or a
  small VPS running MySQL work too — `config/db.php` doesn't care which,
  as long as the env vars are correct.
- **HTTPS**: Render terminates TLS for you automatically on the
  `onrender.com` subdomain and on custom domains you attach, so you can
  safely uncomment the `'secure' => true` cookie flag in
  `includes/auth.php` once you're only ever served over HTTPS.
