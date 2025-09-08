<?php
session_start();
require "conexao.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome_professor'])) {
    $nome_professor = $_POST['nome_professor'];
    try {
        $sql = "DELETE FROM aulass WHERE professor = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$nome_professor])) {
            $_SESSION['mensagem'] = "Professor '" . htmlspecialchars($nome_professor) . "' e todas as suas aulas foram excluídos com sucesso!";
        } else {
            $_SESSION['mensagem'] = "Erro ao excluir o professor.";
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = "Erro no banco de dados: " . $e->getMessage();
    }
} else {
    $_SESSION['mensagem'] = "Requisição inválida.";
}

// Redireciona de volta para a aba correta
header("Location: excluir.php?aba=professores");
exit();
?>