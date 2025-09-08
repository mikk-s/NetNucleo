
<?php
session_start();
require "conexao.php";

include_once("templates/header.php");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['sigla_curso'], $_POST['ano_ingresso'], $_POST['semestre_ingresso'], $_POST['unicurri'])) {
          
            // Combine sigla_curso, ano_ingresso, e semestre_ingresso para formar nome_turma
            $sigla_curso = $_POST['sigla_curso'];
            $ano_ingresso = $_POST['ano_ingresso'];
            $semestre_ingresso = $_POST['semestre_ingresso'];
            $nome_turma = $sigla_curso . '-' . $ano_ingresso . '-' . $semestre_ingresso;

            // Check if turma already exists
            $unicurri = $_POST['unicurri'];
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM turmas WHERE nome_turma = :nome_turma AND unicurri = :unicurri");
            $check_stmt->bindParam(':nome_turma', $nome_turma);
            $check_stmt->bindParam(':unicurri', $unicurri);
            $check_stmt->execute();
            
            $count = $check_stmt->fetchColumn();

            if ($count > 0) {
                $message = "<div class='message error'>Erro: Turma com este nome já cadastrada.</div>";
            } else {
                $stmt = $conn->prepare("INSERT INTO turmas (nome_turma, unicurri) VALUES (:nome_turma, :unicurri)");
                $stmt->bindParam(':nome_turma', $nome_turma);
                $stmt->bindParam(':unicurri', $unicurri);
                if ($stmt->execute()) {
                    $message = "<div class='message success'>Turma cadastrada com sucesso!</div>";
                } else {
                    $message = "<div class='message error'>Erro ao cadastrar turma.</div>";
                }
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
                <label for="sigla_curso">Sigla do Curso:</label>
                <input type="text" id="sigla_curso" name="sigla_curso" placeholder="Ex: TADS" required>

                <label for="ano_ingresso">Ano de Ingresso:</label>
                <input type="number" id="ano_ingresso" name="ano_ingresso" placeholder="Ex: 2025" required>
 
                <label for="semestre_ingresso">Semestre de Ingresso:</label>
                <input type="number" id="semestre_ingresso" name="semestre_ingresso" min="1" max="2" placeholder="Ex: 1" required>
                <label for="unicurri">Unidade Curricular:</label>
                <input type="text" id="unicurri" name="unicurri" required>
            <button type="submit" class="submit-button">Cadastrar Turma</button>
        </form>
    </div>
