<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    $_SESSION['login_error'] = "Você não está logado! Por favor, faça o login.";
    header("Location: index.php");
    exit();
}

include_once("templates/header.php");
?>

<link rel="stylesheet" href="css/style.css">



</html>