# Arquitetura SaaS - Rima Food

## Regra principal

O **Rima Food é a plataforma principal**.

Dentro dela, o cliente pode contratar módulos:

* Rima Menu
* Rima Menu + IA
* Rima Food Completo

Nada é reinstalado.
Nada é separado.
Tudo permanece dentro da mesma conta, do mesmo sistema e da mesma plataforma.

---

## Teste grátis

Todo módulo pode iniciar com **15 dias de teste grátis**.

Exemplo:

Cliente começa com:

Rima Menu

Depois pode evoluir para:

Rima Menu + IA

Depois pode evoluir para:

Rima Food Completo

Sem perder dados.

---

## Isolamento de dados

Cada estabelecimento possui seus próprios dados.

Um estabelecimento nunca pode visualizar dados de outro.

Isso inclui:

* Produtos
* Categorias
* Clientes
* Pedidos
* Mesas
* Relatórios
* Cardápio
* Conversas
* Campanhas
* Configurações

---

## Estrutura

Usuário
↓
Conta
↓
Estabelecimentos
↓
Módulos ativos
↓
Dados isolados por estabelecimento

---

## Exemplo

Usuário: Amir

Estabelecimentos:

* Hamburgueria do Amir

  * Plano: Rima Food Completo
  * Dados próprios

* Doces da Ana

  * Plano: Rima Menu
  * Dados próprios

* Pizzaria do Amir

  * Plano: Rima Menu + IA
  * Dados próprios

Nenhum dado se mistura.

---

## Regra de segurança

Antes de qualquer funcionalidade nova, verificar:

1. Qual é o estabelecimento atual?
2. O usuário tem acesso a este estabelecimento?
3. Os dados consultados pertencem a este estabelecimento?
4. O módulo contratado permite acessar essa funcionalidade?

Se alguma resposta falhar, bloquear acesso.

---

## Regra comercial

O cliente pode começar pequeno e crescer dentro da plataforma.

Rima Menu
↓
Rima Menu + IA
↓
Rima Food Completo

A evolução libera novas funcionalidades, mas preserva todos os dados já cadastrados.

---

## Filosofia

Segurança primeiro.

Isolamento sempre.

Uma plataforma.

Vários módulos.

Vários estabelecimentos.

Dados nunca se misturam.
