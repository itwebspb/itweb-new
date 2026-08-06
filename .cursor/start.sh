#!/usr/bin/env bash
#
# Per-boot startup for the 1C-Bitrix development environment.
# Starts MariaDB and Apache. Safe to run more than once.
set -euo pipefail

DB_NAME="${BITRIX_DB_NAME:-sitemanager}"
DB_USER="${BITRIX_DB_USER:-bitrix}"
DB_PASS="${BITRIX_DB_PASS:-bitrix}"

echo "==> Starting MariaDB"
sudo mkdir -p /run/mysqld
sudo chown mysql:mysql /run/mysqld
if ! sudo mariadb -e "SELECT 1" >/dev/null 2>&1; then
	sudo mariadbd --user=mysql >/tmp/mariadb.log 2>&1 &
	for _ in $(seq 1 30); do
		sudo mariadb -e "SELECT 1" >/dev/null 2>&1 && break
		sleep 1
	done
fi

# Make sure the Bitrix schema/user exist (no-op once created).
sudo mariadb <<SQL || true
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "==> Starting Apache"
# Ensure Apache runtime directories exist (absent on a bare container/VM).
sudo mkdir -p /run/apache2 /run/lock /var/log/apache2
sudo chown ubuntu:ubuntu /var/log/apache2
# apachectl sources /etc/apache2/envvars itself; validate config then (re)start.
if ! sudo apachectl configtest 2>&1 | grep -q "Syntax OK"; then
	echo "Apache config test failed" >&2
	sudo apachectl configtest
	exit 1
fi
sudo apachectl start 2>/dev/null || sudo apachectl restart

echo "==> Services up (MariaDB + Apache on :80)"
