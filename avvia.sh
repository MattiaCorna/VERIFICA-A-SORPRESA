#!/usr/bin/env bash
set -e

PORT="${PORT:-8000}"

echo "Avvio server PHP su porta ${PORT}..."
echo "API: /api/1"
echo "URL Codespaces: https://${CODESPACE_NAME}-${PORT}.app.github.dev/api/1"

php -S 0.0.0.0:${PORT} router.php