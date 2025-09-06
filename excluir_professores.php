<?php
session_start();
require "conexao.php";

if (!isset($_SESSION["usuario"])) {
    $_SESSION['erro'] = "Você não está logado! Por favor, faça o login.";
    header("Location: login.php");
    exit();
}

// Lógica de exclusão por nome do professor
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['excluir_professor_nome'])) {
    $nome_a_excluir = $_POST['excluir_professor_nome'];

    try {
        // Exclui todas as aulas que têm o mesmo nome de professor
        $sql_delete = "DELETE FROM salas WHERE professor = :nome_a_excluir";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bindParam(':nome_a_excluir', $nome_a_excluir);
        $stmt_delete->execute();
        echo "<script>alert('Aulas do professor " . htmlspecialchars($nome_a_excluir) . " foram excluídas com sucesso!'); window.location.href='excluir_professores.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Erro ao excluir aulas: " . $e->getMessage() . "');</script>";
    }
}

include_once("templates/header.php");
?>

<link rel="stylesheet" href="css/style.css">

<main class="form-container">
    <div class="form-card">
        <h2>Excluir Professores</h2>
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
                    // Seleciona todos os nomes de professores da tabela de usuários
                    $sql = "SELECT DISTINCT nome FROM usuarios ORDER BY nome ASC";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $professores = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($professores) {
                        foreach ($professores as $professor) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($professor['nome']) . "</td>";
                            echo "<td>";
                            echo "<form method='post' action='excluir_professores.php' onsubmit=\"return confirm('Tem certeza que deseja excluir as aulas deste professor?');\">";
                            echo "<input type='hidden' name='excluir_professor_nome' value='" . $professor['nome'] . "'>";
                            echo "<button type='submit' class='excluir-btn'>Excluir</button>";
                            echo "</form>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='2'>Nenhum professor encontrado.</td></tr>";
                    }
                } catch (PDOException $e) {
                    echo "<tr><td colspan='2'>Erro ao carregar dados: " . $e->getMessage() . "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</main>
</html>