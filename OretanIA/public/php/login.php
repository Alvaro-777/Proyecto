<?php
session_name('oretan-ia');
session_start();

require_once 'conexion.php';

try {
    $pdo = conectarDB();

    if (isset($_POST["login-email"])) {
        $email = $_POST["login-email"];
        $password = $_POST["login-pswd"];

        // Validar usuario
        $stmt = $pdo->prepare('SELECT * FROM Usuario WHERE correo = :mail');
        $stmt->execute([":mail" => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['pswd'])) {
            setcookie("current_user_id", $user["id"], time() + 86400, "/");
            header("Location: ../");
            exit;
        } else {
            $_SESSION['login_error'] = 'Usuario o contraseña incorrectos.';
            header("Location: ../login");
            exit;
        }
    }
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    $_SESSION['login_error'] = 'Error en el sistema. Intente más tarde.';
    header("Location: ../login");
    exit;
}
