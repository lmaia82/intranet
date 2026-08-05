#!/bin/sh
set -e

# A imagem oficial do Keycloak NÃO suporta a convenção "_FILE" pra secrets
# do Docker (diferente da imagem do Postgres) — KC_DB_PASSWORD_FILE e
# KEYCLOAK_ADMIN_PASSWORD_FILE sozinhos são ignorados, e o Keycloak falha
# achando que a senha está vazia. Este wrapper lê o arquivo e exporta a
# variável "de verdade" antes de chamar o kc.sh real.

if [ -n "${KC_DB_PASSWORD_FILE:-}" ] && [ -f "$KC_DB_PASSWORD_FILE" ]; then
  KC_DB_PASSWORD="$(cat "$KC_DB_PASSWORD_FILE")"
  export KC_DB_PASSWORD
fi

if [ -n "${KEYCLOAK_ADMIN_PASSWORD_FILE:-}" ] && [ -f "$KEYCLOAK_ADMIN_PASSWORD_FILE" ]; then
  KEYCLOAK_ADMIN_PASSWORD="$(cat "$KEYCLOAK_ADMIN_PASSWORD_FILE")"
  export KEYCLOAK_ADMIN_PASSWORD
fi

exec /opt/keycloak/bin/kc.sh "$@"
