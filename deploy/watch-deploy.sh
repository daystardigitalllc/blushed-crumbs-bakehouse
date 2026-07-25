#!/bin/bash
set -e

cd "$(dirname "$0")/.."

STATE_FILE="storage/.last_deployed_sha"
LOG_FILE="storage/logs/deploy.log"

CURRENT_SHA=$(git rev-parse HEAD)
LAST_SHA=""
if [ -f "$STATE_FILE" ]; then
    LAST_SHA=$(cat "$STATE_FILE")
fi

if [ "$CURRENT_SHA" = "$LAST_SHA" ]; then
    exit 0
fi

{
    echo "===== $(date) ====="
    echo "Deploying $LAST_SHA -> $CURRENT_SHA"
    bash deploy/post-deploy.sh
    echo "$CURRENT_SHA" > "$STATE_FILE"
    echo "Deploy finished successfully."
} >> "$LOG_FILE" 2>&1
