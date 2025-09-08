<?php
session_start();
require "conexao.php";

// 1. VERIFICAÇÕES DE SEGURANÇA BÁSICAS
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_sala'])) {
    $_SESSION['mensagem'] = "Requisição inválida.";
    $_SESSION['mensagem_tipo'] = "erro";
    header("Location: excluir.php?aba=turmas");
    exit();
}

$id_sala = $_POST['id_sala'];

try {
    // 2. BUSCA O NOME DA SALA ANTES DE DELETAR (PARA VERIFICAR O USO)
    $stmt_sala = $conn->prepare("SELECT n_sala FROM salas WHERE id = ?");
    $stmt_sala->execute([$id_sala]);
    $sala = $stmt_sala->fetch(PDO::FETCH_ASSOC);

    if (!$sala) {
        throw new Exception("Sala não encontrada.");
    }
    $nome_da_sala = $sala['n_sala'];

    // 3. VERIFICAÇÃO DE SEGURANÇA: A SALA ESTÁ SENDO USADA EM ALGUMA AULA?
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM aulass WHERE n_sala = ?");
    $stmt_check->execute([$nome_da_sala]);
    $count = $stmt_check->fetchColumn();

    if ($count > 0) {
        // Se count for maior que 0, a sala está em uso! IMPEDE A EXCLUSÃO.
        $_SESSION['mensagem'] = "Não é possível excluir a turma/sala '" . htmlspecialchars($nome_da_sala) . "' pois ela já está associada a " . $count . " aula(s).";
        $_SESSION['mensagem_tipo'] = "erro";
    } else {
        // 4. SE NÃO ESTIVER EM USO, EXECUTA A EXCLUSÃO
        $stmt_delete = $conn->prepare("DELETE FROM salas WHERE id = ?");
        if ($stmt_delete->execute([$id_sala])) {
            $_SESSION['mensagem'] = "Turma/Sala '" . htmlspecialchars($nome_da_sala) . "' excluída com sucesso!";
        } else {
            $_SESSION['mensagem'] = "Erro ao excluir a turma/sala.";
            $_SESSION['mensagem_tipo'] = "erro";
        }
    }

} catch (Exception $e) {
    $_SESSION['mensagem'] = "Erro: " . $e->getMessage();
    $_SESSION['mensagem_tipo'] = "erro";
}

// 5. REDIRECIONA DE VOLTA PARA A ABA CORRETA
header("Location: excluir.php?aba=turmas");
exit();

?>