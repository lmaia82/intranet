# Compila o SPI customizado (ad-bridge-spi/) e o empacota junto com o
# Keycloak oficial, para não depender de um jar pré-compilado fora do Docker.
FROM maven:3.9-eclipse-temurin-21 AS spi-build
WORKDIR /build
COPY ad-bridge-spi/pom.xml .
# Baixa as dependências numa camada separada, cacheável, antes de copiar o
# código-fonte (que muda com muito mais frequência que o pom.xml).
RUN mvn -B -q dependency:go-offline || true
COPY ad-bridge-spi/src ./src
RUN mvn -B -q package

FROM quay.io/keycloak/keycloak:26.7.1
COPY --from=spi-build /build/target/ad-bridge-spi.jar /opt/keycloak/providers/ad-bridge-spi.jar
# A imagem oficial não lê KC_DB_PASSWORD_FILE/KEYCLOAK_ADMIN_PASSWORD_FILE
# sozinha (só a convenção "_FILE" do Postgres, não é genérica) — ver o
# comentário dentro do script.
USER root
COPY docker-entrypoint-secrets.sh /opt/keycloak/bin/docker-entrypoint-secrets.sh
RUN chmod +x /opt/keycloak/bin/docker-entrypoint-secrets.sh
USER 1000
ENTRYPOINT ["/opt/keycloak/bin/docker-entrypoint-secrets.sh"]
