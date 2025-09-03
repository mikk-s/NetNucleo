
<?php 
session_start();
ob_start();

require "conexao.php";
if (isset($_SESSION["usuario"])) {
    $_SESSION['login_error'] = "Já logou. Redirecionando...";
    header("Location: home.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login = $_POST["login"];
    $senha = $_POST["senha"];

    $sql = "SELECT * FROM usuarios WHERE login = :login";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":login", $login);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        if (password_verify($senha, $usuario["senha"])) {
            $_SESSION["usuario"] = $usuario['login'];
            header("Location: home.php");
            exit();
        } else {
            $_SESSION["erro"] = "Senha incorreta.";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION["erro"] = "Usuário não encontrado.";
        header("Location: login.php");
        exit();
    }
}


?>
<link rel="stylesheet" href="css/style.css">



<body class="login">

 
    <div class="login">
        <form method="POST" class="login-form">
        <?php
if (isset($_SESSION["erro"])) {
    echo "<p class='aviso-erro'>{$_SESSION["erro"]}</p>";
    unset($_SESSION["erro"]);
}
?>

            <input type="text" name="login" placeholder="Login" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <input type="submit" value="Login">
        </form>    
        <p>Não tem uma conta? <a href="cadastro.php">Cadastre-se</a></p>
    </div>
    
    
</body>

