<?php
    $host = '127.0.0.1';
    $port = 33100;
    $dbname = 'oretan-ia';
    $user = 'root';
    $pass = 'root';

    try {
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        echo "Esta conectado a la base de datos";
        if(isset($_POST["signup-fullname"])){
            $nombre = $_POST["signup-fullname"];
            $email = $_POST["signup-email"];
            $password = $_POST["signup-pswd"];

            $stmt = $pdo->prepare('INSERT INTO Usuario(correo, contrasea, nombre, apellido, creditos, fecha_registro)
                                                VALUES (:mail, :pswd, :name, :srnm, :crdt, :date)');
            $resultado = $stmt->execute([
                ":mail" => $email,
                ":pswd" => $password,
                ":name" => substr($nombre, strpos($nombre, ",") + 1),
                ":srnm" => substr($nombre, 0, strpos($nombre, ",")),
                ":crdt" => 0,
                ":date" => date("Y-m-d H:i:s")
            ]);

            if($resultado) {
                setcookie("current_user_id", $user["id"], time() + (86400), "/");

                header("Location: ../");
                exit;
            } else {
                //Ahora mismo no se como pasar la informacion del error de vuelta a /registro
                //$_POST['error'] = 'No se pudo guardar el usuario';

                header("Location: ../registro");
                exit;
            }
        }
    } catch (PDOException $e) {
        $_POST['error'] = 'No se pudo guardar el usuario';

        header("Location: ../registro");
        exit;
        //die("Error de conexión con BD: " . $e->getMessage());
    }


