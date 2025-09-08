<?php
session_start();
require "conexao.php"; // Este arquivo deve criar a variável $conn = new PDO(...);

// Verifica se o usuário está logado
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

include_once("templates/header.php");

// --- LÓGICA PARA POPULAR OS MENUS DE SELEÇÃO (DROPDOWNS) ---
try {
    // Busca os dados para preencher os formulários
    $professores = $conn->query("SELECT id, nome FROM professores ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
    $turmas = $conn->query("SELECT id, nome_turma FROM turmas ORDER BY nome_turma")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Em caso de erro, inicializa como arrays vazios para não quebrar o HTML
    $professores = [];
    $turmas = [];
    $erro_geral = "Erro ao carregar dados para os filtros: " . $e->getMessage();
}

// Variáveis para guardar os resultados
$resultados_turma = [];
$resultados_professor = [];

// --- PROCESSAMENTO DOS FORMULÁRIOS ---

// VERIFICA SE A PESQUISA POR TURMA FOI FEITA
if (isset($_POST['buscar_por_turma'])) {
    $turma_id = $_POST['turma_id'] ?? '';

    // Se a opção "Todas as aulas" for selecionada
    if ($turma_id === 'all') {
        try {
            $sql = "SELECT a.data_aula, a.hora_inicio, a.hora_fim, t.nome_turma, p.nome AS nome_professor, s.numero_sala, s.bloco
                    FROM aulas a
                    JOIN professores p ON a.professor_id = p.id
                    JOIN salas s ON a.sala_id = s.id
                    JOIN turmas t ON a.turma_id = t.id
                    ORDER BY a.data_aula, a.hora_inicio, t.nome_turma";
            $stmt = $conn->query($sql);
            $resultados_turma = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $erro_turma = "Erro ao buscar todas as aulas: " . $e->getMessage();
        }
    } 
    // Se uma turma específica for selecionada
    elseif (!empty($turma_id)) {
        try {
            $sql = "SELECT a.data_aula, a.hora_inicio, a.hora_fim, p.nome AS nome_professor, s.numero_sala, s.bloco
                    FROM aulas a
                    JOIN professores p ON a.professor_id = p.id
                    JOIN salas s ON a.sala_id = s.id
                    WHERE a.turma_id = ?
                    ORDER BY a.data_aula, a.hora_inicio";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$turma_id]);
            $resultados_turma = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $erro_turma = "Erro ao buscar aulas da turma: " . $e->getMessage();
        }
    }
}


// VERIFICA SE A PESQUISA POR PROFESSOR FOI FEITA
if (isset($_POST['buscar_por_professor'])) {
    $professor_id = $_POST['professor_id'];
    $data_filtro = $_POST['data_filtro'];

    if (!empty($professor_id)) {
        try {
            // SQL base para buscar a agenda de um professor
            $sql = "SELECT a.data_aula, a.hora_inicio, a.hora_fim, t.nome_turma, s.numero_sala, s.bloco
                    FROM aulas a
                    JOIN turmas t ON a.turma_id = t.id
                    JOIN salas s ON a.sala_id = s.id
                    WHERE a.professor_id = ?";
            
            $params = [$professor_id];

            // Adiciona o filtro de data APENAS se uma data for fornecida
            if (!empty($data_filtro)) {
                $sql .= " AND a.data_aula = ?";
                $params[] = $data_filtro;
            }

            $sql .= " ORDER BY a.data_aula, a.hora_inicio";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $resultados_professor = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $erro_professor = "Erro ao buscar agenda do professor: " . $e->getMessage();
        }
    }
}
?>

<main class="form-container">
    <div class="form-card">
        <h2>Consultas Gerais</h2>

        <?php if (isset($erro_geral)): ?>
            <div class="message error"><?= htmlspecialchars($erro_geral); ?></div>
        <?php endif; ?>

        <fieldset class="form-card">
            <legend>Pesquisar Aulas por Turma</legend>
            <form method="POST">
                <label for="turma_id">Selecione a Turma:</label>
                <select name="turma_id" id="turma_id" required>
                    <option value="">-- Escolha uma turma --</option>
                    <option value="all">Todas as aulas</option>
                    <?php foreach ($turmas as $turma): ?>
                        <option value="<?= $turma['id']; ?>"><?= htmlspecialchars($turma['nome_turma']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="buscar_por_turma" class="submit-button">Pesquisar Turma</button>
            </form>
        </fieldset>

        <fieldset class="form-card">
            <legend>Pesquisar Agenda por Professor</legend>
            <form method="POST">
                <label for="professor_id">Selecione o Professor:</label>
                <select name="professor_id" id="professor_id" required>
                    <option value="">-- Escolha um professor --</option>
                    <?php foreach ($professores as $professor): ?>
                        <option value="<?= $professor['id']; ?>"><?= htmlspecialchars($professor['nome']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="data_filtro">Filtrar por Data (Opcional):</label>
                <input type="date" name="data_filtro" id="data_filtro">

                <button type="submit" name="buscar_por_professor" class="submit-button">Pesquisar Professor</button>
            </form>
        </fieldset>
    </div>

    <div class="form-container">
        <div class="form-card">
        <h2>Resultados</h2>

        <?php if (isset($_POST['buscar_por_turma'])): ?>
            <h3>Aulas da Turma Selecionada</h3>
            <?php if (isset($erro_turma)): ?>
                <p class="message error"><?= htmlspecialchars($erro_turma); ?></p>
            <?php elseif (empty($resultados_turma)): ?>
                <p>Nenhuma aula encontrada para esta turma.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Horário</th>
                            <?php if (isset($_POST['turma_id']) && $_POST['turma_id'] === 'all'): ?>
                                <th>Turma</th>
                            <?php endif; ?>
                            <th>Professor</th>
                            <th>Sala</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultados_turma as $aula): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($aula['data_aula']))); ?></td>
                                <td><?= htmlspecialchars(substr($aula['hora_inicio'], 0, 5) . ' - ' . substr($aula['hora_fim'], 0, 5)); ?></td>
                                <?php if (isset($_POST['turma_id']) && $_POST['turma_id'] === 'all'): ?>
                                    <td><?= htmlspecialchars($aula['nome_turma']); ?></td>
                                <?php endif; ?>
                                <td><?= htmlspecialchars($aula['nome_professor']); ?></td>
                                <td><?= htmlspecialchars($aula['bloco'] . ' - ' . $aula['numero_sala']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (isset($_POST['buscar_por_professor'])): ?>
            <h3>Agenda do Professor Selecionado</h3>
            <?php if (isset($erro_professor)): ?>
                <p class="message error"><?= htmlspecialchars($erro_professor); ?></p>
            <?php elseif (empty($resultados_professor)): ?>
                <p>Nenhuma aula encontrada para este professor (e data, se informada).</p>
            <?php else: ?>
                <table>
                    <thead><tr><th>Data</th><th>Horário</th><th>Turma</th><th>Sala</th></tr></thead>
                    <tbody>
                        <?php foreach ($resultados_professor as $aula): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($aula['data_aula']))); ?></td>
                                <td><?= htmlspecialchars(substr($aula['hora_inicio'], 0, 5) . ' - ' . substr($aula['hora_fim'], 0, 5)); ?></td>
                                <td><?= htmlspecialchars($aula['nome_turma']); ?></td>
                                <td><?= htmlspecialchars($aula['bloco'] . ' - ' . $aula['numero_sala']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
        </div>
    </div>
</main>

