# public/py/predict_ia.py
import sys
import os
import json
import pandas as pd
import numpy as np
from sklearn.linear_model import LinearRegression

def predecir_serie_temporal(datos_str):
    """Predecir el siguiente valor en una serie temporal"""
    try:
        # Convertir string a array numérico
        valores = np.array([float(x.strip()) for x in datos_str.split(',')])
        n = len(valores)

        if n < 2:
            return "Error: Se necesitan al menos 2 valores para predecir una tendencia"
        if n > 100:
            return "Error: El número máximo de valores permitidos es 100"

        # Crear variables X (índices) e y (valores)
        X = np.arange(n).reshape(-1, 1)  # [0], [1], [2], ...
        y = valores

        # Entrenar modelo de regresión lineal
        model = LinearRegression()
        model.fit(X, y)

        # Predecir siguiente valor (índice = n)
        prediccion = model.predict([[n]])[0]

        # Calcular confianza (R² score)
        r2_score = model.score(X, y)
        confianza = max(0, min(100, r2_score * 100))

        # Determinar si la tendencia es creciente
        pendiente = model.coef_[0]
        es_posible = "Sí" if pendiente > 0 else "No"

        return (
            f"¿Es posible?: {es_posible}\n"
            f"Confianza: {confianza:.1f}%\n"
            f"Resultado esperado: {prediccion:,.2f}"
        )

    except ValueError:
        return "Error: Valores no numéricos en los datos proporcionados"
    except Exception as e:
        return f"Error inesperado: {str(e)}"

def cargar_datos_desde_archivo(ruta_archivo):
    """Cargar datos desde diferentes formatos como series temporales"""
    extension = os.path.splitext(ruta_archivo)[1].lower()

    try:
        if extension == '.csv':
            # Leer CSV: cada fila es una serie temporal
            df = pd.read_csv(ruta_archivo, header=None)
            return df
        elif extension == '.txt':
            try:
                df = pd.read_csv(ruta_archivo, header=None)
            except:
                df = pd.read_csv(ruta_archivo, header=None, delim_whitespace=True)
            return df
        elif extension == '.json':
            with open(ruta_archivo, 'r', encoding='utf-8') as f:
                data = json.load(f)
                # Asumir que es una lista de listas: [[1,2,3], [4,5,6]]
                if isinstance(data, list):
                    return pd.DataFrame(data)
                else:
                    raise ValueError("JSON debe ser una lista de listas")
        elif extension == '.xlsx':
            return pd.read_excel(ruta_archivo, header=None, engine='openpyxl')
        elif extension == '.xls':
            return pd.read_excel(ruta_archivo, header=None, engine='xlrd')
        else:
            raise ValueError(f"Formato no soportado: {extension}")
    except Exception as e:
        raise ValueError(f"Error al leer {ruta_archivo}: {str(e)}")

def procesar_archivo_series(ruta_archivo):
    """Procesar archivo donde cada fila es una serie temporal"""
    try:
        df = cargar_datos_desde_archivo(ruta_archivo)

        resultados = []
        for idx, row in df.iterrows():
            # Convertir fila a string de valores separados por comas
            # Filtrar valores NaN
            valores_fila = row.dropna().tolist()
            if not valores_fila:
                continue

            # Convertir a string
            datos_str = ','.join(str(x) for x in valores_fila)

            resultado = predecir_serie_temporal(datos_str)
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