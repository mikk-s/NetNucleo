<?php
session_start();
require "conexao.php";

if (!isset($_SESSION["usuario"])) {
    $_SESSION['erro'] = "Você não está logado! Por favor, faça o login.";
    header("Location: login.php");
    exit();
}

// ---- LÓGICA PARA PROCESSAR AS AÇÕES DO FORMULÁRIO ----

// AÇÃO 1: Excluir APENAS AS AULAS de um professor
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['excluir_aulas_do_professor'])) {
    $nome_professor = $_POST['excluir_aulas_do_professor'];
    try {
        $sql_delete_aulas = "DELETE FROM aulass WHERE professor = :nome_professor";
        $stmt_delete_aulas = $conn->prepare($sql_delete_aulas);
        $stmt_delete_aulas->bindParam(':nome_professor', $nome_professor);
        $stmt_delete_aulas->execute();
        $num_aulas_excluidas = $stmt_delete_aulas->rowCount();
        echo "<script>alert('" . $num_aulas_excluidas . " aula(s) do professor " . htmlspecialchars($nome_professor) . " foram excluídas com sucesso!'); window.location.href='excluir_professores.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Erro ao excluir as aulas: " . addslashes($e->getMessage()) . "'); window.location.href='excluir_professores.php';</script>";
    }
    exit();
}

// AÇÃO 2: Excluir O PROFESSOR (apenas se ele não tiver aulas)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['excluir_professor_nome'])) {
    $nome_a_excluir = $_POST['excluir_professor_nome'];
    try {
        // Primeiro, verifica se o professor ainda tem aulas
        $sql_check = "SELECT COUNT(*) FROM aulass WHERE professor = :nome_a_excluir";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bindParam(':nome_a_excluir', $nome_a_excluir);
        $stmt_check->execute();
        $numero_de_aulas = $stmt_check->fetchColumn();

        if ($numero_de_aulas > 0) {
            // Se tiver aulas, impede a exclusão do professor
            echo "<script>alert('ERRO: Este professor não pode ser excluído pois ainda possui aulas cadastradas. Por favor, exclua as aulas dele primeiro.'); window.location.href='excluir_professores.php';</script>";
        } else {
            // Se não tiver aulas, exclui o professor da tabela 'usuarios'
            $sql_delete_prof = "DELETE FROM usuarios WHERE nome = :nome_a_excluir";
            $stmt_delete_prof = $conn->prepare($sql_delete_prof);
            $stmt_delete_prof->bindParam(':nome_a_excluir', $nome_a_excluir);
            $stmt_delete_prof->execute();
            echo "<script>alert('Professor " . htmlspecialchars($nome_a_excluir) . " foi excluído com sucesso!'); window.location.href='excluir_professores.php';</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Erro ao processar a exclusão: " . addslashes($e->getMessage()) . "'); window.location.href='excluir_professores.php';</script>";
    }
    exit();
}

include_once("templates/header.php");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Professores</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Estilo para alinhar os botões na mesma linha */
        .acoes-form {
            display: inline-block;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <main class="form-container">
        <div class="form-card">
            <h2>Gerenciar Professores</h2>
            <table>
                <thead>
                    <tr>
                        <th>Professor</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $sql_prof = "SELECT nome FROM usuarios ORDER BY nome ASC";
                        $stmt_prof = $conn->prepare($sql_prof);
                        $stmt_prof->execute();
                        $professores = $stmt_prof->fetchAll(PDO::FETCH_ASSOC);

                        if ($professores) {
                            foreach ($professores as $professor) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($professor['nome']) . "</td>";
                                echo "<td>";
                                
                              
                                echo "<form method='post'class='acoes-form' onsubmit=\"return confirm('Tem certeza que deseja excluir TODAS AS AULAS do professor " . htmlspecialchars($professor['nome']) . "?');\">";
                                echo "<input type='hidden' name='excluir_aulas_do_professor' value='" . htmlspecialchars($professor['nome']) . "'>";
                                echo "<button type='submit' class='excluir-btn'>Excluir Aulas</button>";
                                echo "</form>";
                            
                                echo "<form method='post' class='acoes-form' onsubmit=\"return confirm('Tem certeza que deseja excluir o PROFESSOR " . htmlspecialchars($professor['nome']) . "? Esta ação só funcionará se ele não tiver aulas associadas.');\">";
                                echo "<input type='hidden' name='excluir_professor_nome' value='" . htmlspecialchars($professor['nome']) . "'>";
                                echo "<button type='submit' class='excluir-btn'>Excluir Professor</button>";
                                echo "</form>";
                                
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='2'>Nenhum professor encontrado.</td></tr>";
                        }
                    } catch (PDOException $e) {
                        echo "<tr><td colspan='2'>Erro ao carregar dados: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>