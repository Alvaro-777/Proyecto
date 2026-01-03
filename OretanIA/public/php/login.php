<?php
    session_name('oretan-ia');
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

        if(isset($_POST["login-email"])){
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
