<?php
    if(isset($_POST["signup-fullname"])){
        $nombre = $_POST["signup-fullname"];
        $email = $_POST["signup-email"];
        $password = $_POST["signup-pswd"];

        echo $nombre;
    }
