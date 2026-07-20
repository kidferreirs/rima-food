<?php

namespace App\Services\Delivery;

class Address
{
    public function __construct(
        public readonly string $logradouro,
        public readonly string $numero,
        public readonly ?string $complemento,
        public readonly string $bairro,
        public readonly string $cidade,
        public readonly string $estado,
        public readonly ?string $cep = null,
    ) {
    }

    public function textoCompleto(): string
    {
        $partes = [
            trim($this->logradouro . ', ' . $this->numero),
            $this->bairro,
            $this->cidade,
            $this->estado,
            $this->cep,
            'Brasil',
        ];

        return implode(', ', array_filter(
            $partes,
            fn ($parte) => filled($parte)
        ));
    }

    public function textoParaPedido(): string
    {
        $endereco = trim($this->logradouro . ', ' . $this->numero);

        if (filled($this->complemento)) {
            $endereco .= ' - ' . trim($this->complemento);
        }

        return implode(', ', array_filter([
            $endereco,
            $this->bairro,
            $this->cidade . '/' . $this->estado,
            $this->cep,
        ]));
    }
}