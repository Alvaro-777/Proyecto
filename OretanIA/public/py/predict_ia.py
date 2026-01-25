# public/py/predict_ia.py
import sys
import os
import json
import re
import pandas as pd
import numpy as np
from sklearn.linear_model import LinearRegression
from datetime import datetime, timedelta

class TipoDato:
    NUMERICO = "numerico"
    LETRAS = "letras"
    FECHA = "fecha"

def intentar_parsear_fechas(partes):
    """Intentar parsear una lista de strings como fechas"""
    formatos_fecha = ["%Y-%m-%d", "%d/%m/%Y", "%Y-%m-%d %H:%M:%S"]

    for formato in formatos_fecha:
        try:
            fechas = []
            for parte in partes:
                if parte.strip():
                    fecha = datetime.strptime(parte.strip(), formato)
                    fechas.append(fecha)
            if len(fechas) >= 2:
                return fechas, formato
        except ValueError:
            continue
    return None, None

def convertir_a_valores_procesables(datos_str):
    """Convertir entrada a valores procesables con detección correcta de tipos"""
    partes = [x.strip() for x in datos_str.split(',') if x.strip()]

    if len(partes) < 2:
        raise ValueError("Se necesitan al menos 2 valores para predecir")

    # 1. Primero intentar fechas (prioridad alta)
    fechas, formato_fecha = intentar_parsear_fechas(partes)
    if fechas is not None:
        # Convertir fechas a días desde la primera
        dias = [(fecha - fechas[0]).days for fecha in fechas]
        return dias, TipoDato.FECHA, (fechas[0], formato_fecha)

    # 2. Luego intentar letras puras (solo letras, nada más)
    if all(re.match(r'^[a-zA-Z]+$', parte) for parte in partes):
        letras = partes
        valores_numericos = [ord(letra.lower()) - ord('a') + 1 for letra in letras]
        return valores_numericos, TipoDato.LETRAS, letras

    # 3. Finalmente, extraer números de cualquier texto
    numeros_extraidos = []
    for parte in partes:
        # Buscar números en cada parte
        nums = re.findall(r'-?\d+\.?\d*', parte)
        if nums:
            numeros_extraidos.extend([float(x) for x in nums])

    if len(numeros_extraidos) >= 2:
        return numeros_extraidos, TipoDato.NUMERICO, None

    raise ValueError("No se pudieron identificar suficientes datos válidos")

def predecir_serie_temporal(datos_str):
    """Predecir manteniendo el tipo de dato original"""
    try:
        valores_numericos, tipo_dato, metadata = convertir_a_valores_procesables(datos_str)
        n = len(valores_numericos)

        if n > 100:
            return "Error: Demasiados valores (máximo 100)"

        # Entrenar modelo
        X = np.arange(n).reshape(-1, 1)
        y = np.array(valores_numericos)
        model = LinearRegression()
        model.fit(X, y)

        prediccion_numerica = model.predict([[n]])[0]
        r2_score = model.score(X, y)
        confianza = max(0, min(100, r2_score * 100))

        pendiente = model.coef_[0]
        es_posible = "Sí" if pendiente > 0 else "No"

        # Convertir predicción al tipo original
        if tipo_dato == TipoDato.LETRAS:
            letra_num = int(round(prediccion_numerica))
            letra_num = max(1, min(26, letra_num))  # Asegurar rango a-z
            resultado_final = chr(ord('a') + letra_num - 1)
            resultado_texto = f"letra '{resultado_final}'"

        elif tipo_dato == TipoDato.FECHA:
            fecha_base, formato = metadata
            dias_prediccion = int(round(prediccion_numerica))
            fecha_prediccion = fecha_base + timedelta(days=dias_prediccion)
            resultado_final = fecha_prediccion.strftime("%Y-%m-%d")
            resultado_texto = f"fecha {resultado_final}"

        else:  # TipoDato.NUMERICO
            resultado_final = prediccion_numerica
            resultado_texto = f"{resultado_final:,.2f}"

        return (
            f"¿Es posible?: {es_posible}\n"
            f"Confianza: {confianza:.1f}%\n"
            f"Resultado esperado: {resultado_texto}"
        )

    except ValueError as e:
        return f"Error: {str(e)}"
    except Exception as e:
        return f"Error inesperado: {str(e)}"

def cargar_datos_desde_archivo(ruta_archivo):
    """Cargar datos desde diferentes formatos"""
    extension = os.path.splitext(ruta_archivo)[1].lower()

    try:
        if extension == '.csv':
            df = pd.read_csv(ruta_archivo, header=None, dtype=str)
        elif extension == '.txt':
            try:
                df = pd.read_csv(ruta_archivo, header=None, dtype=str)
            except:
                df = pd.read_csv(ruta_archivo, header=None, delim_whitespace=True, dtype=str)
        elif extension == '.json':
            with open(ruta_archivo, 'r', encoding='utf-8') as f:
                data = json.load(f)
                if isinstance(data, list):
                    df = pd.DataFrame(data).astype(str)
                else:
                    raise ValueError("JSON debe ser una lista de listas")
        elif extension == '.xlsx':
            df = pd.read_excel(ruta_archivo, header=None, engine='openpyxl', dtype=str)
        elif extension == '.xls':
            return pd.read_excel(ruta_archivo, header=None, engine='xlrd')
            df = pd.read_excel(ruta_archivo, header=None, engine='xlrd', dtype=str)
        else:
            raise ValueError(f"Formato no soportado: {extension}")
        return df
    except Exception as e:
        raise ValueError(f"Error al leer {ruta_archivo}: {str(e)}")

def procesar_archivo_series(ruta_archivo):
    """Procesar archivo manteniendo tipos de datos"""
    try:
        df = cargar_datos_desde_archivo(ruta_archivo)

        resultados = []
        for idx, row in df.iterrows():
            fila_completa = ','.join([str(x) for x in row.dropna().tolist() if pd.notna(x)])
            if fila_completa.strip():
                resultado = predecir_serie_temporal(fila_completa)
                resultados.append(f"Fila {idx+1}:\n{resultado}")

        if not resultados:
            return "Error: No se encontraron datos válidos en el archivo"

        return "\n\n".join(resultados)

    except Exception as e:
        return f"Error al procesar el archivo: {str(e)}"

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Uso: python predict_ia.py '<datos>' o '<ruta_archivo>'")
        sys.exit(1)

    entrada = sys.argv[1]

    if os.path.isfile(entrada):
        resultado = procesar_archivo_series(entrada)
    else:
        resultado = predecir_serie_temporal(entrada)

    print(resultado)

    """
    py -3 -m pip install openpyxl xlrd

    py -3 -m pip install pandas scikit-learn joblib
    """