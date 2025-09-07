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
        <h2>Salas Cadastradas</h2>
        <table>
            <thead>
                <tr>
                    <th>Sala</th>
                    <th>Bloco</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                  
                    $sql = "SELECT n_sala, bloco FROM salas ORDER BY bloco ASC ";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($salas) {
                        foreach ($salas as $sala) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($sala['n_sala']) . "</td>";
                            echo "<td>" . htmlspecialchars($sala['bloco']) . "</td>";
                            
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>Nenhuma sala encontrada.</td></tr>";
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