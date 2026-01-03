<?php
session_start();

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
    if (!isset($_POST["signup-nombre"], $_POST["signup-apellidos"], $_POST["signup-email"], $_POST["signup-pswd"])) {
        throw new Exception("Faltan datos del formulario.");
    }

    $nombre = trim($_POST["signup-nombre"]);
    $apellidos = trim($_POST["signup-apellidos"]);
    $email = trim($_POST["signup-email"]);
    $password = $_POST["signup-pswd"];
    $confirm = $_POST["signup-confirm"] ?? '';

    if ($password !== $confirm) {
        $_SESSION['error'] = 'Las contraseñas no coinciden.';
        header("Location: ../registro");
        exit;
    }
    echo "Esta conectado a la base de datos";

        $stmt = $pdo->prepare('INSERT INTO Usuario(correo, pswd, nombre, apellido, fecha_registro)
                                                VALUES (:mail, :pswd, :name, :srnm, :date)');
        $resultado = $stmt->execute([
            ":mail" => $email,
            ":pswd" => password_hash($password, PASSWORD_DEFAULT),
            ":name" => $nombre,
            ":srnm" => $apellidos,
            ":date" => date("Y-m-d H:i:s")
        ]);

        if ($resultado) {
            $userId = $pdo->lastInsertId();
            setcookie("current_user_id", $userId, time() + (86400), "/");
            header("Location: ../");
            exit;
        } else {
            $_SESSION['error'] = 'No se pudo guardar el usuario.';
            header("Location: ../registro");
            exit;
        }

} catch (PDOException $e) {
    $_SESSION['error'] = $e->getMessage();
    error_log("Registro error: " . $e->getMessage());
    header("Location: ../registro");
    exit;
}

?>
