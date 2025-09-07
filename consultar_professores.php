<?php
session_start();
require "conexao.php"; 



if (!isset($_SESSION["usuario"])) {
    $_SESSION['erro'] = "Você não está logado! Por favor, faça o login.";
    header("Location: login.php");
    exit();
}

include_once("templates/header.php");
?>

<link rel="stylesheet" href="css/style.css">

<main class="form-container">
    <div class="form-card">
        <h2>Professores Cadastrados</h2>
        <table>
            <thead>
                <tr>
                    <th>Professor</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    
                    $sql = "SELECT DISTINCT nome FROM usuarios ORDER BY nome ASC";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $professores = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($professores) {
                        foreach ($professores as $professor) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($professor['nome']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td>Nenhum professor encontrado.</td></tr>";
                    }
                } catch (PDOException $e) {
                    echo "<tr><td>Erro ao carregar dados: " . $e->getMessage() . "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</main>

</html>