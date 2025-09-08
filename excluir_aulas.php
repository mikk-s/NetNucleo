<?php
session_start();
require "conexao.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_aula'])) {
    $id_aula = $_POST['id_aula'];
    try {
        $sql = "DELETE FROM aulass WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$id_aula])) {
            $_SESSION['mensagem'] = "Aula excluída com sucesso!";
        } else {
            $_SESSION['mensagem'] = "Erro ao excluir a aula.";
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = "Erro no banco de dados: " . $e->getMessage();
    }
} else {
    $_SESSION['mensagem'] = "Requisição inválida.";
}

// Redireciona de volta para a aba de aulas
header("Location: excluir.php?aba=aulas");
exit();
?>