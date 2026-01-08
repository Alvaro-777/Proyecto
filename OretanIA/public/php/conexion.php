<?php

function obtener_ip_real()
{
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

function conectarDB()
{
    $host = '127.0.0.1';
    $port = 33100;
    $dbname = 'oretan-ia';
    $user = 'root';
    $pass = 'root';

    try {
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    } catch (PDOException $e) {
        abort_with_message('Error de conexión a la base de datos.');
        exit;
    }
    return $pdo;
}

function gestionArchivos($usuario, $nombre, $tam, $tipo, $ruta)
{
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare('INSERT INTO Archivo(usuario_id, nombre, peso, tipo)
                                    VALUES (:usuario, :name, :tam, :type)');
        $resultado = $stmt->execute([
            ":usuario" => $usuario,
            ":name" => $nombre,
            ":tam" => $tam,
            ":type" => $tipo
        ]);
        $archivo_id = $pdo->lastInsertId();
        $texto_input_historial = "Información introducida por archivo (" . $_FILES['archivo_adjunto']['name'] . ")";
    } catch (PDOException $e) {
        unlink($ruta);
        abort_with_message('Error al guardar el archivo.');
        exit;
    }
}

function historial($texto, $ia, $archivo, $usuario)
{
    try {


        $pdo = conectarDB();
        $stmt_historial = $pdo->prepare(
            'INSERT INTO HistorialUsoIA (texto_input, ip_usuario, ia_id, archivo_id, usuario_id)
             VALUES (:texto_input, :ip_usuario, :ia_id, :archivo_id, :usuario_id)'
        );
        $stmt_historial->execute([
            ':texto_input' => $texto,
            ':ip_usuario' => obtener_ip_real(),
            ':ia_id' => $ia,
            ':archivo_id' => $archivo,
            ':usuario_id' => $usuario
        ]);

    } catch
    (PDOException $e) {
        // Opcional: loggear el error, pero no abortar (el audio es más importante)
        abort_with_message('Error al guardar en historial: ' . $e->getMessage());

    }
}
