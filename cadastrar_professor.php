
<?php
session_start();
require "conexao.php";

include_once("templates/header.php");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['nome_professor'])) {
            $nome_professor = $_POST['nome_professor'];
            $unidade_curricular = $_POST['unidade_curricular'];

            // Check if professor already exists
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM professores WHERE nome = ?");
            $check_stmt->execute([$nome_professor]);
            $count = $check_stmt->fetchColumn();

            if ($count > 0) {
                $message = "<div class='message error'>Erro: Professor com este nome já cadastrado.</div>";
            } else {
                $stmt = $conn->prepare("INSERT INTO professores (nome, unidade_curricular) VALUES (?, ?)");
                $stmt->execute([$nome_professor, $unidade_curricular]);
                $message = "<div class='message success'>Professor cadastrado com sucesso!</div>";
            }
        }
    }
    catch (PDOException $e) {
        $message = "<div class='message error'>Erro ao cadastrar professor: " . $e->getMessage() . "</div>";
    }
   
}
    ?>
<div class="form-container">
    
    <!-- Formulário de Professor -->
    <div class="form-card">
        <h2>Cadastrar Professor</h2>
        <form method="POST">
            <?php if (isset($message)) echo $message; ?>
                <label for="nome_professor">Nome do Professor:</label>
                <input type="text" id="nome_professor" name="nome_professor" required>
                <label for="unidade_curricular">Unidade Curricular Principal:</label>
                <input type="text" id="unidade_curricular" name="unidade_curricular" required>
            <button type="submit" class="submit-button">Cadastrar Professor</button>
        </form>
    </div>
