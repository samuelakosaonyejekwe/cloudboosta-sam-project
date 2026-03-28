#!/usr/bin/env bash
set -euo pipefail

# Usage:
#   DB_HOST=... DB_USER=... DB_PASS=... DB_PORT=3306 ./reset_db.sh
#
# This script is intended for labs only.

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USER:-admin}"
DB_PASS="${DB_PASS:-}"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "Applying migrate.sql..."
mysql -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" -p"${DB_PASS}" < "${SCRIPT_DIR}/migrate.sql"

echo "Applying seed.sql..."
mysql -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" -p"${DB_PASS}" < "${SCRIPT_DIR}/seed.sql"

echo "Done."
