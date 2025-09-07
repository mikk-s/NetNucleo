<?php
session_start();
require "conexao.php";

if (!isset($_SESSION["usuario"])) {
    $_SESSION['erro'] = "Você não está logado! Por favor, faça o login.";
    header("Location: login.php");
    exit();
}

// Lógica de exclusão
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['excluir_sala_id'])) {
    $sala_id = $_POST['excluir_sala_id'];

    try {
        $sql_delete = "DELETE FROM salas WHERE id = :id";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bindParam(':id', $sala_id);
        $stmt_delete->execute();
        echo "<script>alert('Sala excluída com sucesso!'); window.location.href='excluir_salas.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Erro ao excluir sala: " . $e->getMessage() . "');</script>";
    }
}

include_once("templates/header.php");
?>

<link rel="stylesheet" href="css/style.css">

<main class="form-container">
    <div class="form-card">
        <h2>Excluir Salas</h2>
        <table>
            <thead>
                <tr>
                    <th>Sala</th>
                    <th>Bloco</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $sql_select = "SELECT DISTINCT n_sala,bloco, id FROM salas ORDER BY n_sala ASC";
                    $stmt_select = $conn->prepare($sql_select);
                    $stmt_select->execute();
                    $salas = $stmt_select->fetchAll(PDO::FETCH_ASSOC);

                    if ($salas) {
                        foreach ($salas as $sala) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($sala['n_sala']) . "</td>"; 
                            echo "<td>" . htmlspecialchars($sala['bloco']) . "</td>"; 
                            echo "<td>";
                            echo "<form method='post' action='excluir_salas.php' onsubmit=\"return confirm('Tem certeza que deseja excluir esta sala?');\">";
                            echo "<input type='hidden' name='excluir_sala_id' value='" . $sala['id'] . "'>";
                            echo "<button type='submit' class='excluir-btn'>Excluir</button>";
                            echo "</form>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='2'>Nenhuma sala encontrada.</td></tr>";
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