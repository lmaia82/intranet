#!/bin/bash
set -e
cd ~/projetos/intranet
git am --abort 2>/dev/null || true
git rm -rf lib/ 2>&1 | tail -3
git rm -f "intranet-legacy-slim/actions/ajaxFiles.php"
git rm -f "intranet-legacy-slim/actions/createFolder.php"
git rm -f "intranet-legacy-slim/actions/deleteArticle.php"
git rm -f "intranet-legacy-slim/actions/deleteEvent.php"
git rm -f "intranet-legacy-slim/actions/deleteFile.php"
git rm -f "intranet-legacy-slim/actions/deleteFolder.php"
git rm -f "intranet-legacy-slim/actions/deleteRepositorio.php"
git rm -f "intranet-legacy-slim/actions/deleteTelefone.php"
git rm -f "intranet-legacy-slim/actions/doDownload.php"
git rm -f "intranet-legacy-slim/actions/doLogin.php"
git rm -f "intranet-legacy-slim/actions/doLogoff.php"
git rm -f "intranet-legacy-slim/actions/doUpload.php"
git rm -f "intranet-legacy-slim/actions/doUploadImage.php"
git rm -f "intranet-legacy-slim/actions/getInfoFiles.php"
git rm -f "intranet-legacy-slim/actions/getRamal.php"
git rm -f "intranet-legacy-slim/actions/iframe_list_images.php"
git rm -f "intranet-legacy-slim/actions/iframe_treeview.php"
git rm -f "intranet-legacy-slim/actions/loadAgenda.php"
git rm -f "intranet-legacy-slim/actions/registerArticle.php"
git rm -f "intranet-legacy-slim/actions/registerEvent.php"
git rm -f "intranet-legacy-slim/actions/registerTelefone.php"
git rm -f "intranet-legacy-slim/actions/updateArticle.php"
git rm -f "intranet-legacy-slim/actions/updateEvent.php"
git rm -f "intranet-legacy-slim/actions/updateFile.php"
git rm -f "intranet-legacy-slim/actions/updateFolder.php"
git rm -f "intranet-legacy-slim/actions/updateTelefone.php"
git rm -f "intranet-legacy-slim/actions/writeText.php"
git rm -f "intranet-legacy-slim/application/default.php"
git rm -f "intranet-legacy-slim/application/logoff/default.php"
git rm -f "intranet-legacy-slim/application/logoff/information.xml"
git rm -f "intranet-legacy-slim/application/sca/default.php"
git rm -f "intranet-legacy-slim/application/sca/information.xml"
git rm -f "intranet-legacy-slim/application/srh/default.php"
git rm -f "intranet-legacy-slim/application/srh/information.xml"
git rm -f "intranet-legacy-slim/components/CheckPermissionAdm.php"
git rm -f "intranet-legacy-slim/components/Header_HTML.php"
git rm -f "intranet-legacy-slim/components/Header_login.php"
git rm -f "intranet-legacy-slim/components/Header_teste.php"
git rm -f "intranet-legacy-slim/components/SideBar.php"
git rm -f "intranet-legacy-slim/components/footer.php"
git rm -f "intranet-legacy-slim/components/header_page.php"
git rm -f "intranet-legacy-slim/components/navBar.php"
git rm -f "intranet-legacy-slim/components/pagination_categories.php"
git rm -f "intranet-legacy-slim/components/side_bar_tool.php"
git rm -f "intranet-legacy-slim/forcastOverHttps.php"
git rm -f "intranet-legacy-slim/forec.html"
git rm -f "intranet-legacy-slim/includes/master.php"
git rm -f "intranet-legacy-slim/index.php"
git rm -f "intranet-legacy-slim/modules/administrador/default.php"
git rm -f "intranet-legacy-slim/modules/agenda/default.php"
git rm -f "intranet-legacy-slim/modules/apps/default.php"
git rm -f "intranet-legacy-slim/modules/article/default.php"
git rm -f "intranet-legacy-slim/modules/article/parameters.xml"
git rm -f "intranet-legacy-slim/modules/autenticar/default.php"
git rm -f "intranet-legacy-slim/modules/error/default.php"
git rm -f "intranet-legacy-slim/modules/error/parameters.xml"
git rm -f "intranet-legacy-slim/modules/eventos/default.php"
git rm -f "intranet-legacy-slim/modules/eventos/view/create.php"
git rm -f "intranet-legacy-slim/modules/eventos/view/list.php"
git rm -f "intranet-legacy-slim/modules/eventos/view/update.php"
git rm -f "intranet-legacy-slim/modules/informativos/default.php"
git rm -f "intranet-legacy-slim/modules/informativos/default_old.php"
git rm -f "intranet-legacy-slim/modules/informativos/parameters.xml"
git rm -f "intranet-legacy-slim/modules/informativos/view/categorie.php"
git rm -f "intranet-legacy-slim/modules/informativos/view/load.php"
git rm -f "intranet-legacy-slim/modules/informativos_administrador/default.php"
git rm -f "intranet-legacy-slim/modules/informativos_administrador/view/create.php"
git rm -f "intranet-legacy-slim/modules/informativos_administrador/view/list.php"
git rm -f "intranet-legacy-slim/modules/informativos_administrador/view/update.php"
git rm -f "intranet-legacy-slim/modules/main/default.php"
git rm -f "intranet-legacy-slim/modules/minha_area/default.php"
git rm -f "intranet-legacy-slim/modules/repositorio/default.php"
git rm -f "intranet-legacy-slim/modules/repositorio_administrador/default.php"
git rm -f "intranet-legacy-slim/modules/repositorio_administrador/view/create_folder.php"
git rm -f "intranet-legacy-slim/modules/repositorio_administrador/view/list.php"
git rm -f "intranet-legacy-slim/modules/repositorio_administrador/view/update_file.php"
git rm -f "intranet-legacy-slim/modules/repositorio_administrador/view/update_folder.php"
git rm -f "intranet-legacy-slim/modules/repositorio_administrador/view/upload.php"
git rm -f "intranet-legacy-slim/modules/telefones/default.php"
git rm -f "intranet-legacy-slim/modules/telefones/default_old.php"
git rm -f "intranet-legacy-slim/modules/telefones_administrador/default.php"
git rm -f "intranet-legacy-slim/modules/telefones_administrador/view/create.php"
git rm -f "intranet-legacy-slim/modules/telefones_administrador/view/list.php"
git rm -f "intranet-legacy-slim/modules/telefones_administrador/view/update.php"
git rm -f "intranet-legacy-slim/template/article/content.php"
git rm -f "intranet-legacy-slim/template/article/footer.php"
git rm -f "intranet-legacy-slim/template/article/header.php"
echo "--- restante em intranet-legacy-slim ---"
ls intranet-legacy-slim/
cat > PLANEJAMENTO.md <<'PLANEJAMENTO_EOF'
# Planejamento — Modernização da Intranet

Reescrita completa da intranet (framework PHP próprio "Diamond") usando
Laravel. O código legado (`intranet-legacy-slim/` e `lib/`) serviu só como
referência de regras de negócio — nunca foi executado — e já foi removido do
repositório depois de todas as regras terem sido portadas. Fica apenas
`intranet-legacy-slim/bck_intranet.sql`, o dump com os dados reais de
produção, guardado até a migração de dados (ver Pendências abaixo).

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
  na tela do Informativo. O botão "Reenviar e-mails" leva a uma tela onde
  a lista de destinatários vem pré-carregada pelo setor do informativo (ou
  todos os usuários), mas fica num campo de texto totalmente editável —
  dá pra adicionar e-mails avulsos (mesmo de quem não é usuário cadastrado)
  ou remover linhas antes de confirmar o reenvio. Testado localmente via
  Mailpit.

- **Eventos gravados**: ✅ Pronta. Nova seção na tela de Agenda/Eventos,
  abaixo dos eventos anteriores, listando gravações (data + título com
  link direto para o vídeo no YouTube, aberto em nova aba). CRUD próprio
  (`evento_gravados`) e cadastro em lote via CSV (mesmo padrão do
  cadastro em lote de Artigos).

- **Cadastro em lote de Ramais**: ✅ Pronta. Tela de Ramais/Telefones
  ganhou opção "Cadastro em lote" (mesmo padrão de Artigos/Eventos
  gravados): CSV com nome, telefone, setor (deve bater com um setor já
  cadastrado), e-mail e cargo.

- **Grupos de permissão**: ✅ Pronta. "Tela Inicial" renomeada (era
  "Dashboard"). Admin > Grupos permite criar grupos com permissões
  flexíveis de "Ver" e "Criar/editar" por tela (Ramais, Informativos,
  Agenda, Artigos, Repositório) — ex.: um grupo só lê Informativos,
  outro pode publicá-los. Usuário sem grupo não acessa nada (exceto
  administradores, que ignoram a checagem). Cada usuário tem um único
  grupo (`users.group_id`), ajustável individualmente ou em lote (CSV
  e-mail+grupo). Cadastro de usuários também ganhou lote (CSV com
  nome/e-mail/senha/setor/grupo/admin). A migração cria um grupo
  padrão "Colaboradores" com acesso total e migra todos os usuários
  existentes para ele, então nada quebra após o `migrate` — a
  restrição por grupo é opt-in via a tela de Admin > Grupos.

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
PLANEJAMENTO_EOF
git add PLANEJAMENTO.md
git add lib/ intranet-legacy-slim/ 2>/dev/null || true
git status --short | head -20
git commit -m "Remove código PHP legado do repositório

As regras de negócio do framework \"Diamond\" (intranet-legacy-slim/ e
lib/) já foram totalmente portadas para o Laravel — o código legado só
servia como referência e nunca foi executado neste repositório.
Remove tudo exceto intranet-legacy-slim/bck_intranet.sql, que continua
guardado até a migração de dados de produção (Ramais, Eventos,
Informativos) planejada para perto do go-live.

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_013hcEEXZu91mYj9ytM7sc2R"
git log --oneline -3
