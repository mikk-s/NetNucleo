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
        <h2>Aulas Cadastradas</h2>
        <table>
            <thead>
                <tr>
                    <th>Sala</th>
                    <th>Disciplina</th>
                    <th>Professor</th>
                    <th>Horários</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT n_sala,disciplina, professor, data, dia, periodo FROM aulass ORDER BY data DESC, periodo ASC";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $aulas = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($aulas) {
                    foreach ($aulas as $aula) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($aula['n_sala']) . "</td>";
                        echo "<td>" . htmlspecialchars($aula['disciplina']) . "</td>";
                        echo "<td>" . htmlspecialchars($aula['professor']) . "</td>";
                        echo "<td>" . htmlspecialchars($aula['dia']) . ", " . htmlspecialchars($aula['data']) . " - " . htmlspecialchars($aula['periodo']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>Nenhuma aula encontrada.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</main>


</html>