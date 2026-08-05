# Keycloak — SSO da intranet (sem conta de serviço no AD)

Stack: `keycloak-db` (Postgres) + `keycloak` (build customizado, com o SPI
`ad-bridge` embutido) + `caddy` (proxy TLS via ACME contra o
[step-ca](../step-ca)). Depende da rede/volume criados pelo stack do
step-ca — **suba o step-ca primeiro**.

## Por que não é LDAP federation

O CETEM decidiu, de propósito, não ter uma conta de serviço fixa para o AD
(ver comentário em `app/Services/ActiveDirectoryAuthenticator.php`, branch
`feature/notificacao-informativos`). A federação LDAP nativa do Keycloak
exige uma conta de bind armazenada — então, em vez dela, o Keycloak usa um
**User Storage Provider customizado** (`ad-bridge-spi/`) que delega a
validação de senha para um endpoint interno do próprio `intranet-new`
(`POST /internal/ad-auth`), reaproveitando o `ActiveDirectoryAuthenticator`
que já existe: bind direto com a credencial do próprio usuário, nunca uma
conta de serviço.

```
usuário → login no Keycloak → SPI ad-bridge → POST /internal/ad-auth (Laravel)
                                                        │
                                                        ▼
                                    ActiveDirectoryAuthenticator::autenticar()
                                                        │
                                                        ▼
                                          bind direto no AD com a própria senha
```

> ⚠️ **Este SSO só existe na branch `feature/sso-on-notificacoes`** — o
> `ActiveDirectoryAuthenticator` (e ~150 commits de trabalho relacionado)
> vive em `feature/notificacao-informativos`, não na `main`. Rodar esse
> stack em cima da `main` sozinha quebra com 500 (`ActiveDirectoryAuthenticator.php: No such file or directory`).

## Rede: o app roda em Docker, não no host

O `intranet-new` roda containerizado (`intranet-app-1`, projeto compose
`intranet` definido em `docker-compose.yml` na **raiz do repo**, rede
`intranet_default`) — não é "direto no host" como uma versão anterior deste
README dizia. O serviço `keycloak` aqui entra também na rede
`intranet_default` (`external: true`) e fala com o app por
`http://app/internal/ad-auth`, container-a-container.

## Portas (conflitos com o que já roda nesta máquina)

| Serviço | Porta do host | Motivo |
|---|---|---|
| `step-ca` | `9099` (não 9000) | 9000 já é do MinIO (`intranet-minio-1`) |
| `caddy` (este stack) | `8080`/`8443` (não 80/443) | 80/443 já são do `intranet-app-1` |

Isso é provisório — a topologia real de proxy/hostname só se define quando
o hostname de produção estiver decidido.

## Antes de subir

1. Confirme que o step-ca já está rodando (`cd ../step-ca && docker compose up -d`).
2. Garanta que `SSO_BRIDGE_SECRET` está no `.env` do `intranet-new` (já
   gerado) e que o container `app` (raiz do repo, `docker compose up -d`)
   está de pé.

## Subir

```bash
cd docker/sso/keycloak
docker compose build keycloak   # compila o SPI (Maven) e monta a imagem
docker compose up -d
docker compose logs -f keycloak
```

Login inicial: usuário `admin`, senha em `secrets/kc_admin_password.txt`.

### Troubleshooting: `KC_DB_PASSWORD_FILE` não funciona sozinho

Diferente da imagem do Postgres, a imagem oficial do Keycloak **não**
suporta a convenção `_FILE` nativamente — `KC_DB_PASSWORD_FILE` e
`KEYCLOAK_ADMIN_PASSWORD_FILE` sozinhos são ignorados, e o Keycloak morre
com `PSQLException: The server requested SCRAM-based authentication, but
no password was provided`. Corrigido com um entrypoint wrapper
(`docker-entrypoint-secrets.sh`, embutido no `Dockerfile`) que lê o arquivo
do secret e exporta a variável "de verdade" antes de chamar o `kc.sh` real.

## O que já está configurado (feito nesta sessão via `kcadm.sh`)

| Objeto | Valor |
|---|---|
| Realm | `intranet` |
| Provider `ad-bridge` | aponta pra `http://app/internal/ad-auth`, segredo = `SSO_BRIDGE_SECRET` |
| Client OIDC | `intranet-php` (confidential, Authorization Code, `directAccessGrantsEnabled=false`) |

Validado ponta a ponta: um Direct Grant de teste (temporário, revertido
depois) contra `POST /realms/intranet/protocol/openid-connect/token` com
credenciais falsas voltou `{"error":"invalid_grant","error_description":
"Invalid user credentials"}` — prova que Keycloak → SPI `ad-bridge` →
`/internal/ad-auth` → `ActiveDirectoryAuthenticator` → bind real no AD
funcionam de ponta a ponta.

Pra recriar do zero (ex: depois de um `docker compose down -v`):

```bash
KC=/opt/keycloak/bin/kcadm.sh
ADMIN_PW=$(docker exec keycloak cat /run/secrets/kc_admin_password)
docker exec keycloak $KC config credentials --server http://localhost:8080 --realm master --user admin --password "$ADMIN_PW"
docker exec keycloak $KC create realms -s realm=intranet -s enabled=true

SECRET=$(grep '^SSO_BRIDGE_SECRET=' ../../../.env | cut -d= -f2-)
docker exec keycloak $KC create components -r intranet \
  -s name=ad-bridge -s providerId=ad-bridge -s providerType=org.keycloak.storage.UserStorageProvider \
  -s 'config.bridgeUrl=["http://app/internal/ad-auth"]' \
  -s "config.bridgeSecret=[\"$SECRET\"]" \
  -s 'config.timeoutMs=["5000"]'

docker exec keycloak $KC create clients -r intranet \
  -s clientId=intranet-php -s enabled=true -s publicClient=false -s standardFlowEnabled=true \
  -s 'redirectUris=["https://intranet.intranet.local/auth/callback"]' \
  -s 'webOrigins=["https://intranet.intranet.local"]'
```

## Outros sistemas da intranet

Esse é o objetivo de ter o Keycloak: qualquer novo sistema interno vira só
mais um **client OIDC** (mesmo formato do `intranet-php` acima) apontando
para `https://<hostname-do-keycloak>/realms/intranet` — nenhum deles
precisa saber falar com o AD, o Keycloak já centraliza isso via `ad-bridge`.

## Notas de produção

- `KC_HOSTNAME_STRICT=false` está OK para ambiente interno; trave (`true`)
  quando o hostname final de produção estiver definido.
- O `caddy` renova certificados automaticamente via ACME contra o step-ca
  — mas ainda não foi testado (portas provisórias, hostname placeholder).
- Se mudar o SPI (`ad-bridge-spi/src`), rode `docker compose build keycloak`
  de novo antes do `up -d`.
- Faça backup do volume `keycloak-db-data` (realms, clients — não guarda
  nenhuma senha de usuário do AD).
- `KEYCLOAK_ADMIN`/`KEYCLOAK_ADMIN_PASSWORD` estão deprecated (o próprio
  Keycloak avisa no log) em favor de `KC_BOOTSTRAP_ADMIN_USERNAME`/
  `KC_BOOTSTRAP_ADMIN_PASSWORD` — funciona, mas vale migrar depois.
