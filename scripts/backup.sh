#!/usr/bin/env bash
# PKSB nightly backup. Run via cron on the production box:
#   15 3 * * * /var/www/pksb/scripts/backup.sh >> /var/log/pksb-backup.log 2>&1
#
# Snapshots all JSON stores + uploaded files, keeps 14 days, prunes the rest.

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DEST="$ROOT/storage/backups"
STAMP="$(date +%Y%m%d-%H%M%S)"
ARCHIVE="$DEST/pksb-$STAMP.tgz"

mkdir -p "$DEST"

tar -czf "$ARCHIVE" \
  -C "$ROOT" \
  storage/app/posts.json \
  storage/app/passes.json \
  storage/app/timetables.json \
  storage/app/users.json \
  storage/app/public/destinations \
  storage/app/public/timetables \
  storage/app/public/passes \
  2>/dev/null || true

# Prune older than 14 days
find "$DEST" -type f -name 'pksb-*.tgz' -mtime +14 -delete

echo "[$(date -u +%FT%TZ)] backup OK -> $ARCHIVE ($(du -h "$ARCHIVE" | cut -f1))"
