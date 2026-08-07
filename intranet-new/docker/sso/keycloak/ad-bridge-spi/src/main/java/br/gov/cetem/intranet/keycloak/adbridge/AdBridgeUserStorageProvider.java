package br.gov.cetem.intranet.keycloak.adbridge;

import jakarta.json.Json;
import jakarta.json.JsonObject;
import jakarta.json.JsonReader;
import org.jboss.logging.Logger;
import org.keycloak.component.ComponentModel;
import org.keycloak.credential.CredentialInput;
import org.keycloak.credential.CredentialInputValidator;
import org.keycloak.models.KeycloakSession;
import org.keycloak.models.RealmModel;
import org.keycloak.models.UserModel;
import org.keycloak.models.cache.CachedUserModel;
import org.keycloak.models.credential.PasswordCredentialModel;
import org.keycloak.storage.StorageId;
import org.keycloak.storage.UserStorageProvider;
import org.keycloak.storage.user.UserLookupProvider;

import java.io.IOException;
import java.io.StringReader;
import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.time.Duration;

/**
 * Provider "ad-bridge": nunca faz LDAP bind diretamente. Toda validação de
 * senha é delegada, via HTTP interno, ao endpoint /internal/ad-auth do
 * intranet-new — que reusa App\Services\ActiveDirectoryAuthenticator (bind
 * direto com a credencial do próprio usuário, sem conta de serviço).
 *
 * Isso mantém uma única fonte de verdade para a autenticação no AD (o
 * código PHP já existente e testado), em vez de duplicar a integração LDAP
 * aqui em Java.
 */
public class AdBridgeUserStorageProvider implements
        UserStorageProvider,
        UserLookupProvider,
        CredentialInputValidator {

    private static final Logger log = Logger.getLogger(AdBridgeUserStorageProvider.class);

    private final KeycloakSession session;
    private final ComponentModel model;
    private final HttpClient httpClient;

    public AdBridgeUserStorageProvider(KeycloakSession session, ComponentModel model) {
        this.session = session;
        this.model = model;
        this.httpClient = HttpClient.newBuilder()
                .connectTimeout(Duration.ofMillis(timeoutMs()))
                .build();
    }

    private long timeoutMs() {
        String value = model.getConfig().getFirst(AdBridgeUserStorageProviderFactory.CONFIG_TIMEOUT_MS);
        try {
            return value != null ? Long.parseLong(value) : 5000L;
        } catch (NumberFormatException e) {
            return 5000L;
        }
    }

    // --- UserLookupProvider --------------------------------------------------
    //
    // Sem conta de serviço não há busca privilegiada no AD — devolvemos
    // sempre um usuário "virtual" com o e-mail informado. A existência real
    // só é confirmada em isValid(), no momento do login, batendo na ponte.

    @Override
    public UserModel getUserByUsername(RealmModel realm, String username) {
        return new AdBridgeUserAdapter(session, realm, model, username);
    }

    @Override
    public UserModel getUserById(RealmModel realm, String id) {
        return getUserByUsername(realm, StorageId.externalId(id));
    }

    @Override
    public UserModel getUserByEmail(RealmModel realm, String email) {
        return getUserByUsername(realm, email);
    }

    // --- CredentialInputValidator ---------------------------------------------

    @Override
    public boolean supportsCredentialType(String credentialType) {
        return PasswordCredentialModel.TYPE.equals(credentialType);
    }

    @Override
    public boolean isConfiguredFor(RealmModel realm, UserModel user, String credentialType) {
        return supportsCredentialType(credentialType);
    }

    @Override
    public boolean isValid(RealmModel realm, UserModel user, CredentialInput input) {
        if (!supportsCredentialType(input.getType())) {
            return false;
        }

        String password = input.getChallengeResponse();
        if (password == null || password.isEmpty()) {
            return false;
        }

        log.infof("ad-bridge isValid() para username=%s, classe recebida=%s", user.getUsername(), user.getClass().getName());

        try {
            JsonObject result = callBridge(user.getUsername(), password);

            if (!result.getBoolean("valid", false)) {
                return false;
            }

            // O Keycloak pode ter envolvido nosso UserModel numa camada de
            // cache (CachedUserModel) antes de chamar isValid() — nesse
            // caso "user instanceof AdBridgeUserAdapter" falha e os
            // atributos (nome, e-mail) nunca seriam aplicados de verdade.
            // getDelegateForUpdate() devolve o objeto real por trás do
            // cache, já invalidando o cache dele.
            UserModel alvo = user;
            if (user instanceof CachedUserModel cached) {
                alvo = cached.getDelegateForUpdate();
                log.infof("Desembrulhado de CachedUserModel — delegate classe=%s", alvo.getClass().getName());
            }

            if (alvo instanceof AdBridgeUserAdapter adapter) {
                adapter.applyBridgeAttributes(result);
                log.infof("Atributos do bridge aplicados. email agora: %s", adapter.getEmail());
            } else {
                log.warnf("UserModel não é AdBridgeUserAdapter (nem delegate) — classe real: %s. Atributos do bridge NÃO foram aplicados.", alvo.getClass().getName());
            }

            return true;
        } catch (IOException | InterruptedException e) {
            if (e instanceof InterruptedException) {
                Thread.currentThread().interrupt();
            }
            log.error("Falha ao chamar a ponte AD do intranet-new", e);
            return false;
        }
    }

    private JsonObject callBridge(String email, String password) throws IOException, InterruptedException {
        String url = model.getConfig().getFirst(AdBridgeUserStorageProviderFactory.CONFIG_BRIDGE_URL);
        String secret = model.getConfig().getFirst(AdBridgeUserStorageProviderFactory.CONFIG_BRIDGE_SECRET);

        String body = Json.createObjectBuilder()
                .add("email", email)
                .add("password", password)
                .build()
                .toString();

        HttpRequest request = HttpRequest.newBuilder()
                .uri(URI.create(url))
                .timeout(Duration.ofMillis(timeoutMs()))
                .header("Content-Type", "application/json")
                .header("Accept", "application/json")
                .header("Authorization", "Bearer " + secret)
                .POST(HttpRequest.BodyPublishers.ofString(body))
                .build();

        HttpResponse<String> response = httpClient.send(request, HttpResponse.BodyHandlers.ofString());

        if (response.statusCode() != 200) {
            log.warnf("Ponte AD retornou HTTP %d", response.statusCode());
            return Json.createObjectBuilder().add("valid", false).build();
        }

        try (JsonReader reader = Json.createReader(new StringReader(response.body()))) {
            return reader.readObject();
        }
    }

    @Override
    public void close() {
        // sem recursos para liberar
    }
}
