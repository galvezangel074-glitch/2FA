<?php
// Criterio: interfaz para generar el hash y validarlo (Programación Orientada a Objetos)
interface IGeneradorHash
{
    public function generar(string $clave): string;

    public function validar(string $clave, string $hash): bool;

    public function necesitaRehash(string $hash): bool;
}
