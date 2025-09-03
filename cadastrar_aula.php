<?php
session_start();
require "conexao.php"; // Inclui o arquivo de conexão com o banco de dados

// Verifica se o usuário está logado
if (!isset($_SESSION["usuario"])) {
    $_SESSION['erro'] = "Você não está logado! Por favor, faça o login.";
    header("Location: login.php");
    exit();
}

$erro_cadastro = "";
$sucesso_cadastro = "";

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $n_sala = $_POST['n_sala'];
    $aula = $_POST['aula']; // No seu formulário, 'Aula' é o campo para o professor
    $data = $_POST['data'];
    $dia = $_POST['dia'];
    $periodo = $_POST['periodo'];

    // Validação básica dos campos
    if (empty($n_sala) || empty($aula) || empty($data) || empty($dia) || empty($periodo)) {
        $erro_cadastro = "Por favor, preencha todos os campos.";
    } else {
        try {
            // Prepara a query SQL para inserção
            $stmt = $conn->prepare("INSERT INTO salas (n_sala, aula, data, dia, periodo) VALUES (:n_sala, :aula, :data, :dia, :periodo)");

            // Bind dos parâmetros
            $stmt->bindParam(':n_sala', $n_sala);
            $stmt->bindParam(':aula', $aula);
            $stmt->bindParam(':data', $data);
            $stmt->bindParam(':dia', $dia);
            $stmt->bindParam(':periodo', $periodo);

            // Executa a query
            $stmt->execute();
            $sucesso_cadastro = "Aula cadastrada com sucesso!";

            // Opcional: Redirecionar para a página de consulta após o cadastro
            // header("Location: consultar_sala.php");
            // exit();

        } catch (PDOException $e) {
            $erro_cadastro = "Erro ao cadastrar aula: " . $e->getMessage();
        }
    }
}

include_once("templates/header.php"); // Inclui o cabeçalho
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Aula</title>
    <link rel="stylesheet" href="css/style.css"> 

</head>
<body>
    <main class="form-container">
        <div class="form-card">
            <h2>Cadastrar Aula</h2>

            <?php if (!empty($erro_cadastro)): ?>
                <p style="color: red;"><?php echo $erro_cadastro; ?></p>
            <?php endif; ?>
            <?php if (!empty($sucesso_cadastro)): ?>
                <p style="color: green;"><?php echo $sucesso_cadastro; ?></p>
            <?php endif; ?>

            <form action="cadastrar_aula.php" method="POST">
                <div class="form-group">
                    <label for="n_sala">Sala:</label>
                    <input type="text" id="n_sala" name="n_sala" required>
                </div>

                <div class="form-group">
                    <label for="aula">Professor:</label>
                    <input type="text" id="aula" name="aula" required>
                </div>

                <div class="form-group">
                    <label for="data">Data:</label>
                    <input type="date" id="data" name="data" required>
                </div>

                <div class="form-group">
                    <label for="dia">Dia da Semana:</label>
                    <select id="dia" name="dia" required>
                        <option value="Segunda-feira">Segunda-feira</option>
                        <option value="Terça-feira">Terça-feira</option>
                        <option value="Quarta-feira">Quarta-feira</option>
                        <option value="Quinta-feira">Quinta-feira</option>
                        <option value="Sexta-feira">Sexta-feira</option>
                        <option value="Sábado">Sábado</option>
                        <option value="Domingo">Domingo</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="periodo">Período:</label>
                    <select id="periodo" name="periodo" required>
                        <option value="Manhã">Manhã</option>
                        <option value="Tarde">Tarde</option>
                        <option value="Noite">Noite</option>
                    </select>
                </div>

                <button type="submit" class="submit-button">Cadastrar Aula</button>
            </form>
        </div>
    </main>
</body>
</html>