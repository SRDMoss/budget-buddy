#!/usr/bin/env bash
set -euo pipefail

ROOT="/var/www/html/budget-buddy"
EMAIL="demo@bb.local"
PASSWORD="password"
PHP="/usr/bin/php"

cd "$ROOT"

# 1) Purge demo data
$PHP "$ROOT/api/scripts/purge_demo.php" --email="$EMAIL"

# 2) Seed from Jan 2024 through current month (deterministic per month)
start="2024-01-01"
end="$(date +%Y-%m-01)"
d="$start"
while [ "$(date -d "$d" +%Y-%m)" != "$(date -d "$end +1 month" +%Y-%m)" ]; do
  m="$(date -d "$d" +%Y-%m)"
  echo "Seeding $m ..."
  $PHP "$ROOT/api/scripts/seed_month.php" --month="$m" --email="$EMAIL" --password="$PASSWORD" --clear
  d="$(date -d "$d +1 month" +%Y-%m-01)"
done

echo "Reseed complete."
