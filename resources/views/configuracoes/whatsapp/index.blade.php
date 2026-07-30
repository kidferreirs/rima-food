<x-rimafood.layout>

    <div class="p-8 max-w-5xl">

        <div class="mb-8">
            <h1 class="text-4xl font-bold">
                💬 Conectar WhatsApp
            </h1>

            <p class="text-gray-500 mt-2">
                Conecte o WhatsApp da empresa para que a Rima possa atender os clientes.
            </p>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">
                {{ session('error') }}
            </div>
        @endif

        @php
            $status = $restauranteAtual->evolution_status;
            $conectado = $status === 'open';
        @endphp

        <div class="rounded-xl bg-white shadow p-6 mb-8">

            <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">

                <div>
                    <h2 class="text-2xl font-bold">
                        {{ $restauranteAtual->nome }}
                    </h2>

                    <div class="mt-3">

                        @if($conectado)
                            <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-700">
                                🟢 WhatsApp conectado
                            </span>
                        @elseif($status === 'connecting')
                            <span class="inline-flex items-center rounded-full bg-yellow-100 px-3 py-1 text-sm font-semibold text-yellow-700">
                                🟡 Aguardando conexão
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-700">
                                ⚪ WhatsApp desconectado
                            </span>
                        @endif

                    </div>
                </div>

                <div class="text-sm text-gray-600">

                    @if($restauranteAtual->evolution_instance)
                        <p>
                            <strong>Instância:</strong>
                            {{ $restauranteAtual->evolution_instance }}
                        </p>
                    @endif

                    @if($restauranteAtual->evolution_phone)
                        <p class="mt-1">
                            <strong>Número:</strong>
                            +{{ $restauranteAtual->evolution_phone }}
                        </p>
                    @endif

                    @if($restauranteAtual->evolution_last_sync_at)
                        <p class="mt-1">
                            <strong>Última atualização:</strong>
                            {{ $restauranteAtual->evolution_last_sync_at->format('d/m/Y H:i') }}
                        </p>
                    @endif

                </div>

            </div>

        </div>

        @if($qrCode)

            <div class="rounded-xl bg-white shadow p-8 text-center mb-8">

                <h2 class="text-2xl font-bold">
                    Escaneie o QR Code
                </h2>

                <p class="mt-2 text-gray-500">
                    No celular, abra o WhatsApp e acesse
                    <strong>Aparelhos conectados</strong>.
                </p>

                <div class="flex justify-center mt-6">
                    <img
                        src="{{ $qrCode }}"
                        alt="QR Code do WhatsApp"
                        class="w-72 h-72 border rounded-xl p-3"
                    >
                </div>

                <p class="mt-5 text-sm text-gray-500">
                    Depois de escanear, clique em “Verificar conexão”.
                </p>

                @if($pairingCode)
                    <div class="mt-5">
                        <p class="text-gray-500">
                            Código alternativo:
                        </p>

                        <div class="mt-2 text-3xl font-bold tracking-widest">
                            {{ $pairingCode }}
                        </div>
                    </div>
                @endif

            </div>

        @endif

        <div class="rounded-xl bg-white shadow p-6">

            <h2 class="text-xl font-bold mb-5">
                Ações
            </h2>

            <div class="flex flex-wrap gap-3">

                @if(!$conectado)

                    <form
                        action="{{ route(
                            'restaurante.configuracoes.whatsapp.conectar',
                            $restauranteAtual->slug
                        ) }}"
                        method="POST"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="rounded-lg bg-green-600 px-5 py-3 font-semibold text-white hover:bg-green-700"
                        >
                            {{ $restauranteAtual->evolution_instance
                                ? 'Gerar novo QR Code'
                                : 'Conectar WhatsApp'
                            }}
                        </button>
                    </form>

                @endif

                @if($restauranteAtual->evolution_instance)

                    <form
                        action="{{ route(
                            'restaurante.configuracoes.whatsapp.status',
                            $restauranteAtual->slug
                        ) }}"
                        method="POST"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-700"
                        >
                            Verificar conexão
                        </button>
                    </form>

                @endif

                @if($conectado)

                    <form
                        action="{{ route(
                            'restaurante.configuracoes.whatsapp.desconectar',
                            $restauranteAtual->slug
                        ) }}"
                        method="POST"
                        onsubmit="return confirm('Deseja desconectar este WhatsApp?')"
                    >
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="rounded-lg bg-yellow-500 px-5 py-3 font-semibold text-white hover:bg-yellow-600"
                        >
                            Desconectar
                        </button>
                    </form>

                @endif

                @if($restauranteAtual->evolution_instance)

                    <form
                        action="{{ route(
                            'restaurante.configuracoes.whatsapp.excluir',
                            $restauranteAtual->slug
                        ) }}"
                        method="POST"
                        onsubmit="return confirm('Excluir completamente esta instância? Será necessário conectar novamente.')"
                    >
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="rounded-lg bg-red-600 px-5 py-3 font-semibold text-white hover:bg-red-700"
                        >
                            Excluir instância
                        </button>
                    </form>

                @endif

            </div>

        </div>

    </div>

</x-rimafood.layout>