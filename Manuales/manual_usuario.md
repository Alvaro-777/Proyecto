<div align="center">

# Manual de Usuario
# OretanIA

<br><br>

![](img/logoDocs.png)

<br><br>

**Participantes:** Raúl Lumbreras Delegido, Francisco Manuel Vigil Ruiz, Álvaro Colmenero Rodríguez y Alejandro Medel Martínez  
**Centro:** I.E.S. Oretania  
**Curso:** 2025-2026

</div>

<div style="page-break-after: always;"></div>


---

## Índice

<div style="font-size: 1.2em; line-height: 2.4;">

1. [¿Qué es OretanIA?](#1-qué-es-oretania)
2. [Registro e inicio de sesión](#2-registro-e-inicio-de-sesión)
3. [Sistema de créditos](#3-sistema-de-créditos)
4. [Servicio: Generador de Audio](#4-servicio-generador-de-audio)
5. [Servicio: Predicción IA](#5-servicio-predicción-ia)
6. [Servicio: Chatbot IA](#6-servicio-chatbot-ia)
7. [Mi perfil](#7-mi-perfil)
8. [Comprar créditos](#8-comprar-créditos)
9. [Configuración de cuenta](#9-configuración-de-cuenta)
10. [Preguntas frecuentes](#10-preguntas-frecuentes)

</div>



<div style="page-break-before: always;"></div>

## 1. ¿Qué es OretanIA?

OretanIA es una plataforma web que ofrece tres servicios de inteligencia artificial:

| Servicio | Descripción | Requiere cuenta |
|---------|------------|----------------|
| **Generador de Audio** | Convierte texto o documentos a voz en MP3 | No (texto); Sí (archivos) |
| **Predicción IA** | Predice el siguiente valor de una serie de datos | Sí |
| **Chatbot IA** | Asistente virtual inteligente en español | Sí |

Cada uso de un servicio consume **1 crédito**. Al registrarte recibes **50 créditos** gratuitos.


## 2. Registro e inicio de sesión

### Crear una cuenta

1. Haz clic en **Registrarse** en el menú superior.
2. Rellena el formulario:
   - **Nombre** y **apellido**
   - **Correo electrónico** (debe ser único en el sistema)
   - **Contraseña** (mínimo 8 caracteres, debe incluir mayúscula, minúscula y número)
   - **Confirmar contraseña**
3. Haz clic en **Crear cuenta**.
4. Recibirás **50 créditos de bienvenida** automáticamente.

> Si el correo ya está registrado, el sistema te lo indicará.

### Iniciar sesión

1. Haz clic en **Iniciar sesión** en el menú superior.
2. Introduce tu **correo electrónico** y **contraseña**.
3. Haz clic en **Entrar**.

### Cerrar sesión

Haz clic en tu nombre de usuario en el menú y selecciona **Cerrar sesión**.


<div style="page-break-before: always;"></div>

## 3. Sistema de créditos

Los créditos son la moneda interna de OretanIA. Cada servicio IA consume **1 crédito** por uso.

- Al registrarte: **50 créditos** gratuitos.
- Puedes ver tu saldo de créditos en la barra de navegación superior (una vez iniciada sesión).
- Cuando se agoten, puedes comprar más en la sección [Planes](#8-comprar-créditos).


## 4. Servicio: Generador de Audio

**Ruta:** `/audio`  
**Coste:** 1 crédito (solo si estás registrado; gratis si usas texto sin cuenta)

Este servicio convierte texto escrito o el contenido de un documento en un archivo de audio MP3 con voz en español.

### Cómo usarlo

#### Opción A: Texto directo

1. Navega a **Audio** desde el menú.
2. Escribe o pega el texto en el cuadro de texto.
3. Haz clic en **Generar Audio**.
4. El archivo MP3 se descargará automáticamente.

> Los usuarios sin cuenta solo pueden usar esta opción.

#### Opción B: Subir un documento (requiere cuenta)

1. Navega a **Audio** desde el menú (con sesión iniciada).
2. Haz clic en **Adjuntar archivo**.
3. Selecciona un archivo de tipo:
   - `.txt` — Archivo de texto plano
   - `.pdf` — Documento PDF
   - `.docx` — Documento Word
4. Haz clic en **Generar Audio**.
5. El archivo MP3 se descargará automáticamente.

> No puedes subir un archivo y escribir texto al mismo tiempo: elige una sola opción.

### Restricciones

- Solo texto **o** archivo, nunca los dos a la vez.
- Formatos de archivo aceptados: `.txt`, `.pdf`, `.docx`.
- Documentos muy largos pueden tardar varios segundos en procesarse.


<div style="page-break-before: always;"></div>

## 5. Servicio: Predicción IA

**Ruta:** `/predictia`  
**Coste:** 1 crédito  
**Requiere cuenta:** Sí

Este servicio analiza una serie de datos y predice cuál será el siguiente valor utilizando un modelo de regresión lineal.

### Tipos de datos soportados

| Tipo | Ejemplo de entrada |
|------|-------------------|
| **Números** | `10, 20, 30, 40` |
| **Fechas** | `2024-01-01, 2024-02-01, 2024-03-01` |
| **Letras** | `a, b, c, d` |

### Cómo usarlo

#### Opción A: Introducir datos manualmente

1. Navega a **Predicción** desde el menú.
2. Escribe tu serie de valores separados por comas en el campo de texto.
   - Ejemplo: `100, 200, 300, 400`
3. Haz clic en **Predecir**.
4. Verás el resultado con:
   - **¿Es posible?** — Si la tendencia es creciente.
   - **Confianza** — Porcentaje de fiabilidad del modelo (0–100%).
   - **Resultado esperado** — El valor predicho para el siguiente paso.

#### Opción B: Subir un archivo de datos

1. Haz clic en **Subir archivo**.
2. Selecciona un archivo de tipo:
   - `.csv`, `.txt` — Una serie por fila
   - `.xls`, `.xlsx` — Hoja de Excel, una serie por fila
   - `.json` — Lista de listas
3. El sistema procesará cada fila como una serie independiente.
4. Verás los resultados de todas las filas.

### Restricciones

- Mínimo **2 valores** para realizar una predicción.
- Máximo **100 valores** por serie.
- Solo texto **o** archivo, nunca los dos.


<div style="page-break-before: always;"></div>

## 6. Servicio: Chatbot IA

**Ruta:** `/chatbotia`  
**Coste:** 1 crédito por mensaje enviado  
**Requiere cuenta:** Sí

El chatbot es un asistente virtual impulsado por la IA de Google Gemini, configurado para responder siempre en español de forma clara y profesional.

### Cómo usarlo

1. Navega a **Chatbot** desde el menú.
2. Escribe tu mensaje en el campo de texto inferior.
3. Pulsa **Enter** o haz clic en el botón de enviar.
4. El asistente responderá en unos segundos.
5. Cada respuesta consume **1 crédito**.

### Historial

El chatbot recuerda los **últimos 2 intercambios** de la sesión. Al cargar la página, verás el historial reciente.

### Reiniciar conversación

Haz clic en el botón **Reiniciar** para limpiar el historial y comenzar desde cero.

### Restricciones

- No puedes enviar mensajes vacíos.
- Si no tienes créditos, el sistema te redirigirá a la página de planes.


<div style="page-break-before: always;"></div>

## 7. Mi perfil

**Ruta:** `/perfil`  
**Requiere cuenta:** Sí

En tu perfil puedes ver:

- **Tus datos personales** (nombre, apellido, correo, créditos disponibles).
- **Foto de perfil** — Puedes subir o cambiar tu foto.
- **Historial de uso** — Registro de todos los servicios que has utilizado.
- **Historial de pagos** — Registro de tus compras de créditos.

### Cambiar foto de perfil

1. En tu perfil, haz clic en la imagen de perfil o en el botón de cámara.
2. Selecciona una imagen en formato `.jpg`, `.jpeg`, `.png` o `.webp`.
3. El archivo debe pesar **menos de 2 MB**.
4. La imagen se actualizará automáticamente.


## 8. Comprar créditos

**Ruta:** `/pago`  
**Requiere cuenta:** Sí

Cuando se acaben tus créditos, puedes comprar más con los siguientes planes:

| Plan | Precio | Créditos | Bonus |
|------|--------|----------|-------|
| **Básico** | €4,99 | 500 | — |
| **Pro** | €9,99 | 1.200 | +200 bonus |
| **Premium** | €14,99 | 2.000 | +500 bonus |

> **Descuento de primera compra:** Si es tu primera compra, obtienes un **10% de descuento** automático.

### Proceso de compra

1. Selecciona un plan en la página de planes.
2. Introduce los datos de tu tarjeta en el formulario de pago.
3. Haz clic en **Confirmar pago**.
4. Los créditos se añadirán inmediatamente a tu cuenta.

> El pago está simulado (entorno de desarrollo). No se realiza ningún cargo real.


<div style="page-break-before: always;"></div>

## 9. Configuración de cuenta

**Ruta:** `/perfil/configuracion`

### Cambiar contraseña

1. Ve a **Perfil → Configuración**.
2. Introduce tu **contraseña actual**.
3. Introduce y confirma tu **nueva contraseña**.
4. Haz clic en **Guardar cambios**.

### Eliminar cuenta

> **Atención:** Esta acción es **irreversible**. Se eliminarán todos tus datos.

1. Ve a **Perfil → Configuración → Eliminar cuenta**.
2. Introduce tu **correo electrónico** y **contraseña** para confirmar.
3. Haz clic en **Eliminar cuenta**.


## 10. Preguntas frecuentes

**¿Puedo usar el generador de audio sin registrarme?**  
Sí, pero solo con texto escrito directamente. Para subir documentos necesitas una cuenta.

**¿Qué pasa si el chatbot no responde?**  
Puede ser un problema temporal con la API de Gemini. Espera unos segundos y vuelve a intentarlo. Si el problema persiste, contacta al administrador.

**¿Cómo sé cuántos créditos me quedan?**  
El saldo de créditos aparece en la barra de navegación superior cuando tienes sesión iniciada.

**¿Mis créditos caducan?**  
No. Los créditos no tienen fecha de caducidad.

**¿Qué formatos de audio genera el servicio?**  
Siempre genera archivos `.mp3`.

**¿La predicción es exacta?**  
La predicción se basa en regresión lineal. El campo "Confianza" te indica qué tan fiable es el resultado según la tendencia de tus datos. No es una garantía.

**¿El chatbot recuerda conversaciones de sesiones anteriores?**  
Solo recuerda los últimos 2 intercambios. Al cerrar sesión o reiniciar el chat, el historial visible se reinicia.
