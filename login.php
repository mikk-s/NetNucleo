
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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login = $_POST["login"];
    $senha = $_POST["senha"];

    // Verifica se usuário já existe
    $checkSql = "SELECT COUNT(*) FROM usuarios WHERE login = :login";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(":login", $login);
    $checkStmt->execute();
    $userExists = $checkStmt->fetchColumn();

    if ($userExists > 0) {
        $_SESSION["erro"] = "Usuário já existe. Por favor, escolha outro nome de usuário.";
        header("Location: login.php");
        exit();
    }

    
    $senhaCriptografada = password_hash($senha, PASSWORD_DEFAULT);

    
    $sql = "INSERT INTO usuarios (login, senha) VALUES (:login, :senha)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":login", $login);
    $stmt->bindParam(":senha", $senhaCriptografada);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        $_SESSION["erro"] = "Erro ao realizar cadastro.";
        header("Location: login.php");
        exit();
    }
}

// Exibe mensagem de erro se existir
// num apague meu php 
if (isset($_SESSION["erro"])) {
    echo "<p class='aviso-erro'>{$_SESSION["erro"]}</p>";
    unset($_SESSION["erro"]);
    
}
?>
<!DOCTYPE html>
<html lang="en" >
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Signup Form</title>
    <link rel="stylesheet" href="css/style.css">

</head>
<body class="login">
    <div class="container">
        <div class="curved-shape"></div>
        <div class="curved-shape2"></div>
        <div class="form-box Login">
            <h2 class="animation" style="--D:0; --S:21">Login</h2>
            <form method="post">
                <div class="input-box animation" style="--D:1; --S:22">
                    <input name="login" type="text" required>
                    <label for="">Login</label>
                    <box-icon type='solid' name='user' color="gray"></box-icon>
                </div>

                <div class="input-box animation" style="--D:2; --S:23">
                    <input name="senha" type="password" required>
                    <label for="">Senha</label>
                    <box-icon name='lock-alt' type='solid' color="gray"></box-icon>
                </div>

                <div class="input-box animation" style="--D:3; --S:24">
                    <button class="btn" type="submit">Login</button>
                </div>

                <div class="regi-link animation" style="--D:4; --S:25">
                    <p>Don't have an account? <br> <a href="#" class="SignUpLink">Sign Up</a></p>
                </div>
            </form>
        </div>

        <div class="info-content Login">
            <h2 class="animation" style="--D:0; --S:20">WELCOME BACK!</h2>
            <p class="animation" style="--D:1; --S:21">We are happy to have you with us again. If you need anything, we are here to help.</p>
        </div>

        <div class="form-box Register">
            <h2 class="animation" style="--li:17; --S:0">Register</h2>
            <form method="post">
                <div class="input-box animation" style="--li:18; --S:1">
                    <input name="login" type="text" required>
                    <label for="">Login</label>
                    <box-icon type='solid' name='user' color="gray"></box-icon>
                </div>

           
                <div class="input-box animation" style="--li:19; --S:3">
                    <input name="senha" type="password" required>
                    <label for="">Senha</label>
                    <box-icon name='lock-alt' type='solid' color="gray"></box-icon>
                </div>

                <div class="input-box animation" style="--li:20; --S:4">
                    <button class="btn" type="submit">Registrar</button>
                </div>

                <div class="regi-link animation" style="--li:21; --S:5">
                    <p>Don't have an account? <br> <a href="#" class="SignInLink">Sign In</a></p>
                </div>
            </form>
        </div>

        <div class="info-content Register">
            <h2 class="animation" style="--li:17; --S:0">WELCOME!</h2>
            <p class="animation" style="--li:18; --S:1">We’re delighted to have you here. If you need any assistance, feel free to reach out.</p>
        </div>

    </div>

    <script src="index.js"></script>
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>

</body>
</html>
  <script  src="    script.js"></script>

