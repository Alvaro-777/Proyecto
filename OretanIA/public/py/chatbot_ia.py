# public/py/chatbot_ia.py
import sys
import os
from google import genai
from dotenv import load_dotenv

load_dotenv()

api_key = os.getenv('GEMINI_API_KEY')
if not api_key:
    print("Error: API key no configurada en .env")
    sys.exit(1)

client = genai.Client(api_key=api_key)

def generar_respuesta(mensaje_usuario):
    """Generar respuesta usando el modelo disponible en tu cuenta"""
    try:
        # Usar el modelo que SÍ tienes disponible
        response = client.models.generate_content(
            model='gemini-flash-latest',  # ← ¡Este modelo SÍ existe en tu cuenta!
            contents=mensaje_usuario,
            config={
                'system_instruction': 'Eres un asistente útil y amable. Siempre debes responder en español, de forma clara, concisa y profesional.'
            }
        )
        return response.text.strip()

    except Exception as e:
        error_msg = str(e)
        if "404" in error_msg:
            # Intentar con otro modelo disponible
            try:
                response = client.models.generate_content(
                    model='gemini-2.5-flash',
                    contents=mensaje_usuario,
                    config={'system_instruction': 'Responde siempre en español.'}
                )
                return response.text.strip()
            except Exception as fallback_error:
                return f"Error: {str(fallback_error)}"
        else:
            return f"Error en Gemini: {error_msg}"

def main():
    if len(sys.argv) != 2:
        print("Uso: python chatbot_ia.py '<mensaje>'")
        sys.exit(1)

    mensaje_usuario = sys.argv[1].strip()
    if not mensaje_usuario:
        mensaje_usuario = "Hola"

    respuesta = generar_respuesta(mensaje_usuario)
    print(respuesta)

if __name__ == "__main__":
    main()


    """
    apt update
    apt install -y python3-full python3-venv

cd /var/www/html/OretanIA
python3 -m venv venv

source venv/bin/activate

pip install openpyxl xlrd google-genai python-dotenv

python3 -m pip install google-genai python-dotenv --break-system-packages


    # .env
    GEMINI_API_KEY=AIzaTuApiKeyRealAqui

    py -3 -m pip install google-genai python-dotenv
    """