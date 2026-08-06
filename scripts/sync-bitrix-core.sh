#!/usr/bin/env bash
# Copy Bitrix core files that are intentionally excluded from git.
# Required for a working local site (header.php, urlrewrite.php, etc.).
#
# Prerequisites:
#   bash scripts/setup-ssh.sh   # or your own ~/.ssh/config for itweb-new-test
#
# Usage:
#   bash scripts/sync-bitrix-core.sh
#   REMOTE_DOCROOT=/var/www/.../public bash scripts/sync-bitrix-core.sh
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SSH_TARGET="${ITWEB_NEW_SSH_TARGET:-itweb-new-test}"
REMOTE_DOCROOT="${REMOTE_DOCROOT:-/var/www/itweb-new/data/www/itweb-new.acrobat.test-itweb.ru}"

echo "==> Syncing Bitrix core from ${SSH_TARGET}:${REMOTE_DOCROOT}"

rsync -az --delete \
	--include='/bitrix/header.php' \
	--include='/bitrix/footer.php' \
	--include='/bitrix/urlrewrite.php' \
	--include='/bitrix/index.php' \
	--include='/bitrix/click.php' \
	--include='/bitrix/redirect.php' \
	--include='/bitrix/rss.php' \
	--include='/bitrix/spread.php' \
	--include='/bitrix/stop_redirect.php' \
	--include='/bitrix/virtual_file_system.php' \
	--include='/bitrix/admin/***' \
	--include='/bitrix/js/***' \
	--include='/bitrix/css/***' \
	--include='/bitrix/images/***' \
	--include='/bitrix/tools/***' \
	--include='/bitrix/panel/***' \
	--include='/bitrix/services/***' \
	--include='/bitrix/themes/***' \
	--include='/bitrix/wizards/***' \
	--include='/bitrix/activities/***' \
	--include='/bitrix/gadgets/***' \
	--include='/bitrix/blocks/***' \
	--include='/bitrix/modules/bitrix.***' \
	--include='/bitrix/modules/main/***' \
	--include='/bitrix/modules/iblock/***' \
	--include='/bitrix/modules/catalog/***' \
	--include='/bitrix/modules/sale/***' \
	--include='/bitrix/components/bitrix/***' \
	--exclude='*' \
	"${SSH_TARGET}:${REMOTE_DOCROOT}/" "${REPO_ROOT}/"

echo "==> Done. Open http://itweb-new.local/ (import DB separately if needed)."
