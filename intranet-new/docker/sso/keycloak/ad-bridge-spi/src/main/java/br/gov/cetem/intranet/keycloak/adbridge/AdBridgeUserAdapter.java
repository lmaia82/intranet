package br.gov.cetem.intranet.keycloak.adbridge;

import jakarta.json.JsonObject;
import org.keycloak.component.ComponentModel;
import org.keycloak.models.KeycloakSession;
import org.keycloak.models.RealmModel;
import org.keycloak.storage.adapter.AbstractUserAdapterFederatedStorage;

/**
 * Representa, dentro do Keycloak, um usuário do AD. Não guarda senha nem
 * confirma existência antecipadamente — não há busca privilegiada no
 * diretório (não há conta de serviço). O e-mail é o username, igual ao
 * login atual da intranet. Os demais atributos só ficam disponíveis depois
 * de um login bem-sucedido, quando {@link #applyBridgeAttributes} é chamado
 * com a resposta do endpoint /internal/ad-auth.
 */
public class AdBridgeUserAdapter extends AbstractUserAdapterFederatedStorage {

    private final String username;

    public AdBridgeUserAdapter(KeycloakSession session, RealmModel realm, ComponentModel model, String username) {
        super(session, realm, model);
        this.username = username;
        setSingleAttribute("email", username);
    }

    @Override
    public String getUsername() {
        return username;
    }

    @Override
    public void setUsername(String username) {
        // Username = e-mail do AD; não é editável a partir do Keycloak.
    }

    void applyBridgeAttributes(JsonObject bridgeResult) {
        if (bridgeResult.containsKey("name") && !bridgeResult.isNull("name")) {
            String name = bridgeResult.getString("name");
            setSingleAttribute("name", name);

            String[] parts = name.split(" ", 2);
            setFirstName(parts[0]);
            setLastName(parts.length > 1 ? parts[1] : "");
        }

        if (bridgeResult.containsKey("email") && !bridgeResult.isNull("email")) {
            setEmail(bridgeResult.getString("email"));
            setEmailVerified(true);
        }

        if (bridgeResult.containsKey("is_admin") && bridgeResult.getBoolean("is_admin", false)) {
            setSingleAttribute("is_admin", "true");
        }
    }
}
