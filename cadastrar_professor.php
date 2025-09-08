
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
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM professores WHERE nome = ? AND unidade_curricular = ?");
            $check_stmt->execute([$nome_professor, $unidade_curricular]);
            $count = $check_stmt->fetchColumn();

            if ($count > 0) {
                echo "<script>alert('Erro: Professor com este nome já cadastrado.');</script>";
            } else {
                $stmt = $conn->prepare("INSERT INTO professores (nome, unidade_curricular) VALUES (?, ?)");
                $stmt->execute([$nome_professor, $unidade_curricular]);
                echo "<script>alert('Professor cadastrado com sucesso!');</script>";
            }
        }
    }
    catch (PDOException $e) {
        echo "<script>alert('Erro ao cadastrar professor: " . $e->getMessage() . "');</script>";
    }
   
}
    ?>
<div class="form-container">
    
    <!-- Formulário de Professor -->
    <div class="form-card">
        <h2>Cadastrar Professor</h2>
        <form method="POST">           
                <label for="nome_professor">Nome do Professor:</label>
                <input type="text" id="nome_professor" name="nome_professor" required>
                <label for="unidade_curricular">Unidade Curricular:</label>
                <select id="unidade_curricular" name="unidade_curricular" required>
                    <option value="">Selecione uma Unidade Curricular</option>
                    <?php
                    $sql_unicurri = "SELECT DISTINCT unicurri FROM turmas ORDER BY unicurri ASC";
                    $resultado_unicurri = $conn->query($sql_unicurri);
                    if ($resultado_unicurri->rowCount() > 0) {
                        while($unicurri = $resultado_unicurri->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='" . htmlspecialchars($unicurri['unicurri']) . "'>" . htmlspecialchars($unicurri['unicurri']) . "</option>";
                        }
                    } else {
                        echo "<option value=''>Nenhuma Unidade Curricular cadastrada em turmas</option>";
                    }
                    ?>
                </select>
            <button type="submit" class="submit-button">Cadastrar Professor</button>
        </form>
    </div>
