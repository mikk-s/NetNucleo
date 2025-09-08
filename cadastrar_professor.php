
<?php
session_start();
require "conexao.php";

include_once("templates/header.php");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_professor'])) {
            $stmt = $pdo->prepare("INSERT INTO professores (nome, unidade_curricular) VALUES (?, ?)");
            $stmt->execute([$_POST['nome_professor'], $_POST['unidade_curricular']]);
            $message = "<div class='message success'>Professor cadastrado com sucesso!</div>";
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
        <form action="cadastros.php" method="POST">
    
                <label for="nome_professor">Nome do Professor:</label>
                <input type="text" id="nome_professor" name="nome_professor" required>
                <label for="unidade_curricular">Unidade Curricular Principal:</label>
                <input type="text" id="unidade_curricular" name="unidade_curricular" required>
            <button type="submit" class="submit-button">Cadastrar Professor</button>
        </form>
    </div>
