<?php

namespace App\Http\Controllers;

use App\Models\Restaurante;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Services\Delivery\GoogleMapsService;
use App\Services\Delivery\Address;

class RestauranteController extends Controller
{
    public function __construct(
        private GoogleMapsService $googleMapsService
    ) {
    }

    public function index()
    {
        $restaurantes = Restaurante::where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('restaurantes.index', compact('restaurantes'));
    }

    public function create()
    {
        return view('restaurantes.create');
    }

    public function store(Request $request)
    {
        $dados = $this->validarDados($request);

        $dados['user_id'] = auth()->id();
        $dados['slug'] = $this->gerarSlugUnico($dados['nome']);

        $restaurante = Restaurante::create($dados);
        $this->atualizarCoordenadas($restaurante);

        $restaurante->clientes()->create([
            'nome' => 'Balcão',
            'telefone' => '0000000000',
            'ativo' => true,
        ]);

        return redirect()
            ->route('restaurantes.index')
            ->with('success', 'Restaurante cadastrado com sucesso!');
    }

    public function edit(Restaurante $restaurante)
    {
        $this->autorizarRestaurante($restaurante);

        $linkMenu = route('menu.show', $restaurante->slug);

        return view('restaurantes.edit', compact('restaurante', 'linkMenu'));
    }

    public function update(Request $request, Restaurante $restaurante)
    {
        $this->autorizarRestaurante($restaurante);

        $dados = $this->validarDados($request);

        if ($request->hasFile('logo') && $restaurante->logo) {
            Storage::disk('public')->delete($restaurante->logo);
        }

        if ($request->hasFile('banner') && $restaurante->banner) {
            Storage::disk('public')->delete($restaurante->banner);
        }

        $enderecoAntigo = implode('|', [
            $restaurante->cep,
            $restaurante->endereco,
            $restaurante->numero,
            $restaurante->bairro,
            $restaurante->cidade,
            $restaurante->estado,
        ]);

        $restaurante->update($dados);

        $enderecoNovo = implode('|', [
            $restaurante->cep,
            $restaurante->endereco,
            $restaurante->numero,
            $restaurante->bairro,
            $restaurante->cidade,
            $restaurante->estado,
        ]);

        if ($enderecoAntigo !== $enderecoNovo) {
            $this->atualizarCoordenadas($restaurante);
        }

        return redirect()
            ->route('restaurantes.index')
            ->with('success', 'Restaurante atualizado com sucesso!');
    }

    public function alterarStatus(Restaurante $restaurante)
    {
        $this->autorizarRestaurante($restaurante);

        $restaurante->update([
            'ativo' => !$restaurante->ativo,
        ]);

        return redirect()
            ->route('restaurantes.index')
            ->with('success', 'Status do restaurante atualizado!');
    }

    private function validarDados(Request $request): array
    {
        $dados = $request->validate([
            'nome' => 'required|string|max:255',
            'telefone' => 'nullable|string|max:50',
            'documento' => 'nullable|string|max:18',
            'email' => 'nullable|email|max:255',
            'cep' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:50',
            'delivery' => 'nullable|boolean',
            'retirada' => 'nullable|boolean',
            'consumo_local' => 'nullable|boolean',
            'quantidade_mesas' => 'nullable|integer|min:0|max:500',
            'logo' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:4096',
            'abre_as' => 'nullable|date_format:H:i',
            'fecha_as' => 'nullable|date_format:H:i',
            'google_rating' => 'nullable|numeric|min:0|max:5',
            'google_reviews_total' => 'nullable|integer|min:0',
            'google_maps_url' => 'nullable|string|max:500',
        ]);

        $dados['delivery'] = $request->boolean('delivery');
        $dados['retirada'] = $request->boolean('retirada');
        $dados['consumo_local'] = $request->boolean('consumo_local');

        if (!$dados['consumo_local']) {
            $dados['quantidade_mesas'] = 0;
        }

        if ($request->hasFile('logo')) {
            $dados['logo'] = $request->file('logo')->store('restaurantes/logos', 'public');
        }

        if ($request->hasFile('banner')) {
            $dados['banner'] = $request->file('banner')->store('restaurantes/banners', 'public');
        }

        return $dados;
    }

    private function autorizarRestaurante(Restaurante $restaurante): void
    {
        if ($restaurante->user_id !== auth()->id()) {
            abort(403);
        }
    }

    private function gerarSlugUnico(string $nome): string
    {
        $baseSlug = Str::slug($nome);
        $slug = $baseSlug;
        $contador = 1;

        while (Restaurante::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $contador;
            $contador++;
        }

        return $slug;
    }

    public function cardapioDigital(string $slug)
    {
        $restaurante = Restaurante::where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $linkMenu = route('menu.show', $restaurante->slug);

        return view(
            'restaurantes.cardapio-digital',
            compact(
                'restaurante',
                'linkMenu'
            )
        );
    }

    private function atualizarCoordenadas(Restaurante $restaurante): void
    {
        try {
            $address = new Address(
                logradouro: $restaurante->endereco,
                numero: $restaurante->numero,
                complemento: $restaurante->complemento,
                bairro: $restaurante->bairro,
                cidade: $restaurante->cidade,
                estado: $restaurante->estado,
                cep: $restaurante->cep,
            );

            $coordenadas = $this->googleMapsService->geocodificar($address);

            $restaurante->forceFill([
                'latitude' => $coordenadas['latitude'],
                'longitude' => $coordenadas['longitude'],
                'google_place_id' => $coordenadas['place_id'],
            ])->save();

        } catch (\Throwable $e) {
            logger()->error('Erro ao geocodificar restaurante', [
                'restaurante_id' => $restaurante->id,
                'erro' => $e->getMessage(),
            ]);
        }
    }
}