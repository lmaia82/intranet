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
| Publicações (ex-Artigos) | ✅ Pronta (apresentação + links para o Mineralis e o Master) |
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
  Agenda, Repositório) — ex.: um grupo só lê Informativos, outro pode
  publicá-los. Usuário sem grupo não acessa nada (exceto
  administradores, que ignoram a checagem). Cada usuário tem um único
  grupo (`users.group_id`), ajustável individualmente ou em lote (CSV
  e-mail+grupo). Cadastro de usuários também ganhou lote (CSV com
  nome/e-mail/senha/setor/grupo/admin). A migração cria um grupo
  padrão "Colaboradores" com acesso total e migra todos os usuários
  existentes para ele, então nada quebra após o `migrate` — a
  restrição por grupo é opt-in via a tela de Admin > Grupos. As
  permissões internas `artigos.ver`/`artigos.criar` ficaram órfãs (não
  gateiam mais nada) depois da tela virar um link estático — o rótulo
  exibido em Admin > Grupos já foi atualizado para "Publicações", mas a
  chave interna continua `artigos.*`; podem ser renomeadas/removidas do
  catálogo numa limpeza futura.

- **Publicações (ex-Artigos) → Mineralis / Master**: ✅ Pronta. A tela
  (renomeada de "Artigos" para "Publicações" no menu) deixou de ter
  CRUD/busca/cadastro em lote e virou uma página de apresentação com
  dois links: Mineralis (`https://mineralis.cetem.gov.br/buscar`),
  repositório institucional do CETEM, e Master (`https://master.cetem.gov.br/`),
  biblioteca digital com a produção técnico-científica publicada por
  editores externos ao Centro. O model/tabela `Artigo` e os PDFs já
  enviados foram mantidos no banco (não usados na UI), só por precaução
  — podem ser removidos numa limpeza futura se não fizerem mais falta.

- **Cotas de armazenamento por setor**: ✅ Pronta. Implementado direto
  no Laravel (não no MinIO — MinIO aplica quota por bucket, e todos os
  setores compartilham um único bucket organizado por pasta/setor a
  nível de aplicação; usar quota nativa exigiria um bucket por setor,
  complexidade desproporcional ao ganho). Setores ganharam uma coluna
  `quota_bytes` (editável em MB via Admin > Setores, em branco = sem
  limite). O uso é calculado somando `arquivos.tamanho` por setor
  (já rastreado no banco, sem consultar o MinIO). Upload é bloqueado no
  `RepositorioController` quando ultrapassaria a cota do setor
  selecionado. Nova tela **Admin > Armazenamento** mostra uso/cota por
  setor com barra de progresso (vermelho ≥90%, laranja ≥70%).
  O setor passou a ser **obrigatório** ao criar pasta/enviar arquivo no
  Repositório (antes existia a opção "Geral, sem setor", que ficava sem
  cota e sem limite) — todo item pertence a um setor real, para que o
  consumo sempre entre na conta de alguma cota. Nos formulários, o
  setor do usuário logado já vem pré-selecionado (com fallback para o
  setor gravado no registro, em edições de itens antigos que ainda não
  tinham setor). A pasta "Meus Arquivos", criada automaticamente na
  primeira visita, também é vinculada ao setor do usuário logado.

- **Visibilidade pública/restrita no Repositório**: ✅ Pronta. O campo
  "Restrito ao setor" (`is_private`), que já existia na tela mas não
  tinha efeito nenhum, agora é aplicado de fato: pasta/arquivo
  **desmarcado** é público (visível a qualquer usuário autenticado,
  independente do setor); **marcado**, só é visível para usuários do
  mesmo setor selecionado (administradores sempre veem tudo). A
  checagem (`Pasta::visivelPara()` / `Arquivo::visivelPara()`) é
  aplicada na listagem (`RepositorioController::index`, que filtra
  subpastas/arquivos e bloqueia com 403 o acesso direto a uma pasta
  restrita) e no download (`RepositorioController::download`).

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
