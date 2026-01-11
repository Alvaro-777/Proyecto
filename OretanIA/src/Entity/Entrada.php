<?php

namespace App\Entity;

enum Entrada: string
{
    case Texto = 'texto';
    case Documento = 'documento';
    case Ambas = 'ambas';
}