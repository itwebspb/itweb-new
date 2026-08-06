#!/usr/bin/env bash
#
# Idempotent environment bootstrap for the 1C-Bitrix site.
#
# System packages (php8.3, apache2 + mod_php, mariadb-server, composer) are
# expected to be present in the base image / snapshot. This script only applies
# repository-managed configuration and makes sure the Bitrix database exists.
# It is safe to run repeatedly.
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PHP_VER="$(ls /etc/php 2>/dev/null | sort -V | tail -1)"
DB_NAME="${BITRIX_DB_NAME:-sitemanager}"
DB_USER="${BITRIX_DB_USER:-bitrix}"
DB_PASS="${BITRIX_DB_PASS:-bitrix}"

echo "==> Applying PHP configuration (php ${PHP_VER})"
for sapi in cli apache2; do
	if [ -d "/etc/php/${PHP_VER}/${sapi}/conf.d" ]; then
		sudo cp "${REPO_ROOT}/.cursor/php/zz-bitrix.ini" \
			"/etc/php/${PHP_VER}/${sapi}/conf.d/99-bitrix.ini"
	fi
done

echo "==> Applying Apache configuration"
sudo cp "${REPO_ROOT}/.cursor/apache/bitrix.conf" /etc/apache2/sites-available/bitrix.conf
# Run Apache as the repo owner so Bitrix can write cache/upload directories.
if ! grep -q '^export APACHE_RUN_USER=ubuntu' /etc/apache2/envvars; then
	sudo sed -i 's/^export APACHE_RUN_USER=.*/export APACHE_RUN_USER=ubuntu/' /etc/apache2/envvars
	sudo sed -i 's/^export APACHE_RUN_GROUP=.*/export APACHE_RUN_GROUP=ubuntu/' /etc/apache2/envvars
fi
sudo a2enmod rewrite headers expires >/dev/null
sudo a2dissite 000-default >/dev/null 2>&1 || true
sudo a2ensite bitrix >/dev/null

echo "==> Ensuring MariaDB is initialised and the Bitrix database exists"
sudo mkdir -p /run/mysqld
sudo chown mysql:mysql /run/mysqld
if [ ! -d /var/lib/mysql/mysql ]; then
	sudo mariadb-install-db --user=mysql --datadir=/var/lib/mysql >/dev/null
fi
STARTED_MYSQL=0
if ! sudo mariadb -e "SELECT 1" >/dev/null 2>&1; then
	sudo mariadbd --user=mysql >/tmp/mariadb-install.log 2>&1 &
	STARTED_MYSQL=1
	for _ in $(seq 1 30); do
		sudo mariadb -e "SELECT 1" >/dev/null 2>&1 && break
		sleep 1
	done
fi
sudo mariadb <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
CREATE USER IF NOT EXISTS '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL
if [ "${STARTED_MYSQL}" = "1" ]; then
	sudo mariadb-admin shutdown >/dev/null 2>&1 || true
fi

echo "==> install.sh complete"
