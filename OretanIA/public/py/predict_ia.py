import sys
import joblib
import numpy as np
from sklearn.ensemble import RandomForestClassifier

# Cargar modelo preentrenado (debes generar este archivo primero)
try:
    model = joblib.load('modelo_prediccion.pkl')
except FileNotFoundError:
    # Modelo de ejemplo (reemplaza con tu modelo real)
    from sklearn.datasets import make_classification
    X, y = make_classification(n_samples=100, n_features=4, random_state=42)
    model = RandomForestClassifier().fit(X, y)
    joblib.dump(model, 'modelo_prediccion.pkl')

def predecir(datos_str):
    # Convertir string a array numérico
    # Ej: "25,50000,1,0" → [25, 50000, 1, 0]
    try:
        entrada = np.array([float(x) for x in datos_str.split(',')]).reshape(1, -1)
        prediccion = model.predict(entrada)
        probabilidad = model.predict_proba(entrada)[0].max()
        return f"{prediccion[0]} (confianza: {probabilidad:.2f})"
    except Exception as e:
        return f"Error: {str(e)}"

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Uso: python predict_ia.py 'dato1,dato2,dato3'")
        sys.exit(1)

    datos_entrada = sys.argv[1]
    resultado = predecir(datos_entrada)
    print(resultado)