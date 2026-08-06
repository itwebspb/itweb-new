#!/usr/bin/env bash
# Run ON THE REMOTE SERVER inside the site document root after code was merged to master.
# Typical flow: ssh itweb-new-test → cd <docroot> → bash scripts/deploy-on-server.sh
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BRANCH="${DEPLOY_BRANCH:-master}"

cd "${REPO_ROOT}"

echo "==> Fetching origin/${BRANCH}"
git fetch origin "${BRANCH}"
git checkout "${BRANCH}"
git pull --ff-only origin "${BRANCH}"

if [ -d bitrix/cache ]; then
	echo "==> Clearing Bitrix cache"
	find bitrix/cache bitrix/managed_cache bitrix/stack_cache -mindepth 1 -maxdepth 1 -exec rm -rf {} + 2>/dev/null || true
fi

if [ -d bitrix/html_pages ]; then
	rm -rf bitrix/html_pages/*
fi

echo "==> Deploy complete ($(git rev-parse --short HEAD))"
