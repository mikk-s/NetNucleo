<?php
session_start();

// Verifica se o utilizador está logado
if (!isset($_SESSION["usuario"])) {
    $_SESSION["erro"] = "Você não está logado! Por favor, faça o login.";
    header("location: login.php");
    exit();
}

// Incluir a conexão com o banco de dados
include_once("conexao.php");

// Consulta SQL para buscar os professores
$sql = "SELECT id, nome, email FROM professores";
$resultado = $conn->query($sql);

// Incluir o cabeçalho da página
include_once("templates/header.php");
?>

<div class="container mt-4">
    <h2>Consulta de Professores</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Nome</th>
                <th scope="col">E-mail</th>
                <th scope="col">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Verifica se a consulta retornou resultados
            if ($resultado->num_rows > 0) {
                // Loop para exibir cada linha de professor
                while ($row = $resultado->fetch_assoc()) {
                    // Usando echo para imprimir a linha completa
                    echo "<tr>";
                    echo "<td>" . $row["id"] . "</td>";
                    echo "<td>" . $row["nome"] . "</td>";
                    echo "<td>" . $row["email"] . "</td>";
                    echo "<td><button class='btn btn-danger btn-sm btn-excluir' data-id='" . $row["id"] . "'>Excluir</button></td>";
                    echo "</tr>";
                }
            } else {
                // Se não houver professores no banco de dados
                echo "<tr><td colspan='4'>Nenhum professor encontrado.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php
// Incluir o rodapé
include_once("templates/footer.php");

// Fechar a conexão
$conn->close();
?>