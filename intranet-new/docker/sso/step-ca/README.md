# step-ca — CA interna para certificados do SSO da intranet

CA privada (Smallstep `step-ca`) com provisioner **ACME** habilitado, para emitir
certificados TLS internamente (ex: para o Keycloak, para o reverse proxy da
intranet, etc.) sem depender de CA pública.

## Antes de subir

1. Ajuste `DOCKER_STEPCA_INIT_DNS_NAMES` no `docker-compose.yml` para os
   hostnames reais que a CA vai usar na rede interna (hoje está com
   placeholders `ca.intranet.local` / `step-ca`).
2. O arquivo `secrets/ca_password.txt` já foi gerado com uma senha aleatória
   (protege a chave privada root/intermediate). **Não versione essa pasta**
   (já está no `.gitignore`) — guarde uma cópia da senha em um cofre
   (Vault/Bitwarden/1Password) caso precise reinicializar a CA depois.

## Subir o container

```bash
docker compose up -d
docker compose logs -f step-ca   # confirmar que a inicialização terminou
```

No primeiro start, a imagem gera automaticamente:
- Root CA e Intermediate CA (chave e certificado)
- Um provisioner JWK chamado `admin`
- Um provisioner **ACME** (para emissão automática de certificados)

## Pegar o certificado root (para distribuir/confiar nos outros serviços)

```bash
docker compose exec step-ca step ca root
# ou, copiando o arquivo para fora do container:
docker compose cp step-ca:/home/step/certs/root_ca.crt ./root_ca.crt
```

Esse `root_ca.crt` é o que você vai instalar/confiar:
- No **Keycloak** (truststore Java, ou via `KC_TRUSTSTORE_PATHS`), para ele
  aceitar o próprio certificado TLS emitido por essa CA.
- No **reverse proxy** da intranet (Caddy/Traefik/nginx), se ele for
  configurado para usar ACME apontando pra essa CA.
- Nas máquinas/navegadores da rede interna, se quiser eliminar avisos de
  certificado não confiável ao acessar `https://` na intranet.

## Endpoint ACME

```
https://<host-da-ca>:9000/acme/acme/directory
```

Exemplo com Caddy na frente do Keycloak (ACME automático contra essa CA):

```caddyfile
{
    acme_ca https://ca.intranet.local:9000/acme/acme/directory
}

sso.intranet.local {
    reverse_proxy keycloak:8080
}
```

(O Caddy precisa confiar no root da CA — ver seção anterior.)

## Próximos passos (combinados na conversa)

- [ ] Subir o **Keycloak** com **User Federation LDAP** em modo `READ_ONLY`
      apontando pro AD, reaproveitando a mesma service account de leitura
      que a intranet PHP já usa hoje para bind/verificação de senha.
- [ ] Configurar client OIDC no Keycloak para a aplicação PHP da intranet.
- [ ] Colocar um reverse proxy (Caddy sugerido) na frente do Keycloak,
      usando ACME contra esta CA para TLS automático.

Me avise quando quiser que eu monte o Keycloak (compose + configuração de
federação LDAP read-only) — sigo a partir daqui.
