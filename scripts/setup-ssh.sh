#!/usr/bin/env bash
# Configure SSH access to the test server from a private key.
#
# Usage (local machine or Cloud Agent secret):
#   export ITWEB_NEW_SSH_KEY="$(cat ~/.ssh/itweb-new_ed25519)"
#   bash scripts/setup-ssh.sh
#
# Optional overrides:
#   ITWEB_NEW_SSH_HOST  (default: itweb-new.acrobat.test-itweb.ru)
#   ITWEB_NEW_SSH_USER  (default: itweb-new)
#   ITWEB_NEW_SSH_PORT  (default: 22)
set -euo pipefail

HOST="${ITWEB_NEW_SSH_HOST:-itweb-new.acrobat.test-itweb.ru}"
USER="${ITWEB_NEW_SSH_USER:-itweb-new}"
PORT="${ITWEB_NEW_SSH_PORT:-22}"
KEY_PATH="${HOME}/.ssh/itweb-new_ed25519"
CONFIG_PATH="${HOME}/.ssh/config"

if [ -z "${ITWEB_NEW_SSH_KEY:-}" ]; then
	echo "Set ITWEB_NEW_SSH_KEY to the private key contents, then rerun." >&2
	exit 1
fi

umask 077
mkdir -p "${HOME}/.ssh"
printf '%s\n' "${ITWEB_NEW_SSH_KEY}" > "${KEY_PATH}"
chmod 600 "${KEY_PATH}"

if ! grep -q "Host itweb-new-test" "${CONFIG_PATH}" 2>/dev/null; then
	cat >> "${CONFIG_PATH}" <<EOF

Host itweb-new-test
	HostName ${HOST}
	User ${USER}
	Port ${PORT}
	IdentityFile ${KEY_PATH}
	IdentitiesOnly yes
	StrictHostKeyChecking accept-new
EOF
	chmod 600 "${CONFIG_PATH}"
fi

ssh -o BatchMode=yes itweb-new-test "echo 'SSH OK:' \$(hostname) && pwd"
