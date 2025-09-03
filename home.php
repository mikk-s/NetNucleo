<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    $_SESSION['erro'] = "Você não está logado! Por favor, faça o login.";
    header("Location: login.php");
    exit();
}

include_once("templates/header.php");
?>

<link rel="stylesheet" href="css/style.css">

<p>Seja bem vindo. Para começar, visualize as turmas existentes em Consultas>Turmas</p>

</html>