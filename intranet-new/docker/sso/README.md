# SSO da intranet — visão geral

Infraestrutura de SSO para o `intranet-new`, pensada para permitir que
**outros sistemas internos** se autentiquem contra o mesmo AD sem cada um
reimplementar a integração — e sem criar uma conta de serviço no diretório
(decisão do CETEM, ver `app/Services/ActiveDirectoryAuthenticator.php`).

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

## Ordem de subida

```bash
cd step-ca && docker compose up -d && cd ..
cd keycloak && docker compose build keycloak && docker compose up -d && cd ..
```

O `intranet-new` (Laravel) continua rodando como já roda hoje (fora de
Docker) — só precisa do `SSO_BRIDGE_SECRET` no `.env` (já gerado) e estar
acessível pelo container do Keycloak via `host.docker.internal`.

## O que falta decidir/ajustar antes de produção

- [ ] Hostnames reais (só ficam definidos quando a intranet for pra
      produção — combinado anteriormente).
- [ ] Porta/vhost real do `intranet-new` para a URL da ponte no Keycloak
      (`http://host.docker.internal/internal/ad-auth`, ajustar porta).
- [ ] Rodar `php artisan route:list` / testar o endpoint
      `/internal/ad-auth` de ponta a ponta com um usuário real do AD.
- [ ] Criar o realm e o primeiro client OIDC (intranet-new) no Keycloak —
      ver `keycloak/README.md`.
- [ ] Considerar `KC_HOSTNAME_STRICT=true` e revisão de segurança antes do
      go-live (endpoint interno não pode ficar acessível no vhost público).
