# 🏗️ Arquitetura do Rima Food

## Objetivo

O Rima Food foi projetado como uma plataforma modular para restaurantes.

Cada módulo possui uma responsabilidade específica e se comunica através de eventos e serviços, reduzindo acoplamento e facilitando a evolução do sistema.

---

# Fluxo Principal

```text
Cliente
    │
    ▼
🍔 Cardápio Digital
    │
    ▼
🤖 WhatsApp / Rima IA
    │
    ▼
🛒 Carrinho
    │
    ▼
📦 Pedido
    │
    ▼
⚙️ PedidoEventService
    │
    ├── 📊 Dashboard
    ├── 🍳 Central da Cozinha
    ├── 🔔 Notificações
    ├── 📈 Analytics
    ├── 👥 CRM
    └── 📢 Campanhas
```

---

# Estrutura do projeto

```
app/

Controllers/

Models/

Services/

Events/

Jobs/

Notifications/

Policies/
```

---

# Responsabilidade de cada camada

## Controllers

Recebem as requisições HTTP.

Nunca devem conter regras de negócio.

Funções:

* Validar requisições
* Chamar Services
* Retornar Views ou JSON

---

## Models

Representam as entidades do banco.

Responsáveis apenas por:

* Relacionamentos
* Casts
* Scopes
* Accessors

Nunca devem conter regras complexas.

---

## Services

Onde ficam todas as regras de negócio.

Exemplos:

* PedidoAutomaticoService
* PedidoEventService
* WhatsappService
* CardapioService

---

## Events

Responsáveis por disparar ações após acontecimentos importantes.

Exemplo:

Novo pedido criado

↓

PedidoEventService

↓

Atualizar Dashboard

↓

Notificar Cozinha

↓

Enviar WhatsApp

↓

Atualizar CRM

---

## Blade

Responsáveis apenas pela interface.

Nunca consultar banco.

Nunca executar regras de negócio.

---

# Filosofia

Cada módulo deve ser independente.

Exemplo:

🍔 Cardápio Digital

não depende da

🤖 IA

Porém ambos utilizam o mesmo módulo de Pedidos.

---

# Produtos

O projeto é dividido em produtos comercializáveis.

## 🍔 Rima Menu

Cardápio Digital.

---

## 🤖 Rima IA

Atendimento Inteligente.

---

## 🏪 Rima Food

ERP completo.

Todos compartilham a mesma base.

---

# Integrações futuras

* Evolution API
* n8n
* iFood
* Rappi
* Mercado Pago
* APIs públicas
* Webhooks

---

# Objetivo final

Construir uma plataforma modular, escalável e preparada para atender desde pequenos restaurantes até redes com múltiplas unidades.

Cada nova funcionalidade deve fortalecer o ecossistema existente, evitando duplicação de código e mantendo a arquitetura simples e organizada.
