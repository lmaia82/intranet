# ad-bridge-spi

Custom User Storage Provider do Keycloak (`ad-bridge`). Substitui a
federação LDAP nativa: em vez de o Keycloak fazer bind no AD com uma conta
de serviço própria, ele delega a validação de senha para o endpoint interno
`POST /internal/ad-auth` do `intranet-new` (ver `AdBridgeController` e
`ActiveDirectoryAuthenticator` no projeto principal).

## Build

Não precisa de Java/Maven instalado localmente — o `../Dockerfile` compila
tudo dentro do build da imagem do Keycloak:

```bash
cd ..
docker compose build keycloak
```

Para compilar isoladamente (ex: checar erros de compilação sem build da
imagem inteira):

```bash
docker run --rm -v "$PWD":/app -w /app -v maven-repo-cache:/root/.m2 \
  maven:3.9-eclipse-temurin-21 mvn -B package
```

O jar sai em `target/ad-bridge-spi.jar`.

## Estrutura

- `AdBridgeUserStorageProviderFactory` — registra o provider `ad-bridge` e
  seus campos de config (URL da ponte, segredo, timeout) na tela Realm →
  User Federation.
- `AdBridgeUserStorageProvider` — implementa `CredentialInputValidator`
  (`isValid`): chama a ponte HTTP a cada tentativa de login, nunca guarda
  senha.
- `AdBridgeUserAdapter` — modelo de usuário "virtual" (sem busca prévia no
  AD); atributos (nome, e-mail, admin) só são preenchidos depois de um
  login bem-sucedido, com o que a ponte devolveu.

## Compatibilidade de versão

`pom.xml` fixa `keycloak.version=26.7.1` para bater com a tag usada em
`../Dockerfile` (`quay.io/keycloak/keycloak:26.7.1`). Se atualizar a versão
do Keycloak, atualize as duas em conjunto — as APIs de SPI (em especial
`AbstractUserAdapterFederatedStorage`) mudam entre versões maiores.
