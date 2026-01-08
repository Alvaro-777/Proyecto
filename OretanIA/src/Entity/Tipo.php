<?php

namespace App\Entity;

enum Tipo: string
{
    case TextoAudio = 'texto_audio';
    case Predictiva = 'predictiva';
    case Chatbot = 'chatbot';
}