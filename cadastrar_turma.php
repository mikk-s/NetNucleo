
<?php
session_start();
require "conexao.php";

include_once("templates/header.php");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['nome_turma'])) {
            $nome_turma = $_POST['nome_turma'];

            // Check if turma already exists
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM turmas WHERE nome_turma = ?");
            $check_stmt->execute([$nome_turma]);
            $count = $check_stmt->fetchColumn();

            if ($count > 0) {
                $message = "<div class='message error'>Erro: Turma com este nome já cadastrada.</div>";
            } else {
                $stmt = $conn->prepare("INSERT INTO turmas (nome_turma) VALUES (?)");
                $stmt->execute([$nome_turma]);
                $message = "<div class='message success'>Turma cadastrada com sucesso!</div>";
            }
        }
    } catch (PDOException $e) {
        $message = "<div class='message error'>Erro ao cadastrar turma: " . $e->getMessage() . "</div>";
    }
}
    ?>
<div class="form-container">
    <!-- Formulário de Turma -->
    <div class="form-card">
        <h2>Cadastrar Turma</h2>
        <form method="POST">
            <?php if (isset($message)) echo $message; ?>
                <label for="nome_turma">Nome da Turma:</label>
                <input type="text" id="nome_turma" name="nome_turma" required>
            <button type="submit" class="submit-button">Cadastrar Turma</button>
        </form>
    </div>
