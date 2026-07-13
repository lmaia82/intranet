# Planejamento — Modernização da Intranet

Reescrita completa da intranet (framework PHP próprio "Diamond", legado em
`intranet-legacy-slim/` e `lib/`) usando Laravel. O legado fica só como
referência de regras de negócio e schema de dados — não é executado.

Stack: Laravel + Blade/Livewire, rodando via Docker Compose na VM Ubuntu
(`docker-compose.yml` na raiz). Autenticação local (Breeze) por enquanto;
integração com AD/LDAP fica para o final, depois de todas as telas prontas.

## Status das telas

| Tela | Status |
| --- | --- |
| Ramais / Telefones | ✅ Pronta (CRUD completo) |
| Informativos / Mural de Avisos | ✅ Pronta (CRUD + upload de imagem) |
| Agenda / Eventos | ✅ Pronta (CRUD completo) |
| Repositório de Arquivos | ✅ Pronta (pastas, upload/download, MinIO) |
| Artigos | ✅ Pronta (CRUD + busca + cadastro em lote) |
| Painel de Administração | ✅ Pronta (setores, usuários, estatísticas) |
| Autenticação via AD/LDAP | ⬜ Pendente (substituirá o Breeze) |

- **Identidade visual CETEM**: ✅ Aplicada (logo oficial, cores institucionais azul #0052CC/laranja #F4A000, tipografia Calibri/Arial). Falta ainda ajustar telas de login/registro (Breeze padrão).

- **Notificação por e-mail de Informativos**: ✅ Pronta. Ao publicar um
  Informativo, o usuário pode marcar a opção "Enviar notificação por
  e-mail ao publicar" (não é automático) para disparar um e-mail (template
  com identidade visual CETEM) para todos os usuários ou, se um setor for
  selecionado, apenas para os usuários daquele setor (campo `sector_id` em
  `users`, ainda provisório até a integração com AD/LDAP). Cada envio é
  registrado na tabela `informativo_envios` (e-mail + data/hora), exibido
  na tela do Informativo, com botão "Reenviar e-mails" para disparar um
  novo lote a qualquer momento. Testado localmente via Mailpit.

## Pendências técnicas anotadas

- **Migração de dados do legado** (Ramais, Eventos, Informativos): existe
  o dump `intranet-legacy-slim/bck_intranet.sql` com os dados reais de
  produção (tabelas `telefones`, `events`, `article`). Planejar um comando
  Artisan de migração de dados (mapear `categories` → `sectors`, corrigir
  encoding latin1 → UTF-8, filtrar `article` por `id_index = 1` para
  Informativos). **Rodar perto do go-live**, não antes.

- **Integração com OnlyOffice** (Repositório de Arquivos): usar o
  OnlyOffice Document Server (container Docker `onlyoffice/documentserver`)
  para visualizar/editar documentos Office (Word/Excel/PowerPoint) direto
  no navegador. Integração via JSON de configuração assinado com JWT
  (`firebase/php-jwt`) + editor JS embutido na view + endpoint de callback
  no Laravel para salvar o arquivo editado. Não existe um SDK oficial
  Laravel — a integração é direta (controller + view + assinatura JWT),
  seguindo a documentação oficial do OnlyOffice Document Server.
