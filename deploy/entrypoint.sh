#!/bin/sh
set -e

if [ -n "$REPO_URL" ] && [ ! -d "$REPO_PATH/.git" ]; then
    git clone "$REPO_URL" "$REPO_PATH"
fi

exec python3 /app/webhook.py
