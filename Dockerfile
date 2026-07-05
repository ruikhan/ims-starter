# Dockerfile — IMS (ims-starter) for Render
# Base: official PHP + Apache image
FROM php:8.2-apache

# ── PHP extensions the app needs ─────────────────────────────
# pdo_mysql / mysqli -> config/db.php uses PDO with MySQL
# gd                 -> safe to have for any future image processing
RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo pdo_mysql mysqli gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ── Apache config ─────────────────────────────────────────────
# Enable mod_rewrite (used by .htaccess) and allow .htaccess overrides
RUN a2enmod rewrite \
    && sed -ri -e 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

# ── App code ──────────────────────────────────────────────────
# NOTE: this Dockerfile expects to sit at the REPO ROOT, i.e. the same
# level as login.php, index.php, config/, includes/, admin/, products/,
# categories/, stock/, users/, shop/, database/, assets/, uploads/
WORKDIR /var/www/html
COPY . /var/www/html/

# uploads/products must exist and be writable (product image uploads)
RUN mkdir -p /var/www/html/uploads/products \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chmod -R 775 /var/www/html/uploads

# ── Render dynamic port handling ─────────────────────────────
# Render injects $PORT at runtime; Apache defaults to 80, so we rewrite
# the listen directives on container start via this small entrypoint.
COPY docker/apache-start.sh /usr/local/bin/apache-start.sh
RUN chmod +x /usr/local/bin/apache-start.sh

EXPOSE 80
CMD ["/usr/local/bin/apache-start.sh"]
