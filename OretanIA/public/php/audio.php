<?php
// === CONFIGURACIÓN DE TIEMPO Y ERRORES ===
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(320); // 5 minutos – ajusta si necesitas más
ini_set('max_execution_time', 300);

// Evitar buffering agresivo (para mostrar mensajes en tiempo real)
function safe_ob_clean()
{
    if (ob_get_level()) {
        ob_end_clean();
    }
}

$mostrar_adjunto = true;
function abort_with_message($msg)
{
    safe_ob_clean();
    echo "<!DOCTYPE html><html><head><link rel='stylesheet' href='../css/style.css'><link rel='shortcut icon' href='../imagenes/logo.ico'><meta charset='UTF-8'><title>Error</title></head><body style='background-color: #1e1e1e'>";
    echo "<div class='error'>";
    echo "<h1>⚠️ Error</h1><p>" . htmlspecialchars($msg) . "</p>";
    echo "<p><a href='javascript:history.back()'><button id='volver'>Volver</button></a></p>";
    echo "</div></body></html>";
    exit;
}

// Solo procesamos POST para descarga
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // === Etapa 1: Validaciones iniciales (sin salida aún) ===
    $hay_texto = !empty(trim($_POST['texto_usuario']));
    $hay_archivo = !empty($_FILES['archivo_adjunto']['name']);

    if ($hay_texto && $hay_archivo) {
        abort_with_message('Por favor, escribe un texto O adjunta un archivo, pero no ambos.');
    } elseif (!$hay_texto && !$hay_archivo) {
        abort_with_message('No has escrito nada ni adjuntado ningún archivo.');
    }

    $texto_a_procesar = '';

    // --- Caso: archivo adjunto ---
    if ($hay_archivo) {
        $nombre_archivo = $_FILES['archivo_adjunto']['name'];
        $tmp_archivo = $_FILES['archivo_adjunto']['tmp_name'];
        $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
        $extensiones_permitidas = ['txt', 'pdf', 'docx'];

        if (!in_array($extension, $extensiones_permitidas)) {
            abort_with_message('Tipo de archivo no permitido. Solo se aceptan .txt, .pdf, .docx');
        }

        $dir_subida = dirname(__DIR__) . '/uploads/';
        if (!file_exists($dir_subida)) {
            mkdir($dir_subida, 0777, true);
        }
        $ruta_archivo_final = $dir_subida . basename($nombre_archivo);
        if (!move_uploaded_file($tmp_archivo, $ruta_archivo_final)) {
            abort_with_message('Error al subir el archivo.');
        }
        $texto_a_procesar = $ruta_archivo_final;
    } // --- Caso: texto directo ---
    else {
        $texto_a_procesar = trim($_POST['texto_usuario']);
    }

    // === Etapa 2: Ejecutar Python (todavía sin salida al navegador) ===
    $python_bin = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'py -3' : 'python3';
    $ruta_script = dirname(__DIR__) . '/py/procesar_audio.py';
    $dir_audios = dirname(__DIR__) . '/audios/';

    if (!file_exists($dir_audios)) {
        mkdir($dir_audios, 0777, true);
    }

    $comando = "$python_bin " . escapeshellarg($ruta_script) . " " . escapeshellarg($texto_a_procesar) . " " . escapeshellarg($dir_audios) . " 2>&1";

    $salida = shell_exec($comando);

    if (!$salida) {
        abort_with_message('El script de Python no devolvió ninguna salida. ¿Está instalado y accesible?');
    }

    $audio_generado = trim($salida);
    $audio_ruta_abs = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $audio_generado);
    if (!file_exists($audio_ruta_abs)) {
        abort_with_message("El archivo de audio no se encontró. Salida del script: " . htmlspecialchars($salida));
    }

    // === Etapa 3: ✅ AHORA sí enviamos headers y archivo (sin haber impreso NADA antes) ===
    safe_ob_clean();

    header('Content-Type: audio/mpeg');
    header('Content-Disposition: attachment; filename="' . basename($audio_generado) . '"');
    header('Content-Length: ' . filesize($audio_ruta_abs));
    readfile($audio_ruta_abs);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audio-IA</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="shortcut icon" href="../imagenes/logo.ico">

</head>
<body>
<header class="header">
    <div class="logo">
        <a href="/" title="Inicio">
            <img src="../imagenes/Logo.png" alt="Logo Dreteam-IA">
        </a>
    </div>
    <h1 class="titulo">Generador de Audio Automático</h1>
    <div class="modo">
        <button id="modo-toggle">🌞 / 🌙</button>
        <div class="menu">
            <button class="menu-btn">☰</button>
            <div class="menu-content">
                <a href="./registro">Registrarse</a>
                <a href="./login">Login</a>
                <a href="./perfil">Perfil</a>
                <a href="./planes" class="plan">Plan $X</a>
            </div>
        </div>
    </div>


</header>

<?php if (!empty($mensaje)) echo $mensaje; ?>

<form class="audio" action="audio.php" method="post" enctype="multipart/form-data">
    <label class="titulo" for="texto_usuario">Escribe tu texto:</label><br>
    <textarea id="texto_usuario" name="texto_usuario"
              placeholder="Escribe aquí el texto que deseas convertir a voz..."><?php echo htmlspecialchars($_POST['texto_usuario'] ?? ''); ?></textarea>

    <?php if ($mostrar_adjunto): ?>
        <div>
            <p><label for="archivo_adjunto">O sube un archivo (.txt, .pdf, .docx):</label></p>
            <input type="file" name="archivo_adjunto" id="archivo_adjunto" accept=".txt,.pdf,.docx">
        </div>
    <?php endif; ?>

    <br>
    <p><input type="submit" value="Generar y Descargar Audio"></p>
    <p class="note">Archivos grandes o textos largos pueden tardar hasta 5 minutos. No cierres la página.</p>
</form>
<footer>
    <p>Información básica: contacto | Tel | Redes sociales | Participantes.</p>
</footer>

<script>
    // Toggle modo claro/oscuro
    const toggle = document.getElementById("modo-toggle");
    toggle.addEventListener("click", () => {
        document.body.classList.toggle("oscuro");
    });
</script>
</body>
</html>

<!--pip install gtts PyPDF2 python-docx-->
<!--python -c "from docx import Document; print('python-docx OK')"-->
<!--python -c "from gtts import gTTS; print('gTTS OK')"-->
<!--python -c "from PyPDF2 import PdfReader; print('PyPDF2 OK')"-->
