<?php
    if(isset($_POST["login-email"])){
        $email = $_POST["login-email"];
        $password = $_POST["login-pswd"];

        echo $email;
    }
