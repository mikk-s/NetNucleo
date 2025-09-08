<?php
session_start();
require "conexao.php"; // Este arquivo deve criar a variável $conn = new PDO(...);

// Verifica se o usuário está logado
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

// --- PROCESSAMENTO DE EXCLUSÕES (AÇÕES POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tabela = '';
    $id_para_excluir = null;
    $aba_retorno = $_POST['aba_retorno'] ?? 'aulas'; // Padrão para aulas
    $nome_campo_id = '';

    // Determina qual tipo de item está sendo excluído
    if (isset($_POST['excluir_aula_id'])) {
        $tabela = 'aulas';
        $nome_campo_id = 'id';
        $id_para_excluir = $_POST['excluir_aula_id'];
    } elseif (isset($_POST['excluir_professor_id'])) {
        $tabela = 'professores';
        $nome_campo_id = 'id';
        $id_para_excluir = $_POST['excluir_professor_id'];
    } elseif (isset($_POST['excluir_turma_id'])) {
        $tabela = 'turmas';
        $nome_campo_id = 'id';
        $id_para_excluir = $_POST['excluir_turma_id'];
    } elseif (isset($_POST['excluir_sala_id'])) {
        $tabela = 'salas';
        $nome_campo_id = 'id';
        $id_para_excluir = $_POST['excluir_sala_id'];
    }

    if ($tabela && $id_para_excluir) {
        try {
            // A cláusula ON DELETE CASCADE no banco cuidará de remover aulas associadas
            $stmt = $conn->prepare("DELETE FROM $tabela WHERE $nome_campo_id = ?");
            $stmt->execute([$id_para_excluir]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['mensagem'] = ucfirst($tabela) . " excluído(a) com sucesso!";
            } else {
                $_SESSION['mensagem'] = "Item não encontrado ou já excluído.";
            }
        } catch (PDOException $e) {
            $_SESSION['mensagem'] = "Erro ao excluir: " . $e->getMessage();
            $_SESSION['mensagem_tipo'] = 'erro';
        }
    }
    
    // Redireciona para a mesma página para evitar reenvio do formulário
    header("Location: excluir.php?aba=" . $aba_retorno);
    exit();
}


// --- LÓGICA DE EXIBIÇÃO (AÇÕES GET) ---
include_once("templates/header.php");

$aba_ativa = $_GET['aba'] ?? 'aulas'; // Aba padrão
$titulo_aba = '';
$itens_para_excluir = [];
$erro = '';

try {
    switch ($aba_ativa) {
        case 'professores':
            $titulo_aba = 'Professores';
            $itens_para_excluir = $conn->query("SELECT id, nome FROM professores ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
            break;
        case 'turmas':
            $titulo_aba = 'Turmas';
            $itens_para_excluir = $conn->query("SELECT id, nome_turma AS nome FROM turmas ORDER BY nome_turma")->fetchAll(PDO::FETCH_ASSOC);
            break;
        case 'salas':
            $titulo_aba = 'Salas';
            $itens_para_excluir = $conn->query("SELECT id, numero_sala, bloco FROM salas ORDER BY bloco, numero_sala")->fetchAll(PDO::FETCH_ASSOC);
            break;
        case 'aulas':
        default:
            $aba_ativa = 'aulas';
            $titulo_aba = 'Aulas Agendadas';
            $itens_para_excluir = $conn->query(
                "SELECT a.id, a.data_aula, t.nome_turma, p.nome AS nome_professor 
                 FROM aulas a 
                 JOIN turmas t ON a.turma_id = t.id 
                 JOIN professores p ON a.professor_id = p.id 
                 ORDER BY a.data_aula DESC, t.nome_turma"
            )->fetchAll(PDO::FETCH_ASSOC);
            break;
    }
} catch (PDOException $e) {
    $erro = "Erro ao carregar dados: " . $e->getMessage();
}
?>
<link rel="stylesheet" href="css/style.css">
<style>
    /* Estilos para as abas de navegação */
    .nav-abas { margin-bottom: 20px; border-bottom: 2px solid #ccc; padding-bottom: 10px; }
    .nav-abas a { padding: 10px 15px; text-decoration: none; color: #0056b3; border: 1px solid transparent; border-radius: 5px 5px 0 0; }
    .nav-abas a.active { font-weight: bold; border-color: #ccc; border-bottom-color: white; background-color: white; position: relative; top: 3px; }
</style>

<main class="form-container">
    <div class="form-card">
        <h2>Área de Exclusão</h2>

        <nav class="nav-abas">
            <a href="excluir.php?aba=aulas" class="<?= ($aba_ativa == 'aulas') ? 'active' : '' ?>">Excluir Aulas</a>
            <a href="excluir.php?aba=professores" class="<?= ($aba_ativa == 'professores') ? 'active' : '' ?>">Excluir Professores</a>
            <a href="excluir.php?aba=turmas" class="<?= ($aba_ativa == 'turmas') ? 'active' : '' ?>">Excluir Turmas</a>
            <a href="excluir.php?aba=salas" class="<?= ($aba_ativa == 'salas') ? 'active' : '' ?>">Excluir Salas</a>
        </nav>

        <h3><?php echo $titulo_aba; ?></h3>

        <?php if (isset($_SESSION['mensagem'])): ?>
            <div class="message <?= ($_SESSION['mensagem_tipo'] ?? '') === 'erro' ? 'error' : 'success' ?>">
                <?= $_SESSION['mensagem']; ?>
            </div>
            <?php unset($_SESSION['mensagem'], $_SESSION['mensagem_tipo']); ?>
        <?php endif; ?>

        <?php if ($erro): ?>
            <p class="message error"><?= htmlspecialchars($erro); ?></p>
        <?php elseif (empty($itens_para_excluir)): ?>
            <p>Nenhum item para excluir encontrado.</p>
        <?php else: ?>
            <table>
                <thead>
                    <?php if ($aba_ativa == 'aulas'): ?>
                        <tr><th>Data</th><th>Turma</th><th>Professor</th><th>Ação</th></tr>
                    <?php elseif ($aba_ativa == 'salas'): ?>
                        <tr><th>Nº Sala</th><th>Bloco</th><th>Ação</th></tr>
                    <?php else: // Professores e Turmas ?>
                        <tr><th>Nome</th><th>Ação</th></tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                    <?php foreach ($itens_para_excluir as $item): ?>
                        <tr>
                            <!-- Colunas de dados da tabela -->
                            <?php if ($aba_ativa == 'aulas'): ?>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($item['data_aula']))); ?></td>
                                <td><?= htmlspecialchars($item['nome_turma']); ?></td>
                                <td><?= htmlspecialchars($item['nome_professor']); ?></td>
                            <?php elseif ($aba_ativa == 'salas'): ?>
                                <td><?= htmlspecialchars($item['numero_sala']); ?></td>
                                <td><?= htmlspecialchars($item['bloco']); ?></td>
                            <?php else: // Professores e Turmas ?>
                                <td><?= htmlspecialchars($item['nome']); ?></td>
                            <?php endif; ?>
                            
                            <!-- Coluna de Ação com o formulário de exclusão -->
                            <td>
                                <form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este item? A ação não pode ser desfeita.');">
                                    <input type="hidden" name="aba_retorno" value="<?= $aba_ativa; ?>">
                                    <?php if ($aba_ativa == 'aulas'): ?>
                                        <input type="hidden" name="excluir_aula_id" value="<?= $item['id']; ?>">
                                    <?php elseif ($aba_ativa == 'professores'): ?>
                                        <input type="hidden" name="excluir_professor_id" value="<?= $item['id']; ?>">
                                    <?php elseif ($aba_ativa == 'turmas'): ?>
                                        <input type="hidden" name="excluir_turma_id" value="<?= $item['id']; ?>">
                                    <?php elseif ($aba_ativa == 'salas'): ?>
                                        <input type="hidden" name="excluir_sala_id" value="<?= $item['id']; ?>">
                                    <?php endif; ?>
                                    <button type="submit" class="btn-excluir">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>
