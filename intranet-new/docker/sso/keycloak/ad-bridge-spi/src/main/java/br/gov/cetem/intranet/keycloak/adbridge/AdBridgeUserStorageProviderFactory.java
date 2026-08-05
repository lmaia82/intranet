package br.gov.cetem.intranet.keycloak.adbridge;

import org.keycloak.component.ComponentModel;
import org.keycloak.models.KeycloakSession;
import org.keycloak.provider.ProviderConfigProperty;
import org.keycloak.provider.ProviderConfigurationBuilder;
import org.keycloak.storage.UserStorageProviderFactory;

import java.util.List;

/**
 * Registra o provider "ad-bridge" na tela Realm &gt; User Federation do
 * Keycloak. Sem LDAP, sem conta de serviço — os três campos de config abaixo
 * são só o endereço e o segredo da ponte HTTP para o intranet-new.
 */
public class AdBridgeUserStorageProviderFactory implements UserStorageProviderFactory<AdBridgeUserStorageProvider> {

    public static final String PROVIDER_ID = "ad-bridge";

    public static final String CONFIG_BRIDGE_URL = "bridgeUrl";
    public static final String CONFIG_BRIDGE_SECRET = "bridgeSecret";
    public static final String CONFIG_TIMEOUT_MS = "timeoutMs";

    @Override
    public String getId() {
        return PROVIDER_ID;
    }

    @Override
    public String getHelpText() {
        return "Autentica delegando a validação de senha para o endpoint interno "
                + "/internal/ad-auth do intranet-new (bind direto no AD feito pelo "
                + "próprio Laravel, sem conta de serviço configurada no Keycloak).";
    }

    @Override
    public AdBridgeUserStorageProvider create(KeycloakSession session, ComponentModel model) {
        return new AdBridgeUserStorageProvider(session, model);
    }

    @Override
    public List<ProviderConfigProperty> getConfigProperties() {
        return ProviderConfigurationBuilder.create()
                .property()
                    .name(CONFIG_BRIDGE_URL)
                    .label("URL da ponte AD")
                    .helpText("Endpoint interno do container \"app\" (rede intranet_default), "
                            + "ex: http://app/internal/ad-auth — só a rede interna do Keycloak "
                            + "deve alcançar esse endereço.")
                    .type(ProviderConfigProperty.STRING_TYPE)
                    .defaultValue("http://app/internal/ad-auth")
                    .add()
                .property()
                    .name(CONFIG_BRIDGE_SECRET)
                    .label("Segredo compartilhado")
                    .helpText("Mesmo valor de SSO_BRIDGE_SECRET no .env do intranet-new.")
                    .type(ProviderConfigProperty.PASSWORD)
                    .secret(true)
                    .add()
                .property()
                    .name(CONFIG_TIMEOUT_MS)
                    .label("Timeout (ms)")
                    .type(ProviderConfigProperty.STRING_TYPE)
                    .defaultValue("5000")
                    .add()
                .build();
    }
}
