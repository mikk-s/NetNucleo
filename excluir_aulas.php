<?php
session_start();
require "conexao.php";

if (!isset($_SESSION["usuario"])) {
    $_SESSION['erro'] = "Você não está logado! Por favor, faça o login.";
    header("Location: login.php");
    exit();
}

// Lógica de exclusão
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['excluir_aula_id'])) {
    $aula_id = $_POST['excluir_aula_id'];

    try {
        $sql_delete = "DELETE FROM aulass WHERE id = :id";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bindParam(':id', $aula_id);
        $stmt_delete->execute();
        echo "<script>alert('Aula excluída com sucesso!'); window.location.href='excluir_aulas.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Erro ao excluir aula: " . $e->getMessage() . "');</script>";
    }
}

include_once("templates/header.php");
?>

<link rel="stylesheet" href="css/style.css">

<main class="form-container">
    <div class="form-card">
        <h2>Excluir Aulas</h2>
        <table>
            <thead>
                <tr>
                    <th>Sala</th>
                    <th>Disciplina</th>
                    <th>Professor</th>
                    <th>Horários</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $sql_select = "SELECT id, n_sala, disciplina, professor, data, dia, periodo FROM aulass ORDER BY data DESC, periodo ASC";
                    $stmt_select = $conn->prepare($sql_select);
                    $stmt_select->execute();
                    $aulas = $stmt_select->fetchAll(PDO::FETCH_ASSOC);

                    if ($aulas) {
                        foreach ($aulas as $aula) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($aula['n_sala']) . "</td>";
                            echo "<td>" . htmlspecialchars($aula['disciplina']) . "</td>";
                            echo "<td>" . htmlspecialchars($aula['professor']) . "</td>";
                            echo "<td>" . htmlspecialchars($aula['dia']) . ", " . htmlspecialchars($aula['data']) . " - " . htmlspecialchars($aula['periodo']) . "</td>";
                            echo "<td>";
                            echo "<form method='post' action='excluir_aulas.php' onsubmit=\"return confirm('Tem certeza que deseja excluir esta aula?');\">";
                            echo "<input type='hidden' name='excluir_aula_id' value='" . $aula['id'] . "'>";
                            echo "<button type='submit' class='excluir-btn'>Excluir</button>";
                            echo "</form>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>Nenhuma aula encontrada.</td></tr>";
                    }
                } catch (PDOException $e) {
                    echo "<tr><td colspan='5'>Erro ao carregar dados: " . $e->getMessage() . "</td></tr>";
                }
                ?>
                
            </tbody>
        </table>
    </div>
</main>
</html>