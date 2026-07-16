#!/usr/bin/env bash
# Avisa a intranet que um documento terminou o processamento de OCR.
# Variáveis DOCUMENT_ID etc. são injetadas pelo próprio paperless-ngx.
# INTRANET_WEBHOOK_URL / INTRANET_WEBHOOK_SECRET vêm do ambiente do container.

set -euo pipefail

if [ -z "${INTRANET_WEBHOOK_URL:-}" ]; then
    exit 0
fi

curl -fsS -m 10 -X POST "$INTRANET_WEBHOOK_URL" \
    -H "Content-Type: application/json" \
    -H "X-Webhook-Secret: ${INTRANET_WEBHOOK_SECRET:-}" \
    -d "{\"document_id\": ${DOCUMENT_ID}}" \
    || true
