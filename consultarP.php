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
            if ($resultado->num_rows > 0) {
                while ($row = $resultado->fetch_assoc()) {
                    ?>
                    <tr>
                        <td><?php echo $row["id"]; ?></td>
                        <td><?php echo $row["nome"]; ?></td>
                        <td><?php echo $row["email"]; ?></td>
                        <td>
                            <button class="btn btn-danger btn-sm btn-excluir" data-id="<?php echo $row["id"]; ?>">Excluir</button>
                        </td>
                    </tr>
                <?php
                }
            } else {
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