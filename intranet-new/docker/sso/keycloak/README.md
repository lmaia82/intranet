# Keycloak — SSO da intranet (sem conta de serviço no AD)

Stack: `keycloak-db` (Postgres) + `keycloak` (build customizado, com o SPI
`ad-bridge` embutido) + `caddy` (proxy TLS via ACME contra o
[step-ca](../step-ca)). Depende da rede/volume criados pelo stack do
step-ca — **suba o step-ca primeiro**.

## Por que não é LDAP federation

O CETEM decidiu, de propósito, não ter uma conta de serviço fixa para o AD
(ver comentário em `app/Services/ActiveDirectoryAuthenticator.php` no
projeto principal). A federação LDAP nativa do Keycloak exige uma conta de
bind armazenada — então, em vez dela, o Keycloak usa um **User Storage
Provider customizado** (`ad-bridge-spi/`) que delega a validação de senha
para um endpoint interno do próprio `intranet-new`
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

## Antes de subir

1. Confirme que o step-ca já está rodando (`cd ../step-ca && docker compose up -d`) —
   é ele quem cria a network `intranet-sso-net` e o volume `intranet-ca-data`
   que este stack referencia como `external`.
2. Troque o placeholder `sso.intranet.local` (em `docker-compose.yml` e em
   `Caddyfile`) pelo hostname real do Keycloak.
3. Garanta que `SSO_BRIDGE_SECRET` está definido no `.env` do `intranet-new`
   (já foi gerado automaticamente) e que o `intranet-new` está rodando e
   acessível a partir do container do Keycloak.
4. Como o `intranet-new` roda direto no host (não é Docker), o serviço
   `keycloak` já tem `extra_hosts: host.docker.internal:host-gateway` — use
   `http://host.docker.internal/internal/ad-auth` como URL da ponte (ajuste
   a porta/vhost se o Apache/Nginx do intranet-new não escutar na 80 do
   host).

## Subir

```bash
cd docker/sso/keycloak
docker compose build keycloak   # compila o SPI (Maven) e monta a imagem
docker compose up -d
docker compose logs -f keycloak
```

Login inicial: usuário `admin`, senha em `secrets/kc_admin_password.txt`.

> Validado nesta sessão: o build compila o `ad-bridge-spi` de verdade
> (Maven contra as libs do Keycloak 26.7.1), o Keycloak sobe sem erro e o
> provider aparece registrado em `GET /admin/serverinfo` →
> `providers.storage.providers` → `ad-bridge`.

## 1. Configurar o provider `ad-bridge`

No console admin: **Realm → User Federation → Add provider → ad-bridge**.

| Campo | Valor |
|---|---|
| Console Display Name | `AD via intranet-new` |
| URL da ponte AD | `http://host.docker.internal/internal/ad-auth` (ajuste porta/vhost) |
| Segredo compartilhado | o mesmo valor de `SSO_BRIDGE_SECRET` no `.env` do `intranet-new` |
| Timeout (ms) | `5000` (padrão) |

Não existe "Test authentication" nem "Sync users" — não há busca no AD, só
validação no momento do login (igual ao comportamento do
`ActiveDirectoryAuthenticator` hoje). O primeiro login de cada usuário já
autentica e preenche nome/e-mail vindos da resposta da ponte.

## 2. Client OIDC para a intranet PHP

**Realm → Clients → Create client**

- Client ID: `intranet-php`
- Client authentication: `On` (confidential client)
- Standard flow: `On` (Authorization Code)
- Valid redirect URIs: `https://intranet.intranet.local/auth/callback` (ajuste)
- Valid post logout redirect URIs: `https://intranet.intranet.local/`

Pegue o **Client secret** na aba *Credentials* depois de criado.

## 3. Outros sistemas da intranet

Esse é o objetivo de ter o Keycloak: qualquer novo sistema interno vira só
mais um **client OIDC** (passo 2, repetido) apontando para
`https://<hostname-do-keycloak>/realms/<realm>` — nenhum deles precisa
saber falar com o AD, o Keycloak já centraliza isso via `ad-bridge`.

## Notas de produção

- `KC_HOSTNAME_STRICT=false` está OK para ambiente interno; trave (`true`)
  quando o hostname final de produção estiver definido.
- O `caddy` renova certificados automaticamente via ACME contra o step-ca.
- Se mudar o SPI (`ad-bridge-spi/src`), rode `docker compose build keycloak`
  de novo antes do `up -d` — o Dockerfile recompila o jar a cada build.
- Faça backup do volume `keycloak-db-data` (realms, clients — não guarda
  nenhuma senha de usuário do AD, já que a validação nunca é armazenada).
