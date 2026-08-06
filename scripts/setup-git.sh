#!/usr/bin/env bash
# Recommended local Git settings for itweb-new (safe to run repeatedly).
set -euo pipefail

git config fetch.prune true
git config pull.rebase false
git config push.default simple
git config init.defaultBranch master

# Keep merge commits on master for a clear deploy history.
git config branch.master.mergeoptions "--no-ff"

echo "Git settings applied for $(git rev-parse --show-toplevel)"
