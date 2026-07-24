# Rima Menu + IA

## Manifesto e Arquitetura Inicial

O **Rima Menu + IA** será um garçom inteligente integrado ao cardápio digital, ao carrinho e aos pedidos do restaurante.

O objetivo não é criar apenas um chatbot, mas um atendente rápido, objetivo e capaz de reconhecer clientes recorrentes, recomendar produtos e aumentar o ticket médio sem tornar a conversa cansativa.

---

## 1. Visão do Produto

O cliente deve conseguir:

- tirar dúvidas sobre o cardápio;
- receber recomendações;
- montar o pedido;
- adicionar produtos ao carrinho;
- finalizar a compra;
- ser reconhecido em atendimentos futuros.

A IA deve agir como um garçom experiente, mas com foco em **praticidade e agilidade**.

> Quanto mais clara estiver a intenção do cliente, menos a IA deve falar.

---

## 2. Regra das 6 Interações

A IA deve concluir o fluxo principal de venda em, no máximo, **6 interações**, sempre que possível.

Fluxo recomendado:

1. Identificar o cliente.
2. Entender o que ele deseja.
3. Apresentar poucas opções.
4. Confirmar produto, tamanho ou quantidade.
5. Fazer uma única oferta complementar.
6. Encaminhar para o carrinho e finalização.

A conversa pode continuar quando houver:

- alteração no pedido;
- dúvida sobre ingredientes;
- alergias ou restrições;
- problema de entrega;
- solicitação adicional do cliente.

Mesmo nesses casos, as respostas devem continuar curtas e objetivas.

---

## 3. Regras de Comunicação

A IA deve:

- responder em até 2 ou 3 frases;
- fazer apenas uma pergunta por mensagem;
- apresentar no máximo 3 opções por vez;
- evitar repetir informações;
- não perguntar novamente dados já conhecidos;
- aceitar pedidos diretos sem criar etapas desnecessárias;
- fazer no máximo uma oferta de upsell ou cross-sell;
- conduzir o cliente rapidamente ao carrinho.

### Exemplo direto

**Cliente:**

> Quero uma pizza grande de calabresa e uma Coca-Cola 2L.

**Resposta esperada:**

> Perfeito! Adicionei a pizza grande de calabresa e a Coca-Cola 2L ao seu pedido. Revise o carrinho para finalizar.

---

## 4. Identificação do Cliente

Antes de responder, a IA deve verificar:

> Este cliente já existe no banco de dados?

### Cliente novo

Coletar apenas os dados essenciais:

- nome;
- telefone ou WhatsApp;
- e-mail opcional.

O cadastro deve ser rápido e não pode impedir o cliente de fazer o pedido.

### Cliente recorrente

A IA deve recuperar o cadastro e cumprimentar o cliente pelo nome.

Exemplo:

> Olá, Amir! Que bom ter você de volta. O que vamos pedir hoje?

A IA não deve solicitar novamente dados já armazenados.

---

## 5. Memória do Cliente

A memória permitirá que a IA personalize o atendimento ao longo do tempo.

Dados principais:

- nome;
- telefone;
- e-mail;
- primeiro pedido;
- último pedido;
- total de pedidos;
- valor total gasto;
- ticket médio;
- produto favorito;
- categoria favorita;
- forma de pagamento preferida;
- preferências;
- observações relevantes;
- autorização para receber promoções.

Exemplos de memória:

- prefere borda recheada;
- sempre pede Coca-Cola;
- não gosta de cebola;
- possui alergia informada;
- prefere pagar por Pix;
- costuma pedir às sextas-feiras.

Informações sensíveis ou relacionadas à saúde só devem ser registradas quando forem necessárias para o atendimento e fornecidas voluntariamente pelo cliente.

---

## 6. Histórico de Pedidos

Todo pedido feito pela IA deve continuar usando as estruturas oficiais do Rima Menu.

A IA não cria pedidos paralelos.

Ela deve utilizar:

- clientes;
- produtos;
- categorias;
- carrinho;
- pedidos;
- itens do pedido;
- formas de pagamento;
- entrega e retirada;
- taxas de entrega;
- status do pedido.

O histórico permitirá:

- repetir um pedido anterior;
- recomendar produtos semelhantes;
- reconhecer preferências;
- calcular ticket médio;
- identificar clientes recorrentes.

---

## 7. Conhecimento do Restaurante

A IA deve consumir informações diretamente do banco de dados.

Ela precisa conhecer:

- nome do restaurante;
- horário de funcionamento;
- categorias;
- produtos;
- descrições;
- preços;
- ingredientes;
- tamanhos;
- adicionais;
- promoções;
- disponibilidade;
- tempo médio de preparo;
- formas de pagamento;
- retirada e entrega;
- área e taxa de entrega.

Nenhum produto ou preço deve ficar fixo no prompt.

---

## 8. Motor de Vendas

A IA deve recomendar sem pressionar o cliente.

### Upsell

Oferecer uma versão de maior valor.

Exemplo:

> Por mais R$ 6, você pode trocar pela pizza grande. Deseja alterar?

### Cross-sell

Oferecer um item complementar.

Exemplo:

> Quer adicionar uma Coca-Cola 2L por R$ 12?

### Regras

- apenas uma oferta complementar por fluxo;
- não insistir após uma recusa;
- não oferecer produtos indisponíveis;
- não inventar promoções;
- não alterar valores;
- não adicionar itens sem confirmação.

---

## 9. Arquitetura Funcional

```text
Cliente
   |
   v
Rima Menu + IA
   |
   +--> Identificação do cliente
   |
   +--> Base de conhecimento
   |
   +--> Memória e histórico
   |
   +--> Motor de recomendação
   |
   +--> Carrinho
   |
   +--> Pedido
   |
   v
Banco de dados do restaurante
```

---

## 10. Módulos

### 10.1 Cérebro

Responsável por:

- personalidade;
- regras de comunicação;
- limite de interações;
- decisões de atendimento;
- segurança;
- tom de voz.

### 10.2 Conhecimento

Responsável por consultar:

- produtos;
- categorias;
- ingredientes;
- horários;
- preços;
- promoções;
- disponibilidade.

### 10.3 Memória

Responsável por:

- identificar clientes;
- recuperar histórico;
- registrar preferências;
- reconhecer recorrência;
- personalizar o atendimento.

### 10.4 Vendas

Responsável por:

- recomendações;
- upsell;
- cross-sell;
- combos;
- aumento de ticket médio.

### 10.5 Pedidos

Responsável por:

- montar carrinho;
- validar itens;
- confirmar quantidades;
- calcular total;
- encaminhar para checkout;
- registrar pedido.

---

## 11. Estrutura Inicial de Banco de Dados

### Tabela `cliente_memorias`

Sugestão inicial:

```text
id
restaurante_id
cliente_id
tipo
descricao
origem
ativo
created_at
updated_at
```

Possíveis valores para `tipo`:

- preferencia;
- restricao;
- pagamento;
- entrega;
- produto_favorito;
- observacao.

### Tabela `conversas_ia`

```text
id
restaurante_id
cliente_id
canal
status
iniciada_em
encerrada_em
created_at
updated_at
```

### Tabela `mensagens_ia`

```text
id
conversa_id
remetente
conteudo
intencao
tokens_entrada
tokens_saida
created_at
updated_at
```

### Tabela `configuracoes_ia`

```text
id
restaurante_id
nome_assistente
tom_de_voz
mensagem_boas_vindas
limite_interacoes
upsell_ativo
memoria_ativa
ativo
created_at
updated_at
```

---

## 12. Segurança e Regras de Negócio

A IA nunca deve:

- inventar produtos;
- inventar preços;
- criar promoções inexistentes;
- confirmar pedido sem validação;
- adicionar item sem consentimento;
- alterar taxa de entrega manualmente;
- expor dados de outros clientes;
- acessar dados de outro restaurante;
- prometer prazo sem informação disponível;
- tratar uma recomendação como garantia.

Todas as operações devem respeitar o isolamento por restaurante.

---

## 13. Roadmap

### Sprint 2.1 — Manifesto e Regras

- definir personalidade;
- definir limite de 6 interações;
- definir mensagens curtas;
- definir regras de venda;
- definir restrições.

### Sprint 2.2 — Base de Conhecimento

- carregar restaurante;
- carregar categorias;
- carregar produtos;
- carregar preços;
- carregar horários;
- carregar disponibilidade.

### Sprint 2.3 — Memória do Cliente

- identificar por telefone;
- cadastrar novo cliente;
- reconhecer cliente recorrente;
- recuperar histórico;
- salvar preferências.

### Sprint 2.4 — Motor de Recomendação

- recomendar produtos;
- sugerir adicionais;
- criar upsell;
- criar cross-sell;
- impedir insistência excessiva.

### Sprint 2.5 — Carrinho e Pedido

- adicionar produto;
- remover produto;
- alterar quantidade;
- calcular total;
- validar entrega;
- encaminhar para checkout;
- registrar pedido.

### Sprint 2.6 — Painel da IA

- ativar ou desativar IA;
- configurar nome;
- configurar mensagem inicial;
- visualizar conversas;
- visualizar métricas;
- configurar comportamento comercial.

---

## 14. Métricas

A evolução do produto deverá acompanhar:

- conversas iniciadas;
- conversas convertidas em pedido;
- taxa de conversão;
- média de interações;
- tempo médio até o pedido;
- valor médio dos pedidos com IA;
- itens adicionais vendidos;
- clientes recorrentes reconhecidos;
- pedidos abandonados;
- custo médio de IA por pedido.

---

## 15. Critérios de Sucesso da Primeira Versão

A primeira versão será considerada funcional quando conseguir:

- reconhecer um cliente existente;
- cadastrar um cliente novo;
- consultar o cardápio real;
- responder perguntas sobre produtos;
- recomendar até 3 opções;
- adicionar itens ao carrinho;
- fazer uma única oferta complementar;
- finalizar o fluxo em até 6 interações;
- registrar o pedido no banco;
- reconhecer o cliente em uma nova conversa.

---

## 16. Princípio Final

Cada atendimento deve deixar a IA um pouco mais inteligente do que no atendimento anterior, sem comprometer a velocidade do pedido.

O Rima Menu + IA deve ser lembrado pelo cliente como:

> Um atendimento rápido, personalizado e fácil de usar.

E pelo restaurante como:

> Um garçom digital que atende 24 horas e ajuda a vender mais.
