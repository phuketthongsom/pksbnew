#!/usr/bin/env bash
# R2: prove that scripts/backup.sh actually produces a restorable archive.
#
# What it does:
#   1. Takes a fresh backup
#   2. Extracts it into a throwaway temp dir
#   3. Diffs the JSON files vs. the originals
#   4. Reports OK or FAIL and cleans up
#
# Run this monthly — before you trust the cron'd backup, prove it works.
# Suggested cron (1st of the month):
#   0 4 1 * * /var/www/pksb/scripts/restore-test.sh >> /var/log/pksb-restore-test.log 2>&1

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
TMP="$(mktemp -d -t pksb-restore-XXXXXX)"
trap 'rm -rf "$TMP"' EXIT

echo "[$(date -u +%FT%TZ)] restore-test starting"

# 1. Take a fresh backup
"$ROOT/scripts/backup.sh"

# 2. Find the newest archive
ARCHIVE="$(ls -1t "$ROOT/storage/backups"/pksb-*.tgz 2>/dev/null | head -1 || true)"
if [ -z "$ARCHIVE" ]; then
  echo "FAIL: backup.sh did not produce an archive."
  exit 1
fi
echo "Newest archive: $ARCHIVE"

# 3. Extract to temp
tar -xzf "$ARCHIVE" -C "$TMP"

# 4. Diff each JSON file against the live one
FAIL=0
for f in posts.json passes.json timetables.json users.json; do
  LIVE="$ROOT/storage/app/$f"
  RESTORED="$TMP/storage/app/$f"
  if [ ! -f "$LIVE" ]; then continue; fi
  if [ ! -f "$RESTORED" ]; then
    echo "FAIL: $f missing from archive"
    FAIL=1
    continue
  fi
  if ! diff -q "$LIVE" "$RESTORED" >/dev/null; then
    echo "FAIL: $f content mismatch"
    FAIL=1
  else
    echo "OK:   $f"
  fi
done

if [ "$FAIL" -ne 0 ]; then
  echo "[$(date -u +%FT%TZ)] restore-test FAILED"
  exit 1
fi
echo "[$(date -u +%FT%TZ)] restore-test OK"
