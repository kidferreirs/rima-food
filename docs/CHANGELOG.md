# Changelog — Rima Menu / Rima Food

## v0.5.0-mvp — MVP funcional do Rima Menu

### Base SaaS
- Criada arquitetura multiempresa por restaurante.
- Implementado carregamento por slug em `/r/{slug}`.
- Criado isolamento de dados por restaurante.
- Ajustado dashboard por restaurante ativo.
- Corrigidas rotas internas para uso com contexto do restaurante.

### Cadastro e Onboarding
- Criada tela de cadastro inicial do restaurante.
- Adicionados planos Starter, Pro e Business.
- Implementado cadastro de usuário, empresa e restaurante.
- Adicionadas mensagens de validação em português.
- Removido campo desnecessário “Nome da empresa”.
- Melhorada experiência visual do formulário.

### Dashboard
- Criado dashboard operacional.
- Adicionados indicadores de pedidos, clientes, produtos e pendentes.
- Criado financeiro do dia por forma de pagamento.
- Adicionados últimos pedidos no dashboard.
- Implementado modal de pedido.
- Implementado status pelo dashboard.
- Corrigido retorno após salvar status.
- Adicionado auto refresh.
- Adicionado alerta sonoro para novos pedidos.

### Categorias
- Criado cadastro de categorias.
- Criada listagem de categorias por restaurante.
- Implementada edição de categorias.
- Implementado ativar/inativar categorias.
- Categorias exibidas no menu público.

### Produtos
- Criado cadastro de produtos.
- Implementada edição de produtos.
- Implementado upload de imagem real do produto.
- Produtos vinculados às categorias.
- Implementado ativar/inativar produtos.
- Produtos exibidos no menu público.

### Menu Público
- Criado cardápio público em `/menu/{slug}`.
- Adicionado banner do restaurante.
- Adicionada logo do restaurante.
- Criado layout mobile first.
- Produtos agrupados por categoria.
- Adicionado botão WhatsApp.
- Adicionado botão Compartilhar.
- Implementada busca de produtos.
- Implementada navegação por categorias com scroll.
- Implementado carrinho por restaurante usando `localStorage`.

### Carrinho
- Adicionar produto ao carrinho.
- Atualizar total em tempo real.
- Separar carrinho por restaurante.
- Ver pedido.
- Aumentar quantidade.
- Diminuir quantidade.
- Remover item.
- Limpar carrinho.
- Continuar comprando.

### Checkout
- Criada tela de checkout.
- Adicionado banner e logo no checkout.
- Adicionado resumo do pedido.
- Adicionado nome do cliente.
- Adicionado telefone/WhatsApp.
- Adicionado tipo de entrega:
  - Retirada no balcão
  - Consumo no local
  - Delivery
- Adicionada forma de pagamento:
  - Pix
  - Dinheiro
  - Cartão de Crédito
  - Cartão de Débito
- Adicionado campo de observações.
- Corrigido envio do carrinho para o backend.

### Pedidos
- Criado pedido a partir do menu público.
- Criado cliente automaticamente pelo telefone.
- Criados itens do pedido.
- Calculado total do pedido.
- Adicionado status inicial `novo`.
- Adicionada origem `menu`.
- Adicionado token público do pedido.
- Corrigido salvamento da forma de pagamento.
- Implementada tela de sucesso após pedido.
- Adicionado token na Central de Pedidos.
- Adicionado token na visualização do pedido.

### Tela de Sucesso
- Criada tela premium de pedido recebido.
- Adicionado banner.
- Adicionada logo.
- Exibido nome do restaurante.
- Exibido token do pedido.
- Exibido status recebido.
- Exibido tipo de entrega.
- Exibida forma de pagamento.
- Exibido tempo estimado.
- Exibido total.
- Adicionado botão voltar ao cardápio.
- Botão “Acompanhar Pedido” preparado para próxima sprint.

### Central de Pedidos
- Criada Central de Pedidos.
- Listagem de pedidos por restaurante.
- Exibição de itens.
- Exibição de total.
- Exibição de origem.
- Exibição de token.
- Busca de pedidos.
- Alteração de status.
- Ações de visualizar, imprimir, editar e cancelar.
- Status com badges visuais.
- Corrigidas rotas para contexto `/r/{slug}`.

### Cozinha
- Criada Central da Cozinha.
- Separação por status:
  - Novo
  - Preparando
  - Pronto
- Botões operacionais:
  - Começar
  - Marcar pronto
  - Finalizar
- Integração com status dos pedidos.
- Atualização automática por polling.
- Pedido sai da cozinha ao finalizar.

### Relatórios
- Criada tela de relatórios.
- Filtro por período.
- Atalhos:
  - Hoje
  - Ontem
  - Esta semana
  - Este mês
- Faturamento do dia.
- Faturamento semanal.
- Faturamento mensal.
- Faturamento por período.
- Ticket médio.
- Produto mais vendido.
- Clientes cadastrados.
- Pedidos finalizados.
- Pedidos cancelados.
- Pagamentos separados por:
  - Dinheiro
  - Crédito
  - Débito
  - Pix
  - Total cartões
- Exibição da forma de pagamento mais utilizada.
- Exportação CSV.

### Segurança e Organização
- Ajustado isolamento multi-tenant.
- Corrigidas rotas antigas que causavam problemas.
- Padronizado uso de rotas com `/r/{slug}`.
- Corrigido uso de `restauranteAtual`.
- Adicionada proteção para pedidos de outros restaurantes.
- Token público separado do ID interno.

### UX e Polimentos
- Melhorado layout do checkout.
- Melhorado layout da tela de sucesso.
- Melhorado layout da visualização do pedido.
- Melhorados badges de token e origem.
- Melhorada exibição de pagamento.
- Ajustado relatório financeiro.
- Ajustados textos e botões principais.
- Aplicado padrão visual do Rima Menu.

---

### Sprint 5 — QR Code e Compartilhamento
- Gerar QR Code automático do cardápio.
- Baixar QR Code em PNG.
- Copiar link do cardápio.
- Compartilhar no WhatsApp.
- Criar página de divulgação do menu.


## Próximas Sprints

### Sprint 6 — Importação de Cardápio
- Importar por Excel/CSV.
- Pré-visualizar produtos.
- Criar categorias automaticamente.
- Criar produtos automaticamente.
- Preparar futura importação por PDF/foto com IA.

### Sprint 7 — Rima Menu com IA
- Busca inteligente.
- Sugestão de produtos.
- Sugestão de combos.
- Atendimento guiado no menu.
- Upsell com IA.

### Sprint 8 — Mobile First
- Dashboard mobile.
- Central de pedidos mobile.
- Cozinha mobile.
- Sidebar responsiva.
- Melhor navegação para pequenos negócios.

### Sprint 9 — Landing Page Comercial
- Landing page do Rima Menu.
- Planos e benefícios.
- Demonstração.
- Captação de leads.
- Botão WhatsApp.
- Preparação para vendas.