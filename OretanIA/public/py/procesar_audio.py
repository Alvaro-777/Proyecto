#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sys
import os
import time
from gtts import gTTS
from PyPDF2 import PdfReader
from docx import Document

def extraer_texto_de_archivo(ruta_archivo):
    """
    Extrae texto de un archivo .txt, .pdf o .docx.
    Devuelve el texto como string, o None si hay error.
    """
    extension = os.path.splitext(ruta_archivo)[1].lower()
    texto = ""

    try:
        if extension == ".txt":
            with open(ruta_archivo, 'r', encoding='utf-8', errors='replace') as f:
                texto = f.read()
        elif extension == ".pdf":
            lector = PdfReader(ruta_archivo)
            for pagina in lector.pages:
                extracted = pagina.extract_text()
                if extracted:
                    texto += extracted
        elif extension == ".docx":
            doc = Document(ruta_archivo)
            for parrafo in doc.paragraphs:
                texto += parrafo.text + "\n"
        else:
            print(f"Error: Extensión no soportada: {extension}", file=sys.stderr)
            return None
    except Exception as e:
        print(f"Error al leer el archivo {ruta_archivo}: {e}", file=sys.stderr)
        return None

    return texto

# --- PROGRAMA PRINCIPAL ---
if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Uso: python procesar_audio.py <ruta_archivo_o_texto> <directorio_salida_audios>", file=sys.stderr)
        print("Ejemplo:")
        print("  python procesar_audio.py \"Hola mundo\" \"C:/proyecto/audios\"")
        print("  python procesar_audio.py \"C:/proyecto/uploads/archivo.pdf\" \"C:/proyecto/audios\"")
        sys.exit(1)

    input_data = sys.argv[1]
    audios_dir = sys.argv[2]  # ← ¡Ahora sí lo usamos!

    # Determinar si input_data es un archivo existente o texto plano
    if os.path.isfile(input_data):
        # Es una ruta válida a un archivo
        texto_final = extraer_texto_de_archivo(input_data)
        if texto_final is None:
            sys.exit(1)
    else:
        # Asumimos que es texto directo (p. ej. "Hola mundo")
        texto_final = input_data

    if not texto_final or not texto_final.strip():
        print("Error: El texto a procesar está vacío.", file=sys.stderr)
        sys.exit(1)

    # Asegurar que la carpeta de salida exista
    os.makedirs(audios_dir, exist_ok=True)

    # Generar nombre único
    timestamp = int(time.time())
    nombre_archivo_audio = f"audio_{timestamp}.mp3"
    ruta_audio = os.path.join(audios_dir, nombre_archivo_audio)

    try:
        tts = gTTS(text=texto_final, lang='es', tld='es')  # Acento neutro
        tts.save(ruta_audio)

        if os.path.exists(ruta_audio):
            # ✅ Devolver RUTA RELATIVA desde la raíz del proyecto
            # Ej: si audios_dir = "C:/proyecto/audios", queremos devolver "audios/audio_123.mp3"
            # Extraemos el nombre de la carpeta (último segmento)
            nombre_carpeta = os.path.basename(os.path.normpath(audios_dir))
            ruta_relativa = os.path.join(nombre_carpeta, nombre_archivo_audio)
            # Normalizamos barras para que sean /
            ruta_relativa = ruta_relativa.replace("\\", "/")
            print(ruta_relativa)  # ← ¡SOLO ESTO! Nada más.
        else:
            print(f"ERROR: El archivo no se creó en {ruta_audio}", file=sys.stderr)
            sys.exit(1)

    except Exception as e:
        print(f"Error al generar el audio con gTTS: {e}", file=sys.stderr)
        sys.exit(1)
