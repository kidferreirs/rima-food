# 🍔 Rima Food — Roadmap Oficial

O projeto é construído sobre uma única base SaaS, com recursos liberados conforme o plano contratado.

## Planos

### 🟢 Rima Menu

Cardápio digital completo, pedidos e experiência premium.

### 🔵 Rima Menu + IA

Tudo do Rima Menu, com Garçom Inteligente, recomendações e automações com IA.

### 🟣 Rima Food

Solução completa para operação do restaurante, incluindo gestão, CRM, financeiro, estoque, atendimento e IA.

---

# ✅ Sprint 1 — Base do Sistema

- [x] Laravel
- [x] MySQL
- [x] Docker
- [x] Autenticação
- [x] Login e cadastro
- [x] Dashboard
- [x] Sidebar
- [x] Estrutura multi-tenant
- [x] Vinculação dos dados ao usuário autenticado
- [x] Proteções contra acesso entre restaurantes

---

# ✅ Sprint 2 — Restaurante

- [x] Cadastro de restaurante
- [x] Edição de restaurante
- [x] CEP automático com ViaCEP
- [x] Endereço completo
- [x] Telefone e e-mail
- [x] Documento
- [x] Instagram e site
- [x] Slug público
- [x] Status ativo/inativo
- [x] Formas de atendimento
  - [x] Delivery
  - [x] Retirada
  - [x] Consumo no local
- [x] Quantidade de mesas
- [x] Horário de abertura
- [x] Horário de fechamento
- [x] Formato de horário em 24 horas
- [x] Upload de logo
- [x] Upload de banner
- [x] Google Maps
- [x] Nota e quantidade de avaliações do Google preparadas
- [x] Separação por planos
  - [x] MENU
  - [x] MENU_IA
  - [x] FOOD

---

# ✅ Sprint 3 — Cardápio e Produtos

- [x] Categorias
- [x] Produtos
- [x] Imagem do produto
- [x] Descrição
- [x] Preço
- [x] Status ativo/inativo
- [x] Edição de produto
- [x] Exclusão lógica pelo status
- [x] Organização dos produtos por restaurante
- [x] Campos do Motor de Conhecimento
  - [x] Palavras-chave
  - [x] Sinônimos
  - [x] Ingredientes
  - [x] Restrições
  - [x] Tags inteligentes
- [x] Seleção visual de tags
  - [x] Destaque
  - [x] Mais vendido
  - [x] Recomendado
  - [x] Promoção
  - [x] Novidade
  - [x] Premium
  - [x] Artesanal
  - [x] Picante
  - [x] Vegano
  - [x] Fitness

---

# ✅ Sprint 4 — Pedidos

- [x] Criar pedido
- [x] Itens do pedido
- [x] Quantidade
- [x] Preço unitário
- [x] Observações
- [x] Total do pedido
- [x] Cliente
- [x] Forma de pagamento
- [x] Tipo de entrega
- [x] Taxa de entrega
- [x] Endereço de entrega
- [x] Token do pedido
- [x] Numeração independente por restaurante
- [x] Status do pedido
  - [x] Novo
  - [x] Preparando
  - [x] Pronto
  - [x] Finalizado
- [x] Histórico de horários por status
- [x] Últimos pedidos no dashboard
- [x] Impressão de pedidos finalizados
- [x] Checkout público

---

# ✅ Sprint 5 — QR Code e Compartilhamento

- [x] Gerar QR Code automático do cardápio
- [x] Baixar QR Code em PNG
- [x] Copiar link do cardápio
- [x] Compartilhar pelo WhatsApp
- [x] Compartilhamento nativo com `navigator.share`
- [x] Página de divulgação do cardápio
- [x] Pré-visualização do menu
- [x] Link público por slug
- [x] Estatísticas básicas do cardápio
- [x] Interface premium para divulgação

---

# ✅ Sprint 6 — Importação de Cardápio

- [x] Importar CSV
- [x] Importar Excel
- [x] Pré-visualizar produtos antes da importação
- [x] Criar categorias automaticamente
- [x] Criar produtos automaticamente
- [x] Atualizar produtos existentes
- [x] Identificar novas categorias
- [x] Identificar produtos novos
- [x] Identificar produtos atualizados
- [x] Corrigir caracteres especiais e codificação UTF-8
- [x] Disponibilizar modelo CSV para download
- [x] Preparar arquitetura para futura importação por PDF
- [x] Preparar arquitetura para futura importação por foto com IA

---

# ✅ Sprint 7 — Experiência Premium e Inteligente

## 7.1 — UX do Cardápio

- [x] Status `Aberto até...`
- [x] Status `Abre às...`
- [x] Suporte a horários após meia-noite
- [x] Google Maps clicável
- [x] WhatsApp com mensagem pronta
- [x] Compartilhamento nativo
- [x] Upload de banner
- [x] Upload de logo
- [x] Logo em tamanho adequado
- [x] Banner padrão por segmento preparado
- [x] Nota do Google exibida no menu
- [x] Número de avaliações exibido
- [x] Layout responsivo
- [x] Interface mobile premium
- [x] Categorias em menu lateral
- [x] Bebidas ordenadas no final do cardápio
- [x] Mais vendidos posicionados no final
- [x] Toast de feedback
- [x] Microanimações

## 7.2 — Busca Inteligente

- [x] Busca por nome
- [x] Busca por descrição
- [x] Busca por categoria
- [x] Busca por palavras-chave
- [x] Busca por sinônimos
- [x] Busca por ingredientes
- [x] Busca por tags
- [x] Normalização de acentos
- [x] Interpretação de intenções simples
- [x] Ocultar categorias sem resultados
- [x] Ocultar vitrines durante a pesquisa
- [x] Mensagem contextual
  - [x] `Encontrei X produtos para você`

## 7.3 — Vitrines Inteligentes

- [x] Destaques
- [x] Mais vendidos com dados reais
- [x] Produtos recomendados preparados
- [x] Favoritos salvos no navegador
- [x] Contador de favoritos
- [x] Animação ao favoritar
- [x] Toast ao adicionar ou remover favorito
- [x] Evitar repetição entre Destaques e Mais vendidos

## 7.4 — Arquitetura Frontend

- [x] Separação inicial dos módulos JavaScript
- [x] `garcom.js`
- [x] `favoritos.js`
- [x] `categorias.js`
- [x] Integração dos módulos no `app.js`

---

# 🚧 Sprint 8 — Garçom Inteligente com IA

## 8.1 — Conversa no Cardápio

- [ ] Criar interface conversacional
- [ ] Enviar perguntas ao backend
- [ ] Exibir respostas do Garçom Inteligente
- [ ] Manter histórico curto da conversa
- [ ] Trabalhar apenas com os dados do restaurante
- [ ] Evitar respostas inventadas
- [ ] Responder perguntas sobre produtos
- [ ] Responder perguntas sobre ingredientes
- [ ] Responder perguntas sobre restrições
- [ ] Responder perguntas sobre preços
- [ ] Responder perguntas sobre categorias

## 8.2 — Recomendações

- [ ] Recomendar produtos conforme intenção
- [ ] Recomendar mais vendidos
- [ ] Recomendar produtos pelas tags
- [ ] Recomendar acompanhamentos
- [ ] Recomendar bebidas
- [ ] Recomendar sobremesas
- [ ] Fazer upsell
- [ ] Sugerir alternativas

## 8.3 — Pedido Conversacional

- [ ] Entender produtos mencionados
- [ ] Entender quantidades
- [ ] Entender observações
- [ ] Entender remoção de ingredientes
- [ ] Entender adicionais
- [ ] Adicionar itens ao carrinho pela conversa
- [ ] Confirmar itens antes de finalizar
- [ ] Montar pedido completo pela conversa

## 8.4 — Segurança e Planos

- [ ] Liberar IA apenas para `MENU_IA` e `FOOD`
- [ ] Limitar quantidade de mensagens por plano
- [ ] Registrar consumo de IA
- [ ] Criar fallback quando a IA estiver indisponível
- [ ] Implementar logs de erros
- [ ] Proteger contra abuso
- [ ] Preparar troca de provedor de IA

---

# 📋 Sprint 9 — Onboarding Inteligente e Landing Page

## 9.1 — Landing Page Comercial

- [ ] Landing page oficial do Rima Menu
- [ ] Apresentação dos três planos
- [ ] Comparativo de recursos
- [ ] Prova social
- [ ] Perguntas frequentes
- [ ] CTA para teste grátis
- [ ] Formulário de 15 dias grátis

## 9.2 — Cadastro Inteligente

- [ ] Escolher o ramo do negócio
- [ ] Pizzaria
- [ ] Hamburgueria
- [ ] Restaurante
- [ ] Lanchonete
- [ ] Doceria
- [ ] Cafeteria
- [ ] Sushi
- [ ] Açaí
- [ ] Sorveteria
- [ ] Marmitaria
- [ ] Padaria
- [ ] Bar
- [ ] Outros

## 9.3 — Personalização Automática

- [ ] Aplicar banner conforme o ramo selecionado
- [ ] Usar os banners já criados
- [ ] Aplicar banner apenas quando o usuário não enviar um próprio
- [ ] Permitir troca posterior no dashboard
- [ ] Manter logo em branco quando não houver upload
- [ ] Preview do cardápio durante o cadastro
- [ ] Criar categorias sugeridas conforme o ramo
- [ ] Produtos de exemplo opcionais
- [ ] Cardápio inicial visualmente pronto

## 9.4 — Teste Grátis

- [ ] Ativar teste de 15 dias
- [ ] Registrar data de início
- [ ] Registrar data de encerramento
- [ ] Exibir dias restantes
- [ ] Avisar antes do vencimento
- [ ] Bloquear recursos conforme o plano
- [ ] Preparar upgrade

---

# 📋 Sprint 10 — Assinaturas e Pagamentos

- [ ] Tabela de assinaturas
- [ ] Status da assinatura
- [ ] Período de teste
- [ ] Vencimento
- [ ] Upgrade de plano
- [ ] Downgrade de plano
- [ ] Cancelamento
- [ ] Integração com Mercado Pago
- [ ] Integração futura com Stripe
- [ ] Webhooks de pagamento
- [ ] Controle de inadimplência
- [ ] Histórico de cobranças
- [ ] Emissão de recibos

---

# 📋 Sprint 11 — WhatsApp Inteligente

- [ ] Evolution API
- [ ] Conexão por QR Code
- [ ] Webhook de mensagens
- [ ] Identificar restaurante pela instância
- [ ] Consultar cardápio
- [ ] Responder perguntas
- [ ] Criar pedido
- [ ] Enviar resumo do pedido
- [ ] Confirmar pedido
- [ ] Transferir para atendimento humano
- [ ] Pausar e retomar IA
- [ ] Histórico de mensagens
- [ ] Templates
- [ ] Mensagens automáticas

---

# 📋 Sprint 12 — CRM e Marketing

- [ ] Cadastro automático de clientes
- [ ] Histórico de pedidos
- [ ] Último pedido
- [ ] Total gasto
- [ ] Ticket médio
- [ ] Clientes recorrentes
- [ ] Clientes inativos
- [ ] Segmentação
- [ ] Campanhas
- [ ] Cupons
- [ ] Programa de fidelidade
- [ ] Aniversariantes
- [ ] Recuperação de clientes
- [ ] Automação de campanhas
- [ ] IA para criar mensagens

---

# 📋 Sprint 13 — Operação Rima Food

- [ ] Caixa
- [ ] Abertura de caixa
- [ ] Fechamento de caixa
- [ ] Sangria
- [ ] Suprimento
- [ ] Comandas
- [ ] Mesas
- [ ] Impressão de cozinha
- [ ] Tela de produção
- [ ] Controle de entregas
- [ ] Motoboys
- [ ] Estoque
- [ ] Insumos
- [ ] Ficha técnica
- [ ] Baixa automática de estoque
- [ ] Fornecedores
- [ ] Custos
- [ ] Margem de lucro
- [ ] Financeiro
- [ ] Contas a pagar
- [ ] Contas a receber
- [ ] Fluxo de caixa

---

# 📋 Sprint 14 — Relatórios e Analytics

- [ ] Relatório de vendas
- [ ] Relatório por produto
- [ ] Relatório por categoria
- [ ] Relatório por forma de pagamento
- [ ] Relatório por canal
- [ ] Horários com mais pedidos
- [ ] Produtos mais vendidos
- [ ] Produtos menos vendidos
- [ ] Ticket médio
- [ ] Clientes recorrentes
- [ ] Conversão do Garçom Inteligente
- [ ] Receita gerada por recomendações
- [ ] Exportação de relatórios
- [ ] Dashboard executivo

---

# 📋 Sprint 15 — Produção e Escala

- [ ] Ambiente de produção
- [ ] Domínio oficial
- [ ] SSL
- [ ] Banco de produção
- [ ] Storage em nuvem
- [ ] Otimização automática de imagens
- [ ] Conversão para WebP
- [ ] Recorte de imagens
- [ ] Cache
- [ ] Filas
- [ ] Jobs
- [ ] Monitoramento
- [ ] Logs
- [ ] Backups
- [ ] Política de privacidade
- [ ] Termos de uso
- [ ] LGPD
- [ ] Testes automatizados
- [ ] CI/CD

---

# 🎯 Meta Comercial

## Validação

- [ ] Publicar Landing Page
- [ ] Disponibilizar teste grátis de 15 dias
- [ ] Apresentar demonstração comercial
- [ ] Fechar 3 restaurantes pagantes

## Tração Inicial

- [ ] Chegar a 10 restaurantes ativos
- [ ] Validar preços dos planos
- [ ] Validar custo da IA
- [ ] Obter depoimentos
- [ ] Criar estudos de caso

## Recorrência

- [ ] Chegar a 25 restaurantes ativos
- [ ] Criar receita recorrente previsível
- [ ] Reduzir cancelamentos
- [ ] Automatizar onboarding
- [ ] Automatizar cobrança
- [ ] Preparar expansão regional