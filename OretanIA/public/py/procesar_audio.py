import sys
import os
import time
from gtts import gTTS
from PyPDF2 import PdfReader
from docx import Document

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
PROJECT_ROOT = os.path.dirname(SCRIPT_DIR)  # Carpeta padre del script

DOCUMENTS_DIR = os.path.join(PROJECT_ROOT, "documentos")
AUDIOS_DIR = os.path.join(PROJECT_ROOT, "audios")

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
    if len(sys.argv) != 2:
        print("Uso: python procesar_audio.py <nombre_archivo_o_texto>", file=sys.stderr)
        print("  - Si es un archivo: debe estar en la carpeta '../documentos/'")
        print("  - Si es texto directo: se procesará tal cual.")
        sys.exit(1)

    input_data = sys.argv[1]

    # Determinar si input_data es un archivo o texto directo
    ruta_posible = os.path.join(DOCUMENTS_DIR, input_data)
        if os.path.isfile(ruta_posible):
            # Es un archivo dentro de ../documentos/
            texto_final = extraer_texto_de_archivo(ruta_posible)
            if texto_final is None:
                sys.exit(1)
        else:
            # Asumimos que es texto directo (p. ej. "Hola mundo")
            texto_final = input_data

        if not texto_final or not texto_final.strip():
            print("Error: El texto a procesar está vacío.", file=sys.stderr)
            sys.exit(1)
        # Asegurar que la carpeta ../audios/ exista
        os.makedirs(AUDIOS_DIR, exist_ok=True)


    # Generar nombre único
    timestamp = int(time.time())
    nombre_archivo_audio = f"audio_{timestamp}.mp3"
    ruta_audio = os.path.join(output_dir, nombre_archivo_audio)
    ruta_audio = os.path.normpath(ruta_audio)  # Normaliza la ruta (evita ./ o ../)

    try:
        tts = gTTS(text=texto_final, lang='es', tld='es')  # Acento neutro/esp: 'es', 'com.mx', etc.
        tts.save(ruta_audio)

        if os.path.exists(ruta_audio):
            # ¡IMPORTANTE! Devolver RUTA RELATIVA desde la raíz del proyecto
            # Por ejemplo: audios/audio_123456.mp3
            # Suponemos que output_dir es algo como "/proyecto/audios/"
            # Pero queremos devolver solo "audios/audio_xxx.mp3"
            # Buscamos el nombre de la carpeta base (audios) y construimos ruta relativa
            nombre_carpeta_salida = os.path.basename(os.path.normpath(output_dir))
            ruta_relativa = os.path.join(nombre_carpeta_salida, nombre_archivo_audio)
            ruta_relativa = os.path.normpath(ruta_relativa)
            print(ruta_relativa)
        else:
            print(f"ERROR: El archivo no se creó en {ruta_audio}", file=sys.stderr)
            sys.exit(1)

    except Exception as e:
        print(f"Error al generar el audio con gTTS: {e}", file=sys.stderr)
        sys.exit(1)
