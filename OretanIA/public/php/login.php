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
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        if(isset($_POST["login-email"])){
            $email = $_POST["login-email"];
            $password = $_POST["login-pswd"];

            // Validar usuario
            $stmt = $pdo->prepare('SELECT * FROM Usuario WHERE correo = :mail AND contrasea = :pswd');
            $stmt->execute([
                ":mail" => $email,
                ":pswd" => $password
            ]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                setcookie("current_user_id", $user["id"], time() + (86400), "/");

                header("Location: ../");
                exit;
            } else {
                echo "<p style='color:red;'>Usuario o contraseña incorrectos.</p>";
            }
        }

    } catch (PDOException $e) {
        die("Error de conexión con BD: " . $e->getMessage());
    }
