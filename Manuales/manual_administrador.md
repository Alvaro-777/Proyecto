<div align="center">

# Manual del Administrador
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

1. [Arquitectura del sistema](#1-arquitectura-del-sistema)
2. [Estructura de directorios](#2-estructura-de-directorios)
3. [Base de datos](#3-base-de-datos)
4. [Controladores y rutas](#4-controladores-y-rutas)
5. [Scripts Python (IA)](#5-scripts-python-ia)
6. [Sistema de autenticación](#6-sistema-de-autenticación)
7. [Sistema de créditos](#7-sistema-de-créditos)
8. [Gestión de archivos subidos](#8-gestión-de-archivos-subidos)
9. [Configuración de entorno](#9-configuración-de-entorno)
10. [Logs y depuración](#10-logs-y-depuración)
11. [Tareas de mantenimiento](#11-tareas-de-mantenimiento)

</div>



<div style="page-break-before: always;"></div>

## 1. Arquitectura del sistema

OretanIA sigue el patrón **MVC** sobre Symfony 6.4.

```
Navegador
    │
    ▼
Symfony Router (config/routes.yaml)
    │
    ▼
Controller (src/Controller/)
    ├── Lee/escribe en BD via Doctrine ORM (src/Entity/, src/Repository/)
    ├── Llama scripts Python via shell_exec() (public/py/)
    └── Renderiza templates Twig (templates/)
```

### Tecnologías

| Capa | Tecnología | Versión |
|------|-----------|---------|
| Servidor web | Symfony CLI / Apache | 6.4 |
| Lenguaje backend | PHP | ≥ 8.1 |
| ORM | Doctrine | 3.6 |
| Base de datos | MySQL | 8.0 |
| Templates | Twig | 6.4 |
| IA Chatbot | Google Gemini API | gemini-flash-latest |
| IA Audio | gTTS (Google TTS) | — |
| IA Predicción | scikit-learn | — |
| Scripts IA | Python | 3.x |
| Frontend | HTML5 + CSS3 + JS vanilla | — |
| Alertas | SweetAlert2 | 11.26.24 |


<div style="page-break-before: always;"></div>

## 2. Estructura de directorios

```
OretanIA/
├── bin/
│   └── console                    # CLI de Symfony
├── config/
│   ├── packages/
│   │   ├── doctrine.yaml          # Configuración ORM + BD
│   │   ├── framework.yaml         # Sesiones (nombre: oretan-ia)
│   │   ├── twig.yaml              # Motor de plantillas
│   │   └── monolog.yaml           # Sistema de logs
│   ├── routes.yaml                # Autodescubrimiento de rutas
│   └── services.yaml              # Inyección de dependencias
├── migrations/                    # Migraciones Doctrine
├── public/
│   ├── index.php                  # Punto de entrada HTTP
│   ├── css/                       # Hojas de estilo
│   ├── script/                    # JavaScript (8 archivos)
│   ├── imagenes/                  # Imágenes estáticas
│   ├── py/                        # Scripts Python de IA
│   │   ├── chatbot_ia.py          # Gemini API wrapper
│   │   ├── procesar_audio.py      # gTTS + extracción de documentos
│   │   └── predict_ia.py          # scikit-learn regresión lineal
│   ├── uploads/                   # Archivos subidos por usuarios
│   │   ├── {user_id}/             # Documentos por usuario
│   │   └── perfiles/              # Fotos de perfil
│   └── audios/                    # MP3 generados
├── src/
│   ├── Controller/                # 7 controladores
│   ├── Entity/                    # 9 entidades Doctrine
│   ├── Repository/                # 6 repositorios
│   └── Kernel.php
├── templates/                     # 13 plantillas Twig
├── var/
│   ├── cache/                     # Caché de Symfony
│   └── log/                       # Logs de la aplicación
├── .env / .env.dev                # Variables de entorno
└── composer.json                  # Dependencias PHP
```


<div style="page-break-before: always;"></div>

## 3. Base de datos

### Esquema completo

#### Tabla `usuario`

| Campo | Tipo | Restricciones | Descripción |
|-------|------|--------------|-------------|
| `id` | INT | PK, AUTO_INCREMENT | Identificador único |
| `correo` | VARCHAR(100) | UNIQUE, NOT NULL | Email del usuario |
| `pswd` | VARCHAR(100) | NOT NULL | Hash bcrypt de la contraseña |
| `nombre` | VARCHAR(50) | NOT NULL | Nombre |
| `apellido` | VARCHAR(50) | NOT NULL | Apellido |
| `creditos` | INT | DEFAULT 50, NULL | Saldo de créditos |
| `fecha_registro` | DATETIME | NULL | Fecha de creación de cuenta |
| `foto_perfil` | VARCHAR(255) | NULL | Nombre de archivo de foto |

#### Tabla `ia`

| Campo | Tipo | Restricciones | Descripción |
|-------|------|--------------|-------------|
| `id` | INT | PK | Identificador del servicio |
| `nombre` | VARCHAR | NOT NULL | Nombre del servicio |
| `costo_creditos` | INT | NOT NULL | Créditos por uso |
| `accesible_anonimos` | TINYINT(1) | NOT NULL | Si puede usarse sin cuenta |
| `url_externa` | VARCHAR | NULL | URL de API externa si aplica |
| `entrada_permitida` | ENUM('texto','documento','ambas') | NOT NULL | Tipo de entrada |
| `tipo` | ENUM('texto_audio','predictiva','chatbot') | NOT NULL | Categoría del servicio |

**Registros actuales en `ia`:**

| id | nombre | costo | anónimo | entrada | tipo |
|----|--------|-------|---------|---------|------|
| 1 | Audio | 1 | Sí | ambas | texto_audio |
| 2 | Predicción | 1 | No | ambas | predictiva |
| 3 | Chatbot | 1 | No | texto | chatbot |

#### Tabla `historial_uso_ia`

| Campo | Tipo | Restricciones | Descripción |
|-------|------|--------------|-------------|
| `id` | INT | PK, AUTO_INCREMENT | Identificador único del registro |
| `usuario_id` | INT | FK → usuario, NOT NULL | Usuario que realizó el uso |
| `ia_id` | INT | FK → ia, NOT NULL | Servicio IA utilizado |
| `archivo_id` | INT | FK → archivo, NULL | Archivo asociado al uso (si aplica) |
| `texto_input` | TEXT | NULL | Texto introducido o patrón `[USUARIO]...[ASISTENTE]...` |
| `fecha` | DATETIME | NOT NULL | Fecha y hora del uso |
| `ip` | VARCHAR(45) | NULL | Dirección IP del cliente |

#### Tabla `archivo`

| Campo | Tipo | Restricciones | Descripción |
|-------|------|--------------|-------------|
| `id` | INT | PK, AUTO_INCREMENT | Identificador único del archivo |
| `usuario_id` | INT | FK → usuario, NOT NULL | Usuario propietario del archivo |
| `nombre` | VARCHAR(255) | NOT NULL | Nombre del archivo almacenado en disco |
| `peso` | INT | NOT NULL | Tamaño del archivo en bytes |
| `tipo` | VARCHAR(10) | NOT NULL | Extensión del archivo (pdf, docx, csv…) |
| `fecha_subida` | DATETIME | NOT NULL | Fecha y hora de la subida |

#### Tabla `pago`

| Campo | Tipo | Restricciones | Descripción |
|-------|------|--------------|-------------|
| `id` | INT | PK, AUTO_INCREMENT | Identificador único del pago |
| `usuario_id` | INT | FK → usuario, NOT NULL | Usuario que realizó el pago |
| `cantidad` | DECIMAL | NOT NULL | Importe cobrado (con descuento aplicado si procede) |
| `creditos_obtenidos` | INT | NOT NULL | Créditos añadidos a la cuenta tras el pago |
| `fecha` | DATETIME | NOT NULL | Fecha y hora de la transacción |
| `metodo` | VARCHAR(50) | NOT NULL | Método de pago (valor fijo: 'Simulado' en desarrollo) |
| `valido` | TINYINT(1) | NOT NULL | Estado del pago: 1 = válido, 0 = inválido |

### Ejecutar migraciones

```bash
# Ver estado de migraciones
php bin/console doctrine:migrations:status

# Ejecutar migraciones pendientes
php bin/console doctrine:migrations:migrate

# Crear nueva migración desde cambios en entidades
php bin/console doctrine:migrations:diff
```


<div style="page-break-before: always;"></div>

## 4. Controladores y rutas

### Tabla de rutas completa

<div style="font-size: 0.72em;">

| Ruta | Método | Controlador::método | Descripción |
|------|--------|---------------------|-------------|
| `/` | GET | `Home::index` | Página de inicio |
| `/login` | GET | `Usuario::login` | Formulario de login |
| `/registro` | GET | `Usuario::registro` | Formulario de registro |
| `/usuario/new` | POST | `Usuario::createUsuario` | Crear usuario |
| `/verify-login` | POST | `Usuario::verifyLogin` | Autenticar usuario |
| `/verify-credentials` | POST | `Usuario::verifyCredentials` | Verificar credenciales (AJAX) |
| `/logout` | GET | `Usuario::logout` | Cerrar sesión |
| `/audio` | GET | `Audio::index` | Vista del generador de audio |
| `/audio/procesar` | POST | `Audio::procesar` | Procesar audio |
| `/chatbotia` | GET | `Chatbot::index` | Vista del chatbot |
| `/chatbotia/enviar` | POST | `Chatbot::enviarMensaje` | Enviar mensaje al chatbot |
| `/chatbotia/reiniciar` | POST | `Chatbot::reiniciarChat` | Reiniciar conversación |
| `/chatbotia/inicial` | GET | `Chatbot::estadoInicial` | Estado inicial (AJAX) |
| `/predictia` | GET | `PredictIa::index` | Vista de predicción |
| `/predictia/procesar` | POST | `PredictIa::procesar` | Ejecutar predicción |
| `/pago` | GET | `Pago::mostrarPlanes` | Página de planes |
| `/pago/checkout` | GET/POST | `Pago::procesarPago` | Checkout y confirmación |
| `/perfil` | GET | `Perfil::index` | Dashboard del perfil |
| `/perfil/configuracion` | GET | `Perfil::configuracion` | Configuración de cuenta |
| `/perfil/configuracion/cambiar-clave` | GET/POST | `Perfil::newPassword` | Cambiar contraseña |
| `/perfil/configuracion/borrar-cuenta` | POST | `Perfil::borrar` | Eliminar cuenta |
| `/perfil/foto` | POST | `Perfil::subirFoto` | Subir foto de perfil |

</div>

### Convención de sesión

La autenticación usa sesión PHP con clave `user-id`. Comprobación estándar en todos los controladores protegidos:

```php
$userId = $session->get('user-id');
$usuario = $userId ? $usuarioRepository->find($userId) : null;
if (empty($userId) || !$usuario) {
    return $this->redirectToRoute('login'); // o 'inicio'
}
```


<div style="page-break-before: always;"></div>

## 5. Scripts Python (IA)

Los scripts se invocan desde PHP usando `shell_exec()`. El binario de Python se detecta automáticamente:

```php
$pythonBin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'py -3' : 'python3';
```

### chatbot_ia.py

**Ubicación:** `public/py/chatbot_ia.py`  
**Entrada:** `argv[1]` = mensaje del usuario (string)  
**Salida:** Respuesta de Gemini por `stdout`  
**API key:** Variable de entorno `GEMINI_API_KEY` (leída desde `.env` con `python-dotenv`)

Modelos intentados en orden:
1. `gemini-flash-latest` (principal)
2. `gemini-2.5-flash` (fallback si error 404)

```bash
# Prueba manual en Windows
py -3 public/py/chatbot_ia.py "¿Qué es la inteligencia artificial?"

# En Linux
python3 public/py/chatbot_ia.py "¿Qué es la inteligencia artificial?"
```

### procesar_audio.py

**Ubicación:** `public/py/procesar_audio.py`  
**Entrada:**
- `argv[1]` = ruta de archivo o texto plano
- `argv[2]` = directorio de salida para el MP3
**Salida:** Ruta relativa del MP3 generado (ej: `audios/audio_1234567890.mp3`)

Extracción de texto soportada:
- `.txt` — lectura directa UTF-8
- `.pdf` — PyPDF2
- `.docx` — python-docx

```bash
# Desde texto
py -3 public/py/procesar_audio.py "Hola mundo" "C:/ruta/public/audios"

# Desde archivo
py -3 public/py/procesar_audio.py "C:/ruta/uploads/1/doc.pdf" "C:/ruta/public/audios"
```

### predict_ia.py

**Ubicación:** `public/py/predict_ia.py`  
**Entrada:** `argv[1]` = texto con valores CSV o ruta de archivo  
**Salida:** Texto con resultado de predicción

Tipos de datos detectados automáticamente:
- **Numérico:** `10, 20, 30` → predice siguiente número
- **Fechas:** `2024-01-01, 2024-02-01` → predice siguiente fecha (formatos: `%Y-%m-%d`, `%d/%m/%Y`, `%Y-%m-%d %H:%M:%S`)
- **Letras:** `a, b, c` → predice siguiente letra

Formatos de archivo soportados: `.csv`, `.txt`, `.json`, `.xls`, `.xlsx`

```bash
py -3 public/py/predict_ia.py "10,20,30,40,50"
py -3 public/py/predict_ia.py "C:/ruta/uploads/1/datos.csv"
```


<div style="page-break-before: always;"></div>

## 6. Sistema de autenticación

OretanIA **no usa Symfony Security Bundle**. La autenticación es manual:

### Registro
```php
$usuario->setPswd(password_hash($password, PASSWORD_DEFAULT)); // bcrypt
$usuario->setCreditos(50); // créditos iniciales
$request->getSession()->set('user-id', $usuario->getId());
```

### Login
```php
if (!$user || !password_verify($password, $user->getPswd())) {
    // Error de credenciales
}
$request->getSession()->set('user-id', $user->getId());
```

### Logout
```php
$session->invalidate(); // Destruye toda la sesión
```

### Nombre de la sesión
Configurado en `config/packages/framework.yaml`:
```yaml
session:
    name: "oretan-ia"
```


<div style="page-break-before: always;"></div>

## 7. Sistema de créditos

### Descuento de primera compra

```php
$esPrimeraCompra = !$pagoRepo->findOneBy(['usuario' => $usuario]);
$precio = $esPrimeraCompra ? $plan['precio'] * 0.9 : $plan['precio'];
```

### Planes definidos en PagoController

```php
$planes = [
    1 => ['precio' => 4.99, 'creditos' => 500,  'nombre' => 'Básico'],
    2 => ['precio' => 9.99, 'creditos' => 1200, 'nombre' => 'Pro (+200 bonus)'],
    3 => ['precio' => 14.99,'creditos' => 2000, 'nombre' => 'Premium (+500 bonus)'],
];
```

> Los bonus de créditos están incluidos en el total. El Plan Pro da 1200 créditos (de los cuales 200 son bonus).

### Deducción de crédito

Ocurre **después** de un uso exitoso del servicio:
```php
$usuario->setCreditos($usuario->getCreditos() - 1);
$entityManager->flush();
```


<div style="page-break-before: always;"></div>

## 8. Gestión de archivos subidos

### Rutas de almacenamiento

| Tipo | Ruta en disco |
|------|--------------|
| Documentos (audio/predicción) | `public/uploads/{user_id}/` |
| Fotos de perfil | `public/uploads/perfiles/` |
| Audios generados | `public/audios/` |

### Restricciones por servicio

**Audio:**
- Extensiones: `txt`, `pdf`, `docx`
- Solo usuarios autenticados pueden subir archivos

**Predicción:**
- Extensiones: `csv`, `json`, `xls`, `xlsx`, `txt`

**Foto de perfil:**
- Extensiones: `jpg`, `jpeg`, `png`, `webp`
- Tamaño máximo: **2 MB**
- La foto anterior se elimina automáticamente al subir una nueva

### Generación de nombres únicos

Si ya existe un archivo con el mismo nombre, se añade un sufijo numérico:
```
documento.pdf → documento(1).pdf → documento(2).pdf
```


<div style="page-break-before: always;"></div>

## 9. Configuración de entorno

### Variables de entorno requeridas

Archivo `.env` o `.env.dev` en la raíz del proyecto:

```env
# Conexión a base de datos
DATABASE_URL="mysql://root:root@127.0.0.1:33100/oretan-ia?serverVersion=8.0"

# API Key de Google Gemini (para el chatbot)
GEMINI_API_KEY=AIza...tu_clave_aqui

# Entorno Symfony
APP_ENV=dev
APP_SECRET=cambia_esto_en_produccion
```

> `GEMINI_API_KEY` es leída por los scripts Python via `python-dotenv`. El archivo `.env` debe estar en la raíz del proyecto Symfony.

### Dependencias PHP

```bash
composer install
composer dump-autoload
```

### Dependencias Python

```bash
# Windows
py -3 -m pip install google-genai python-dotenv gTTS PyPDF2 \
    python-docx pandas scikit-learn numpy openpyxl xlrd

# Linux
pip3 install google-genai python-dotenv gTTS PyPDF2 \
    python-docx pandas scikit-learn numpy openpyxl xlrd
```


<div style="page-break-before: always;"></div>

## 10. Logs y depuración

### Logs de Symfony

Los logs se almacenan en `var/log/`:

```bash
# Ver log de desarrollo en tiempo real
tail -f var/log/dev.log

# Solo errores
grep "ERROR\|CRITICAL" var/log/dev.log
```

### Logs del chatbot

El `ChatbotController` escribe logs de depuración con `error_log()`:

```
=== DEBUG CHATBOT ===
Ruta del script: /ruta/public/py/chatbot_ia.py
Archivo existe: SÍ
Mensaje usuario: ¿Hola?
Comando: py -3 "..." "..."
Salida: Hola, ¿en qué puedo ayudarte?
=== FIN DEBUG ===
```

Estos aparecen en el `error_log` de PHP (en Windows: en el `Event Log` o en `php_errors.log` dependiendo de la configuración).

### Profiler de Symfony (solo en dev)

Con el servidor en marcha, accede a `/_profiler` para ver:
- Peticiones HTTP recientes
- Queries SQL ejecutadas
- Tiempo de respuesta
- Información de sesión


<div style="page-break-before: always;"></div>

## 11. Tareas de mantenimiento

### Limpiar caché de Symfony

```bash
php bin/console cache:clear
```

### Limpiar audios generados

Los archivos MP3 en `public/audios/` se acumulan indefinidamente. Se puede automatizar su limpieza:

```bash
# Eliminar audios con más de 24 horas (Linux)
find public/audios/ -name "*.mp3" -mtime +1 -delete

# Windows (PowerShell)
Get-ChildItem public\audios\*.mp3 |
  Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-1) } |
  Remove-Item
```

### Limpiar archivos huérfanos

Archivos en `public/uploads/` que ya no tienen referencia en la tabla `archivo`:

```sql
-- Ver archivos en BD sin referencia (ejemplo)
SELECT * FROM archivo WHERE usuario_id NOT IN (SELECT id FROM usuario);
```

### Ver usuarios registrados

```sql
SELECT id, correo, nombre, apellido, creditos, fecha_registro
FROM usuario
ORDER BY fecha_registro DESC;
```

### Modificar créditos de un usuario manualmente

```sql
UPDATE usuario SET creditos = 100 WHERE correo = 'usuario@ejemplo.com';
```

### Ver historial de uso por servicio

```sql
SELECT ia.nombre AS servicio, COUNT(*) AS usos
FROM historial_uso_ia h
JOIN ia ON h.ia_id = ia.id
GROUP BY ia.nombre;
```

### Crear nueva migración tras cambios en entidades

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```
