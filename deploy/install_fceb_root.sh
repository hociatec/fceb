#!/usr/bin/env bash
set -euo pipefail

DOMAIN="${1:-fceb.hociatec.fr}"
APP_DIR="${APP_DIR:-/home/hocine/fceb}"
NGINX_AVAILABLE="/etc/nginx/sites-available/${DOMAIN}.conf"
NGINX_ENABLED="/etc/nginx/sites-enabled/${DOMAIN}.conf"
CERT_DIR="/etc/letsencrypt/live/${DOMAIN}"
PHP_SOCK="/run/php/php8.3-fpm.sock"

if [[ "${EUID}" -ne 0 ]]; then
  echo "Ce script doit etre execute avec sudo/root." >&2
  exit 1
fi

if [[ ! -d "${APP_DIR}" ]]; then
  echo "Application introuvable: ${APP_DIR}" >&2
  exit 1
fi

if [[ ! -S "${PHP_SOCK}" ]]; then
  echo "Socket PHP-FPM introuvable: ${PHP_SOCK}" >&2
  exit 1
fi

set_app_acl() {
  local parent_dir
  parent_dir="$(dirname "${APP_DIR}")"

  setfacl -m u:www-data:rx "${parent_dir}"
  setfacl -R -m u:www-data:rx "${APP_DIR}"

  mkdir -p "${APP_DIR}/var" "${APP_DIR}/public/uploads"
  setfacl -R -m u:www-data:rwx "${APP_DIR}/var" "${APP_DIR}/public/uploads"
  setfacl -R -d -m u:www-data:rwx "${APP_DIR}/var" "${APP_DIR}/public/uploads"
}

write_http_config() {
  cat > "${NGINX_AVAILABLE}" <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};

    root ${APP_DIR}/public;
    index index.php;

    access_log /var/log/nginx/fceb.access.log;
    error_log /var/log/nginx/fceb.error.log;

    client_max_body_size 20m;

    location /.well-known/acme-challenge/ {
        root ${APP_DIR}/public;
        try_files \$uri =404;
    }

    location / {
        try_files \$uri /index.php\$is_args\$args;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:${PHP_SOCK};
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT \$realpath_root;
    }

    location ~* \.(?:ico|css|js|gif|jpe?g|png|webp|svg)$ {
        try_files \$uri /index.php\$is_args\$args;
        expires 7d;
        access_log off;
    }

    location ~ /\.(?!well-known) {
        deny all;
    }
}
EOF
}

write_https_config() {
  cat > "${NGINX_AVAILABLE}" <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};
    return 301 https://\$host\$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name ${DOMAIN};

    root ${APP_DIR}/public;
    index index.php;

    access_log /var/log/nginx/fceb.access.log;
    error_log /var/log/nginx/fceb.error.log;

    ssl_certificate ${CERT_DIR}/fullchain.pem;
    ssl_certificate_key ${CERT_DIR}/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "DENY" always;

    client_max_body_size 20m;

    location / {
        try_files \$uri /index.php\$is_args\$args;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:${PHP_SOCK};
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT \$realpath_root;
    }

    location ~* \.(?:ico|css|js|gif|jpe?g|png|webp|svg)$ {
        try_files \$uri /index.php\$is_args\$args;
        expires 7d;
        access_log off;
    }

    location ~ /\.(?!well-known) {
        deny all;
    }
}
EOF
}

enable_site() {
  ln -sfn "${NGINX_AVAILABLE}" "${NGINX_ENABLED}"
  nginx -t
  systemctl reload nginx
}

write_http_config
set_app_acl
enable_site

if [[ -n "${CERTBOT_EMAIL:-}" ]]; then
  certbot certonly \
    --webroot \
    -w "${APP_DIR}/public" \
    -d "${DOMAIN}" \
    --agree-tos \
    --email "${CERTBOT_EMAIL}" \
    --non-interactive

  write_https_config
  enable_site
  echo "TLS configure pour ${DOMAIN}."
else
  echo "HTTP configure pour ${DOMAIN}."
  echo "Pour activer TLS, relance avec CERTBOT_EMAIL=votre@email sudo $0 ${DOMAIN}"
fi
