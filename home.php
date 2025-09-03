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

<body>

<iframe width="560" height="315" src="https://www.youtube.com/embed/84QqDEG3PQk?si=m1TBDH7Il-WBikoP" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
    <iframe width="300" height="515" src="https://www.youtube.com/embed/CEftqKUi7BA?si=CP3bbVPxcX3a3xDO" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
</body>
</html>