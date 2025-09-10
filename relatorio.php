<?php
session_start();
require "conexao.php"; // Este arquivo deve criar a variável $conn = new PDO(...);

// Verifica se o usuário está logado
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

include_once("templates/header.php");

// --- LÓGICA PARA BUSCAR AS ESTATÍSTICAS ---
$stats = [
    'turmas_total' => 0,
    'turmas_livres' => 0,      // Novo
    'professores_total' => 0,
    'professores_livres' => 0,
    'salas_total' => 0,
    'salas_livres' => 0,
    'aulas_total' => 0,
    'aulas_hoje' => 0         // Novo
];
$erro_relatorio = '';

try {
    $hoje = date('Y-m-d'); // Data de hoje

    // Estatísticas de Turmas
    $stats['turmas_total'] = $conn->query("SELECT COUNT(*) FROM turmas")->fetchColumn();
    $stats['turmas_livres'] = $conn->query(
        "SELECT COUNT(*) FROM turmas 
         WHERE id NOT IN (SELECT DISTINCT turma_id FROM aulas)"
    )->fetchColumn();

    // Estatísticas de Aulas
    $stats['aulas_total'] = $conn->query("SELECT COUNT(*) FROM aulas")->fetchColumn();
    $stmt_aulas_hoje = $conn->prepare("SELECT COUNT(*) FROM aulas WHERE data_aula = ?");
    $stmt_aulas_hoje->execute([$hoje]);
    $stats['aulas_hoje'] = $stmt_aulas_hoje->fetchColumn();

    // Estatísticas de Professores (continua igual)
    $stats['professores_total'] = $conn->query("SELECT COUNT(*) FROM professores")->fetchColumn();
    $stats['professores_livres'] = $conn->query(
        "SELECT COUNT(*) FROM professores 
         WHERE id NOT IN (SELECT DISTINCT professor_id FROM aulas)"
    )->fetchColumn();

    // Estatísticas de Salas (continua igual)
    $stats['salas_total'] = $conn->query("SELECT COUNT(*) FROM salas")->fetchColumn();
    $stats['salas_livres'] = $conn->query(
        "SELECT COUNT(*) FROM salas 
         WHERE id NOT IN (SELECT DISTINCT sala_id FROM aulas)"
    )->fetchColumn();

} catch (PDOException $e) {
    $erro_relatorio = "Erro ao carregar as estatísticas do sistema: " . $e->getMessage();
}
?>
<link rel="stylesheet" href="<?= $BASE_URL?>css/style.css">

<main class="canvas-container">
    <div class="report-card" ">
        <h2>Relatório Geral do Sistema</h2>

        <?php if ($erro_relatorio): ?>
            <p class="message error"><?= htmlspecialchars($erro_relatorio); ?></p>
        <?php else: ?>
            <div class="form-container" style="gap: 1rem; ">
                
            <div class="form-card" style="min-height:189px ;">
    <div class="stat-icon">📚</div>
    <div class="stat-label" style="margin-bottom: 0.5rem;">Turmas</div>
    <div class="stat-details">
        <p><strong><?= $stats['turmas_total'] ?></strong> Cadastradas</p>
        <p><strong><?= $stats['turmas_livres'] ?></strong> Sem Aulas</p>
    </div>
</div>

                <div class="form-card" style="min-height:189px ;">
                    <div class="stat-icon">👨‍🏫</div>
                    <div class="stat-label" style="margin-bottom: 0.5rem;">Professores</div>
                    <div class="stat-details">
                        <p><strong><?= $stats['professores_total'] ?></strong> Ativos</p>
                        <p><strong><?= $stats['professores_livres'] ?></strong> Livres </p>
                    </div>
                </div>

                <div class="form-card" style="min-height:189px ;">
                    <div class="stat-icon">🚪</div>
                    <div class="stat-label" style="margin-bottom: 0.5rem;">Salas</div>
                     <div class="stat-details">
                        <p><strong><?= $stats['salas_total'] ?></strong> Disponíveis</p>
                        <p><strong><?= $stats['salas_livres'] ?></strong> Livres </p>
                    </div>
                </div>
                
              <div class="form-card" style="min-height:189px ;">
    <div class="stat-icon">🗓️</div>
    <div class="stat-label" style="margin-bottom: 0.5rem;">Aulas</div>
    <div class="stat-details">
        <p><strong><?= $stats['aulas_total'] ?></strong> Agendadas (Total)</p>
        <p><strong><?= $stats['aulas_hoje'] ?></strong> Agendadas para Hoje</p>
    </div>
</div>

            </div>
        <?php endif; ?>
    </div>
</main>

