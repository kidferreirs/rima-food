<?php

namespace App\Http\Controllers;

use App\Models\Restaurante;
use App\Services\EvolutionApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class WhatsappConfiguracaoController extends BaseRestaurantController
{
    public function __construct(
        private readonly EvolutionApiService $evolution
    ) {
    }

    public function index(): View
    {
        $restaurante = $this->restaurante();

        $this->garantirPlanoComIa($restaurante);

        if ($restaurante->evolution_instance) {
            $this->sincronizarEstado($restaurante);
        }

        return view(
            'configuracoes.whatsapp.index',
            [
                'restauranteAtual' => $restaurante->fresh(),
                'qrCode' => null,
                'pairingCode' => null,
            ]
        );
    }

    public function conectar(): View|RedirectResponse
    {
        $restaurante = $this->restaurante();

        $this->garantirPlanoComIa($restaurante);

        try {
            $nomeInstancia = $restaurante->evolution_instance
                ?: $this->gerarNomeInstancia($restaurante);

            $respostaCriacao = [];

            if (!$restaurante->evolution_instance) {
                $respostaCriacao = $this->evolution
                    ->criarInstancia($nomeInstancia);

                $restaurante->update([
                    'evolution_instance' => $nomeInstancia,
                    'evolution_status' => 'close',
                    'evolution_last_sync_at' => now(),
                ]);
            }

            $respostaConexao = $this->evolution
                ->conectar($nomeInstancia);

            $qrCode = $this->extrairQrCode(
                $respostaConexao
            ) ?: $this->extrairQrCode(
                $respostaCriacao
            );

            $pairingCode = $this->extrairPairingCode(
                $respostaConexao
            ) ?: $this->extrairPairingCode(
                $respostaCriacao
            );

            $restaurante->update([
                'evolution_status' => 'connecting',
                'evolution_last_sync_at' => now(),
            ]);

            return view(
                'configuracoes.whatsapp.index',
                [
                    'restauranteAtual' => $restaurante->fresh(),
                    'qrCode' => $qrCode,
                    'pairingCode' => $pairingCode,
                ]
            );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route(
                    'restaurante.configuracoes.whatsapp.index',
                    $restaurante->slug
                )
                ->with(
                    'error',
                    $exception->getMessage()
                );
        }
    }

    public function status(): RedirectResponse
    {
        $restaurante = $this->restaurante();

        $this->garantirPlanoComIa($restaurante);

        if (!$restaurante->evolution_instance) {
            return back()->with(
                'error',
                'Este restaurante ainda não possui uma instância do WhatsApp.'
            );
        }

        try {
            $estado = $this->sincronizarEstado(
                $restaurante
            );

            $mensagem = $estado === 'open'
                ? 'WhatsApp conectado com sucesso.'
                : 'Status atualizado: ' . $estado;

            return back()->with(
                'success',
                $mensagem
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                $exception->getMessage()
            );
        }
    }

    public function desconectar(): RedirectResponse
    {
        $restaurante = $this->restaurante();

        $this->garantirPlanoComIa($restaurante);

        if (!$restaurante->evolution_instance) {
            return back()->with(
                'error',
                'Nenhuma instância foi encontrada.'
            );
        }

        try {
            $this->evolution->desconectar(
                $restaurante->evolution_instance
            );

            $restaurante->update([
                'evolution_status' => 'close',
                'evolution_phone' => null,
                'evolution_last_sync_at' => now(),
            ]);

            return back()->with(
                'success',
                'WhatsApp desconectado.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                $exception->getMessage()
            );
        }
    }

    public function excluir(): RedirectResponse
    {
        $restaurante = $this->restaurante();

        $this->garantirPlanoComIa($restaurante);

        if (!$restaurante->evolution_instance) {
            return back()->with(
                'error',
                'Nenhuma instância foi encontrada.'
            );
        }

        try {
            $this->evolution->excluir(
                $restaurante->evolution_instance
            );

            $restaurante->update([
                'evolution_instance' => null,
                'evolution_status' => null,
                'evolution_phone' => null,
                'evolution_connected_at' => null,
                'evolution_last_sync_at' => now(),
            ]);

            return back()->with(
                'success',
                'Instância do WhatsApp excluída.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                $exception->getMessage()
            );
        }
    }

    private function sincronizarEstado(
        Restaurante $restaurante
    ): string {
        $resposta = $this->evolution
            ->consultarEstado(
                $restaurante->evolution_instance
            );

        $estado = data_get(
            $resposta,
            'instance.state',
            'close'
        );

        $dadosInstancia = [];

        try {
            $dadosInstancia = $this->evolution
                ->buscarInstancia(
                    $restaurante->evolution_instance
                );
        } catch (Throwable $exception) {
            report($exception);
        }

        $telefone = $this->extrairTelefone(
            $dadosInstancia
        );

        $dadosAtualizacao = [
            'evolution_status' => $estado,
            'evolution_last_sync_at' => now(),
        ];

        if ($telefone) {
            $dadosAtualizacao['evolution_phone'] =
                $telefone;
        }

        if ($estado === 'open') {
            $dadosAtualizacao[
                'evolution_connected_at'
            ] = $restaurante
                ->evolution_connected_at
                ?: now();
        }

        $restaurante->update(
            $dadosAtualizacao
        );

        return $estado;
    }

    private function garantirPlanoComIa(
        Restaurante $restaurante
    ): void {
        abort_unless(
            $restaurante->temIA(),
            403,
            'A conexão com inteligência artificial está disponível apenas nos planos Rima Menu + IA e Rima Food.'
        );
    }

    private function gerarNomeInstancia(
        Restaurante $restaurante
    ): string {
        $prefixo = config(
            'services.evolution.instance_prefix',
            'rima_rest_'
        );

        return $prefixo . $restaurante->id;
    }

    private function extrairQrCode(
        array $dados
    ): ?string {
        $qrCode =
            data_get($dados, 'base64')
            ?? data_get($dados, 'qrcode.base64')
            ?? data_get($dados, 'qr.base64')
            ?? data_get($dados, 'instance.qrcode.base64');

        if (!is_string($qrCode) || $qrCode === '') {
            return null;
        }

        if (str_starts_with(
            $qrCode,
            'data:image'
        )) {
            return $qrCode;
        }

        return 'data:image/png;base64,' . $qrCode;
    }

    private function extrairPairingCode(
        array $dados
    ): ?string {
        $codigo =
            data_get($dados, 'pairingCode')
            ?? data_get($dados, 'qrcode.pairingCode')
            ?? data_get($dados, 'code');

        return is_string($codigo) && $codigo !== ''
            ? $codigo
            : null;
    }

    private function extrairTelefone(
        array $dados
    ): ?string {
        $instancia = $dados[0]
            ?? data_get($dados, 'instance')
            ?? $dados;

        $identificador =
            data_get($instancia, 'ownerJid')
            ?? data_get($instancia, 'number')
            ?? data_get($instancia, 'instance.ownerJid')
            ?? data_get($instancia, 'instance.number');

        if (!is_string($identificador)) {
            return null;
        }

        $telefone = preg_replace(
            '/\D/',
            '',
            explode('@', $identificador)[0]
        );

        return $telefone !== ''
            ? $telefone
            : null;
    }
}