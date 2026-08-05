# SSO da intranet — visão geral

Infraestrutura de SSO para o `intranet-new`, pensada para permitir que
**outros sistemas internos** se autentiquem contra o mesmo AD sem cada um
reimplementar a integração — e sem criar uma conta de serviço no diretório
(decisão do CETEM, ver `app/Services/ActiveDirectoryAuthenticator.php`,
branch `feature/notificacao-informativos`).

> ⚠️ Este diretório só faz sentido em cima da `feature/notificacao-informativos`
> — é de onde vem o `ActiveDirectoryAuthenticator`. Branch ativa com tudo
> isso: **`feature/sso-on-notificacoes`**. Rodar isso sozinho em cima da
> `main` quebra (falta o `ActiveDirectoryAuthenticator.php`).

## Componentes

| Diretório | O que é |
|---|---|
| [`step-ca/`](step-ca/) | CA interna (Smallstep), emite os certificados TLS via ACME — inclusive o do Keycloak. |
| [`keycloak/`](keycloak/) | Hub de SSO (OIDC/SAML). Outros sistemas só precisam virar um "client" nele. |
| [`keycloak/ad-bridge-spi/`](keycloak/ad-bridge-spi/) | Provider customizado do Keycloak: delega a checagem de senha pro Laravel em vez de LDAP bind com conta de serviço. |
| `../../app/Http/Controllers/Internal/AdBridgeController.php` (no repo principal) | Endpoint interno que o SPI chama — reusa `ActiveDirectoryAuthenticator` já existente. |

## Fluxo de autenticação

```
┌─────────────┐     login      ┌───────────┐   isValid()   ┌──────────────────┐   autenticar()   ┌───────────────────────────┐
│  Usuário     │ ─────────────▶ │ Keycloak  │ ─────────────▶│ SPI ad-bridge     │ ────────────────▶│ POST /internal/ad-auth     │
│ (navegador) │                │           │               │ (Java)            │  HTTP + segredo   │ (intranet-new / Laravel)   │
└─────────────┘                └───────────┘               └──────────────────┘                   └──────────┬────────────────┘
                                                                                                                │
                                                                                                     ActiveDirectoryAuthenticator
                                                                                                                │
                                                                                                                ▼
                                                                                                    bind direto no AD (LDAP),
                                                                                                    com a senha do próprio usuário
```

Nenhum componente novo guarda senha ou tem conta de serviço no AD — a
única mudança de superfície é que a checagem, que hoje só acontece na
importação em lote, passa a acontecer também a cada login via SSO.

**Validado de ponta a ponta nesta sessão** (não é só teoria — rodando de
verdade nesta máquina): step-ca healthy, Keycloak healthy com realm
`intranet` + provider `ad-bridge` + client `intranet-php` já criados, e um
Direct Grant de teste contra credenciais falsas retornou
`invalid_grant / Invalid user credentials` — prova que a cadeia inteira
(Keycloak → SPI → `/internal/ad-auth` → `ActiveDirectoryAuthenticator` →
bind real no AD em `172.16.0.53`) está funcionando.

## O app roda em Docker (correção de uma versão anterior deste README)

`intranet-new` roda no container `intranet-app-1`, projeto compose
`intranet` (raiz do repo, não dentro de `intranet-new/`), rede
`intranet_default`. O `keycloak` deste stack entra nessa mesma rede pra
falar com `http://app/internal/ad-auth` — não é `host.docker.internal`
como uma versão anterior deste README assumia (checagem incompleta na
hora, só tinha olhado dentro de `intranet-new/`).

## Ordem de subida

```bash
# na raiz do repo — o app, mysql, redis, minio
docker compose up -d

cd intranet-new/docker/sso/step-ca && docker compose up -d && cd -
cd intranet-new/docker/sso/keycloak && docker compose build keycloak && docker compose up -d && cd -
```

Portas do host remapeadas pra não conflitar com o que já roda nesta
máquina (MinIO na 9000, `intranet-app-1` na 80): step-ca em `9099`, Caddy
deste stack em `8080`/`8443`. Detalhes em `keycloak/README.md`.

## O que falta decidir/ajustar antes de produção

- [ ] Reconciliar `feature/sso-on-notificacoes` com `main` — hoje a `main`
      está 150+ commits atrás de `feature/notificacao-informativos`
      (decisão de quando/como mesclar é do time, não foi tomada aqui).
- [ ] Hostnames reais (só ficam definidos quando a intranet for pra
      produção).
- [ ] Portas/topologia definitivas do proxy (hoje 8080/8443 provisórios).
- [ ] Testar login de verdade com um usuário real do AD (só foi validado
      o caminho de credencial inválida até agora).
- [ ] Considerar `KC_HOSTNAME_STRICT=true` e revisão de segurança antes do
      go-live (endpoint interno não pode ficar acessível no vhost público).
