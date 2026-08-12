#!/usr/bin/env bash
# Sync design-model pages (HTML/CSS) + Bitrix DETAIL_TEXT/DESCRIPTION to local and/or remote.
#
# Usage:
#   scripts/dm-sync-page.sh --env local|remote|both [--code CODE|--all] [--css]
#
# Examples:
#   scripts/dm-sync-page.sh --env local --all --css
#   scripts/dm-sync-page.sh --env remote --code promo-sayt
#   scripts/dm-sync-page.sh --env both --code sayt-vizitka --css
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
MANIFEST="$SCRIPT_DIR/dm-pages.manifest.json"
UPSERT_PHP="$SCRIPT_DIR/dm-sync-upsert.php"
PAGES_SRC="$REPO_ROOT/bitrix/templates/aspro_max/design-model/pages"
CSS_SRC="$REPO_ROOT/bitrix/templates/aspro_max/css/design-model.css"
IMAGES_SRC="$REPO_ROOT/bitrix/templates/aspro_max/design-model/images"

LOCAL_DOCROOT="${DM_LOCAL_DOCROOT:-/Users/viktorgromov/itweb-new}"
LOCAL_PHP_CONTAINER="${DM_LOCAL_PHP_CONTAINER:-itweb-new-php-1}"
LOCAL_DB_CONTAINER="${DM_LOCAL_DB_CONTAINER:-itweb-new-db-1}"
LOCAL_DB_USER="${DM_LOCAL_DB_USER:-bitrix}"
LOCAL_DB_PASS="${DM_LOCAL_DB_PASS:-123}"
LOCAL_DB_NAME="${DM_LOCAL_DB_NAME:-itweb-new}"

REMOTE_SSH="${DM_REMOTE_SSH:-itweb-new@itweb-new.acrobat.test-itweb.ru}"
REMOTE_DOCROOT="${DM_REMOTE_DOCROOT:-/var/www/itweb-new/data/www/itweb-new.acrobat.test-itweb.ru}"

ENV=""
CODE=""
DO_ALL=0
DO_CSS=0

usage() {
  sed -n '1,20p' "$0" | sed 's/^# \{0,1\}//'
  exit 1
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --env) ENV="${2:-}"; shift 2 ;;
    --code) CODE="${2:-}"; shift 2 ;;
    --all) DO_ALL=1; shift ;;
    --css) DO_CSS=1; shift ;;
    -h|--help) usage ;;
    *) echo "Unknown arg: $1" >&2; usage ;;
  esac
done

[[ -n "$ENV" ]] || usage
[[ "$DO_ALL" -eq 1 || -n "$CODE" ]] || { echo "Need --all or --code" >&2; exit 1; }
[[ -f "$MANIFEST" && -f "$UPSERT_PHP" ]] || { echo "Missing manifest/upsert script" >&2; exit 1; }

clear_cache() {
  local root="$1"
  rm -rf "$root/bitrix/cache/"* "$root/bitrix/managed_cache/"* "$root/bitrix/stack_cache/"* 2>/dev/null || true
}

sync_files_local() {
  mkdir -p "$LOCAL_DOCROOT/bitrix/templates/aspro_max/design-model/pages"
  mkdir -p "$LOCAL_DOCROOT/bitrix/templates/aspro_max/design-model/images"
  mkdir -p "$LOCAL_DOCROOT/bitrix/templates/aspro_max/css"
  if [[ "$DO_ALL" -eq 1 ]]; then
    cp -f "$PAGES_SRC"/*.html "$LOCAL_DOCROOT/bitrix/templates/aspro_max/design-model/pages/"
  else
    local html
    html="$(python3 -c "import json,sys; m=json.load(open(sys.argv[1])); c=sys.argv[2];
print(next(p['html'] for p in m['pages'] if p['code']==c))" "$MANIFEST" "$CODE")"
    cp -f "$PAGES_SRC/$html" "$LOCAL_DOCROOT/bitrix/templates/aspro_max/design-model/pages/"
  fi
  if [[ -d "$IMAGES_SRC" ]]; then
    cp -a "$IMAGES_SRC/." "$LOCAL_DOCROOT/bitrix/templates/aspro_max/design-model/images/" 2>/dev/null || true
  fi
  if [[ "$DO_CSS" -eq 1 ]]; then
    cp -f "$CSS_SRC" "$LOCAL_DOCROOT/bitrix/templates/aspro_max/css/design-model.css"
  fi
}

sync_db_local() {
  # Copy upsert+manifest into docroot temporarily for container access
  local tmp_dir="$LOCAL_DOCROOT/.dm-sync-tmp"
  mkdir -p "$tmp_dir"
  cp -f "$UPSERT_PHP" "$tmp_dir/dm-sync-upsert.php"
  cp -f "$MANIFEST" "$tmp_dir/dm-pages.manifest.json"

  docker exec \
    -e DM_DB_HOST=db \
    -e DM_DB_USER="$LOCAL_DB_USER" \
    -e DM_DB_PASS="$LOCAL_DB_PASS" \
    -e DM_DB_NAME="$LOCAL_DB_NAME" \
    -e DM_DOCROOT=/var/www/bitrix \
    -e DM_MANIFEST=/var/www/bitrix/.dm-sync-tmp/dm-pages.manifest.json \
    -e DM_PAGES_DIR=/var/www/bitrix/bitrix/templates/aspro_max/design-model/pages \
    -e DM_CODE="${CODE}" \
    "$LOCAL_PHP_CONTAINER" \
    php /var/www/bitrix/.dm-sync-tmp/dm-sync-upsert.php

  rm -rf "$tmp_dir"

  # Keep section tree margins valid after ensureSection inserts
  docker exec "$LOCAL_PHP_CONTAINER" php -r '
$_SERVER["DOCUMENT_ROOT"]="/var/www/bitrix";
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
require $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php";
\Bitrix\Main\Loader::includeModule("iblock");
CIBlockSection::ReSort(21);
echo "RESORT_OK\n";
' >/dev/null

  clear_cache "$LOCAL_DOCROOT"
}

sync_files_remote() {
  ssh -o BatchMode=yes "$REMOTE_SSH" "mkdir -p '$REMOTE_DOCROOT/bitrix/templates/aspro_max/design-model/pages' '$REMOTE_DOCROOT/bitrix/templates/aspro_max/design-model/images' '$REMOTE_DOCROOT/bitrix/templates/aspro_max/css' '$REMOTE_DOCROOT/.dm-sync-tmp'"
  if [[ "$DO_ALL" -eq 1 ]]; then
    scp -o BatchMode=yes "$PAGES_SRC"/*.html "$REMOTE_SSH:$REMOTE_DOCROOT/bitrix/templates/aspro_max/design-model/pages/"
  else
    local html
    html="$(python3 -c "import json,sys; m=json.load(open(sys.argv[1])); c=sys.argv[2];
print(next(p['html'] for p in m['pages'] if p['code']==c))" "$MANIFEST" "$CODE")"
    scp -o BatchMode=yes "$PAGES_SRC/$html" "$REMOTE_SSH:$REMOTE_DOCROOT/bitrix/templates/aspro_max/design-model/pages/"
  fi
  if [[ -d "$IMAGES_SRC" ]]; then
    scp -o BatchMode=yes -r "$IMAGES_SRC/." "$REMOTE_SSH:$REMOTE_DOCROOT/bitrix/templates/aspro_max/design-model/images/" 2>/dev/null || true
  fi
  if [[ "$DO_CSS" -eq 1 ]]; then
    scp -o BatchMode=yes "$CSS_SRC" "$REMOTE_SSH:$REMOTE_DOCROOT/bitrix/templates/aspro_max/css/design-model.css"
  fi
  scp -o BatchMode=yes "$UPSERT_PHP" "$MANIFEST" "$REMOTE_SSH:$REMOTE_DOCROOT/.dm-sync-tmp/"
}

sync_db_remote() {
  ssh -o BatchMode=yes "$REMOTE_SSH" bash -s <<REMOTE
set -euo pipefail
ROOT='$REMOTE_DOCROOT'
# read DB from bitrix/.settings.php without loading Bitrix
creds=\$(php -r '\$s=include "'\$ROOT'/bitrix/.settings.php"; \$d=\$s["connections"]["value"]["default"]; echo \$d["host"]."|".\$d["database"]."|".\$d["login"]."|".\$d["password"];')
IFS='|' read -r H DB U P <<<"\$creds"
export DM_DB_HOST="\$H" DM_DB_NAME="\$DB" DM_DB_USER="\$U" DM_DB_PASS="\$P"
export DM_DOCROOT="\$ROOT"
export DM_MANIFEST="\$ROOT/.dm-sync-tmp/dm-pages.manifest.json"
export DM_PAGES_DIR="\$ROOT/bitrix/templates/aspro_max/design-model/pages"
export DM_CODE='${CODE}'
php "\$ROOT/.dm-sync-tmp/dm-sync-upsert.php"
rm -rf "\$ROOT/bitrix/cache/"* "\$ROOT/bitrix/managed_cache/"* "\$ROOT/bitrix/stack_cache/"* 2>/dev/null || true
rm -rf "\$ROOT/.dm-sync-tmp"
REMOTE
}

do_local() {
  echo "==> LOCAL files"
  sync_files_local
  echo "==> LOCAL db"
  sync_db_local
  echo "==> LOCAL done"
}

do_remote() {
  echo "==> REMOTE files"
  sync_files_remote
  echo "==> REMOTE db"
  sync_db_remote
  echo "==> REMOTE done"
}

case "$ENV" in
  local) do_local ;;
  remote) do_remote ;;
  both) do_local; do_remote ;;
  *) echo "Bad --env: $ENV (local|remote|both)" >&2; exit 1 ;;
esac
