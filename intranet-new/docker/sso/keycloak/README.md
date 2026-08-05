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
| Client `account-console` | redirectUris ajustado pra incluir `/sso-test/realms/intranet/account/*` (ver seção de teste abaixo) |
| Required action `VERIFY_PROFILE` | **desabilitado no realm** (decisão permanente, não só do teste — ver por quê abaixo) |

### ✅ Validado ponta a ponta com usuário real do AD

Testado via Direct Grant (`grant_type=password`, temporário, já revertido)
com e-mail e senha reais de um usuário do CETEM — voltou um `access_token`
de verdade. Prova que Keycloak → SPI `ad-bridge` → `/internal/ad-auth` →
`ActiveDirectoryAuthenticator` → bind real no AD funcionam de ponta a
ponta, com credencial genuína (não só o caminho de erro).

No caminho, dois problemas de configuração do realm apareceram e foram
corrigidos (nenhum dos dois é bug do SPI/ponte):

1. **`account-console` com redirect URI errado.** O client automático do
   Keycloak só tinha `/realms/intranet/account/*` — sem o prefixo
   `/sso-test` usado no proxy de teste (ver abaixo). Corrigido adicionando
   a variante com prefixo.
2. **`VERIFY_PROFILE` bloqueando o Direct Grant** com
   `{"error":"invalid_grant","error_description":"Account is not fully set up"}`.
   Esse required action do Keycloak (parte do "User Profile" declarativo)
   não faz sentido pra usuários federados via `ad-bridge`: o perfil "de
   verdade" é o AD, e os atributos (nome, e-mail) só ficam disponíveis
   *depois* da validação de senha, não antes — então o Keycloak nunca vai
   considerar o perfil "completo" antes do primeiro login. Desabilitado
   permanentemente pro realm `intranet`.

### Proxy `/sso-test` — só existe no container ao vivo, não em código

A SPA nova (React) do Account Console do Keycloak **não funciona** direito
atrás de um path prefix como `/sso-test` (fica carregando pra sempre —
problema conhecido do Keycloak com apps React sob subpath). Pra testar o
login mesmo assim, sem depender da porta 8090 (bloqueada por firewall de
rede, fora do meu alcance resolver), montei um proxy reverso **direto no
container `intranet-app-1` já rodando** (`a2enmod proxy proxy_http` +
`/etc/apache2/conf-available/sso-test-proxy.conf` + `apache2ctl graceful`).

**Isso não está em nenhum Dockerfile nem arquivo versionado** — foi feito
com `docker exec` num container que já estava de pé há 12 dias. Se esse
container for recriado (não só reiniciado), o proxy some. Pra refazer:

```bash
docker exec intranet-app-1 a2enmod proxy proxy_http
docker exec intranet-app-1 sh -c "cat > /etc/apache2/conf-available/sso-test-proxy.conf << 'EOF'
ProxyPreserveHost On
ProxyPass /sso-test http://keycloak:8080/sso-test
ProxyPassReverse /sso-test http://keycloak:8080/sso-test
EOF
a2enconf sso-test-proxy"
docker exec intranet-app-1 apache2ctl graceful
```

Pra testar login via linha de comando (recomendado — a SPA não funciona
sob o prefixo, use isso em vez da URL do navegador):

```bash
curl -s -X POST http://<host>/sso-test/realms/intranet/protocol/openid-connect/token \
  -d "client_id=intranet-php" \
  -d "client_secret=<client secret>" \
  -d "grant_type=password" \
  --data-urlencode "username=<email>" \
  --data-urlencode "password=<senha>"
```

(Precisa reativar `directAccessGrantsEnabled=true` no client pra isso
funcionar — reverta depois do teste, não é pra ficar ligado.)

### Recriar do zero (ex: depois de um `docker compose down -v`)

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

# Necessário pra usuários federados via ad-bridge (ver explicação acima).
docker exec keycloak $KC update authentication/required-actions/VERIFY_PROFILE -r intranet -s enabled=false
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
