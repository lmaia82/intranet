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
