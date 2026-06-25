<div class="w-64 min-h-screen bg-gray-900 text-white fixed">

    <div class="p-6 border-b border-gray-700">
        <h1 class="text-2xl font-bold">🍔 Rima Food</h1>
        <p class="text-sm text-gray-400">by Rimatech</p>
    </div>

    <nav class="p-4 space-y-2">

        <a href="/dashboard" class="block p-3 rounded hover:bg-gray-800">
            🏠 Dashboard
        </a>

        <a href="{{ route('whatsapp.conversas.index') }}" class="block p-3 rounded hover:bg-gray-800">
            📱 WhatsApp
        </a>

        <a href="{{ route('cozinha.index') }}" class="block p-3 rounded hover:bg-gray-800">
            🍳 Cozinha
        </a>

        <a href="/pedidos" class="block p-3 rounded hover:bg-gray-800">
            🛒 Pedidos
        </a>

        <a href="/categorias" class="block p-3 rounded hover:bg-gray-800">
            📂 Categorias
        </a>

        <a href="/produtos" class="block p-3 rounded hover:bg-gray-800">
            🍔 Produtos
        </a>

        <a href="{{ route('configuracoes.entregas.index') }}" class="block p-3 rounded hover:bg-gray-800">
            🚚 Entregas
        </a>

        
        <a href="/restaurantes" class="block p-3 rounded hover:bg-gray-800">
            🏪 Restaurante
        </a>
    
        <a href="/clientes" class="block p-3 rounded hover:bg-gray-800">
            👥 Clientes
        </a>

        <a href="/relatorios" class="block p-3 rounded hover:bg-gray-800">
            📊 Relatórios
        </a>

        <a href="#" class="block p-3 rounded hover:bg-gray-800">
            📢 Campanhas
        </a>

    </nav>
</div>