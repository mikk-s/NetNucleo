<?php
session_start();
require "conexao.php"; // Este arquivo deve criar a variável $conn = new PDO(...);

// Verifica se o usuário está logado
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

include_once("templates/header.php");

// --- Mantém o estado dos filtros após o envio do formulário ---
$filtro_turma_id = $_POST['turma_id'] ?? '';
$filtro_data_inicio_turma = $_POST['data_inicio_turma'] ?? '';
$filtro_data_fim_turma = $_POST['data_fim_turma'] ?? '';

$filtro_professor_id = $_POST['professor_id'] ?? '';
$filtro_data_inicio_professor = $_POST['data_inicio_professor'] ?? '';
$filtro_data_fim_professor = $_POST['data_fim_professor'] ?? '';

$filtro_sala_id = $_POST['sala_id'] ?? '';
$filtro_data_inicio_sala = $_POST['data_inicio_sala'] ?? '';
$filtro_data_fim_sala = $_POST['data_fim_sala'] ?? '';


// --- LÓGICA PARA POPULAR OS MENUS DE SELEÇÃO (DROPDOWNS) ---
try {
    // Busca os dados para preencher os formulários
    $turmas = $conn->query("SELECT id, nome_turma FROM turmas ORDER BY nome_turma")->fetchAll(PDO::FETCH_ASSOC);
    $salas = $conn->query("SELECT id, numero_sala, bloco FROM salas ORDER BY bloco, numero_sala")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Em caso de erro, inicializa como arrays vazios para não quebrar o HTML
    $turmas = [];
    $salas = [];
    $erro_geral = "Erro ao carregar dados para os filtros: " . $e->getMessage();
}

// Variáveis para guardar os resultados e títulos
$resultados = [];
$titulo_resultado = '';
$erro_busca = null;

// Função auxiliar para criar o texto do período
function formatar_titulo_periodo($inicio, $fim) {
    if ($inicio && $fim) {
        return ' no período de ' . htmlspecialchars(date('d/m/Y', strtotime($inicio))) . ' a ' . htmlspecialchars(date('d/m/Y', strtotime($fim)));
    } elseif ($inicio) {
        return ' a partir de ' . htmlspecialchars(date('d/m/Y', strtotime($inicio)));
    } elseif ($fim) {
        return ' até ' . htmlspecialchars(date('d/m/Y', strtotime($fim)));
    }
    return '';
}

// --- PROCESSAMENTO DOS FORMULÁRIOS ---

// VERIFICA SE A PESQUISA POR TURMA FOI FEITA
if (isset($_POST['buscar_por_turma'])) {
    if (!empty($filtro_turma_id) || !empty($filtro_data_inicio_turma) || !empty($filtro_data_fim_turma)) {
        
        $params = [];
        $aula_join_conditions = ["t.id = a.turma_id"];
        
        $has_date_filter = !empty($filtro_data_inicio_turma) || !empty($filtro_data_fim_turma);
        $is_specific_search = !empty($filtro_turma_id) && $filtro_turma_id !== 'all';

        if (!empty($filtro_data_inicio_turma)) {
            $aula_join_conditions[] = "a.data_aula >= ?";
            $params[] = $filtro_data_inicio_turma;
        }
        if (!empty($filtro_data_fim_turma)) {
            $aula_join_conditions[] = "a.data_aula <= ?";
            $params[] = $filtro_data_fim_turma;
        }
        $aula_join_str = implode(' AND ', $aula_join_conditions);
        
        // Se for uma pesquisa geral com filtro de data, usa INNER JOIN para ocultar resultados vazios.
        // Caso contrário, usa LEFT JOIN para mostrar todas as turmas/professores/salas.
        $join_type = (!$is_specific_search && $has_date_filter) ? "INNER JOIN" : "LEFT JOIN";

        $sql = "SELECT 'turma' as tipo, t.nome_turma, a.data_aula, a.hora_inicio, a.hora_fim, p.nome AS nome_professor, p.unidade_curricular, s.numero_sala, s.bloco, s.capacidade
                FROM turmas t
                {$join_type} aulas a ON {$aula_join_str}
                LEFT JOIN professores p ON a.professor_id = p.id
                LEFT JOIN salas s ON a.sala_id = s.id";
        
        $where = [];
        if ($is_specific_search) {
            $where[] = "t.id = ?";
            $params[] = $filtro_turma_id;
            $stmt_nome = $conn->prepare("SELECT nome_turma FROM turmas WHERE id = ?");
            $stmt_nome->execute([$filtro_turma_id]);
            $turma_info = $stmt_nome->fetch(PDO::FETCH_ASSOC);
            $titulo_resultado = 'Aulas da Turma: ' . htmlspecialchars($turma_info['nome_turma']);
        } else {
            $titulo_resultado = 'Lista de Turmas e Aulas Agendadas';
        }
        $titulo_resultado .= formatar_titulo_periodo($filtro_data_inicio_turma, $filtro_data_fim_turma);

        if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);
        $sql .= " ORDER BY t.nome_turma, a.data_aula, a.hora_inicio";
        
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $erro_busca = "Erro ao buscar aulas: " . $e->getMessage();
        }
    }
}

// VERIFICA SE A PESQUISA POR PROFESSOR FOI FEITA
if (isset($_POST['buscar_por_professor'])) {
    if (!empty($filtro_professor_id) || !empty($filtro_data_inicio_professor) || !empty($filtro_data_fim_professor)) {
        $params = [];
        $aula_join_conditions = ["p.id = a.professor_id"];
        
        $has_date_filter = !empty($filtro_data_inicio_professor) || !empty($filtro_data_fim_professor);
        $is_specific_search = !empty($filtro_professor_id) && $filtro_professor_id !== 'all';

        if (!empty($filtro_data_inicio_professor)) {
            $aula_join_conditions[] = "a.data_aula >= ?";
            $params[] = $filtro_data_inicio_professor;
        }
        if (!empty($filtro_data_fim_professor)) {
            $aula_join_conditions[] = "a.data_aula <= ?";
            $params[] = $filtro_data_fim_professor;
        }
        $aula_join_str = implode(' AND ', $aula_join_conditions);
        
        $join_type = (!$is_specific_search && $has_date_filter) ? "INNER JOIN" : "LEFT JOIN";
        
        $sql = "SELECT 'professor' as tipo, p.nome AS nome_professor, p.unidade_curricular, a.data_aula, a.hora_inicio, a.hora_fim, t.nome_turma, s.numero_sala, s.bloco
                FROM professores p
                {$join_type} aulas a ON {$aula_join_str}
                LEFT JOIN turmas t ON a.turma_id = t.id
                LEFT JOIN salas s ON a.sala_id = s.id";

        $where = [];
        if ($is_specific_search) {
            $professor_ids = explode(',', $filtro_professor_id);
            $placeholders = implode(',', array_fill(0, count($professor_ids), '?'));
            $where[] = "p.id IN ({$placeholders})";
            $params = array_merge($params, $professor_ids);

            $stmt_nome = $conn->prepare("SELECT nome FROM professores WHERE id = ?");
            $stmt_nome->execute([$professor_ids[0]]);
            $prof_info = $stmt_nome->fetch(PDO::FETCH_ASSOC);
            if ($prof_info) {
                $titulo_resultado = 'Agenda de: ' . htmlspecialchars($prof_info['nome']);
            }
        } else {
             $titulo_resultado = 'Lista de Professores e Aulas Agendadas';
        }
        $titulo_resultado .= formatar_titulo_periodo($filtro_data_inicio_professor, $filtro_data_fim_professor);

        if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);
        $sql .= " ORDER BY p.nome, a.data_aula, a.hora_inicio";
        
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $erro_busca = "Erro ao buscar agenda: " . $e->getMessage();
        }
    }
}

// VERIFICA SE A PESQUISA POR SALA FOI FEITA
if (isset($_POST['buscar_por_sala'])) {
    if (!empty($filtro_sala_id) || !empty($filtro_data_inicio_sala) || !empty($filtro_data_fim_sala)) {
        $params = [];
        $aula_join_conditions = ["s.id = a.sala_id"];
        
        $has_date_filter = !empty($filtro_data_inicio_sala) || !empty($filtro_data_fim_sala);
        $is_specific_search = !empty($filtro_sala_id) && $filtro_sala_id !== 'all';

        if (!empty($filtro_data_inicio_sala)) {
            $aula_join_conditions[] = "a.data_aula >= ?";
            $params[] = $filtro_data_inicio_sala;
        }
        if (!empty($filtro_data_fim_sala)) {
            $aula_join_conditions[] = "a.data_aula <= ?";
            $params[] = $filtro_data_fim_sala;
        }
        $aula_join_str = implode(' AND ', $aula_join_conditions);
        
        $join_type = (!$is_specific_search && $has_date_filter) ? "INNER JOIN" : "LEFT JOIN";
        
        $sql = "SELECT 'sala' as tipo, s.numero_sala, s.bloco, a.data_aula, a.hora_inicio, a.hora_fim, t.nome_turma, p.nome AS nome_professor, p.unidade_curricular
                FROM salas s
                {$join_type} aulas a ON {$aula_join_str}
                LEFT JOIN turmas t ON a.turma_id = t.id
                LEFT JOIN professores p ON a.professor_id = p.id";

        $where = [];
        if ($is_specific_search) {
            $where[] = "s.id = ?";
            $params[] = $filtro_sala_id;
            $stmt_nome = $conn->prepare("SELECT numero_sala, bloco FROM salas WHERE id = ?");
            $stmt_nome->execute([$filtro_sala_id]);
            $sala_info = $stmt_nome->fetch(PDO::FETCH_ASSOC);
            $titulo_resultado = 'Aulas na Sala: ' . htmlspecialchars($sala_info['bloco'] . ' - ' . $sala_info['numero_sala']);
        } else {
            $titulo_resultado = 'Lista de Salas e Aulas Agendadas';
        }
        $titulo_resultado .= formatar_titulo_periodo($filtro_data_inicio_sala, $filtro_data_fim_sala);

        if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);
        $sql .= " ORDER BY s.bloco, s.numero_sala, a.data_aula";

        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $erro_busca = "Erro ao buscar aulas: " . $e->getMessage();
        }
    }
}
?>

<link rel="stylesheet" href="<?= $BASE_URL?>css/style.css">

<main class="canvas-container">
    <div class="filtros-card">
        <h2>Consultas</h2>
        <?php if (isset($erro_geral)): ?>
            <div class="message error"><?= htmlspecialchars($erro_geral); ?></div>
        <?php endif; ?>

        <div class="filtros-grid">
            <!-- Pesquisa por Turma -->
            <form method="POST" class="filtro-bloco">
                <fieldset class="form-card">
                    <legend>Por Turma</legend>
                    <label for="turma_id">Selecione a Turma:</label>
                    <select name="turma_id" id="turma_id">
                        <option value="all" <?= ($filtro_turma_id === 'all') ? 'selected' : '' ?>>Todas as turmas</option>
                        <?php foreach ($turmas as $turma): ?>
                            <option value="<?= $turma['id']; ?>" <?= ($filtro_turma_id == $turma['id']) ? 'selected' : '' ?>><?= htmlspecialchars($turma['nome_turma']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="date-range">
                        <div class="date-range-item">
                            <label for="data_inicio_turma">De:</label>
                            <input type="date" name="data_inicio_turma" id="data_inicio_turma" value="<?= htmlspecialchars($filtro_data_inicio_turma) ?>">
                        </div>
                        <div class="date-range-item">
                            <label for="data_fim_turma">Até:</label>
                            <input type="date" name="data_fim_turma" id="data_fim_turma" value="<?= htmlspecialchars($filtro_data_fim_turma) ?>">
                        </div>
                    </div>
                    <button type="submit" name="buscar_por_turma" class="submit-button">Pesquisar Turma</button>
                    <button type="button" class="clear-date-button" onclick="clearDatesInForm(this)">Limpar Datas</button>
                </fieldset>
            </form>

            <!-- Pesquisa por Professor -->
            <form method="POST" class="filtro-bloco">
                <fieldset class="form-card">
                    <legend>Por Professor</legend>
                    <label for="professor_id">Selecione o Professor:</label>
                    <select name="professor_id" id="professor_id">
                        <option value="all" <?= ($filtro_professor_id === 'all') ? 'selected' : '' ?>>Todos os professores</option>
                        <?php
                        $sql_professores_grouped = "SELECT nome, GROUP_CONCAT(id ORDER BY id) as ids FROM professores GROUP BY nome ORDER BY nome";
                        $stmt_professores_grouped = $conn->query($sql_professores_grouped);
                        while ($prof = $stmt_professores_grouped->fetch(PDO::FETCH_ASSOC)) {
                            $value = $prof['ids'];
                            $ids_array = explode(',', $value);
                            $selected = in_array($filtro_professor_id, $ids_array) || $filtro_professor_id === $value ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($value) . "' $selected>" . htmlspecialchars($prof['nome']) . "</option>";
                        }
                        ?>
                    </select>
                    <div class="date-range">
                        <div class="date-range-item">
                           <label for="data_inicio_professor">De:</label>
                            <input type="date" name="data_inicio_professor" id="data_inicio_professor" value="<?= htmlspecialchars($filtro_data_inicio_professor) ?>">
                        </div>
                         <div class="date-range-item">
                            <label for="data_fim_professor">Até:</label>
                            <input type="date" name="data_fim_professor" id="data_fim_professor" value="<?= htmlspecialchars($filtro_data_fim_professor) ?>">
                        </div>
                    </div>
                    <button type="submit" name="buscar_por_professor" class="submit-button">Pesquisar Professor</button>
                    <button type="button" class="clear-date-button" onclick="clearDatesInForm(this)">Limpar Datas</button>
                </fieldset>
            </form>

            <!-- Pesquisa por Sala -->
            <form method="POST" class="filtro-bloco">
                <fieldset class="form-card"> 
                    <legend>Por Sala</legend>
                    <label for="sala_id">Selecione a Sala:</label>
                    <select name="sala_id" id="sala_id">
                        <option value="all" <?= ($filtro_sala_id === 'all') ? 'selected' : '' ?>>Todas as salas</option>
                        <?php foreach ($salas as $sala): ?>
                            <option value="<?= $sala['id']; ?>" <?= ($filtro_sala_id == $sala['id']) ? 'selected' : '' ?>><?= htmlspecialchars($sala['bloco'] . ' - ' . $sala['numero_sala']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="date-range">
                        <div class="date-range-item">
                            <label for="data_inicio_sala">De:</label>
                             <input type="date" name="data_inicio_sala" id="data_inicio_sala" value="<?= htmlspecialchars($filtro_data_inicio_sala) ?>">
                        </div>
                        <div class="date-range-item">
                            <label for="data_fim_sala">Até:</label>
                            <input type="date" name="data_fim_sala" id="data_fim_sala" value="<?= htmlspecialchars($filtro_data_fim_sala) ?>">
                        </div>
                    </div>
                    <button type="submit" name="buscar_por_sala" class="submit-button">Pesquisar Sala</button>
                    <button type="button" class="clear-date-button" onclick="clearDatesInForm(this)">Limpar Datas</button>
                </fieldset>
            </form>
        </div>
    </div>

    <?php if (!empty($resultados) || $erro_busca || (isset($_POST['buscar_por_turma']) || isset($_POST['buscar_por_professor']) || isset($_POST['buscar_por_sala']))): ?>
    <div class="resultados-card">
        <h2>Resultados da Consulta</h2>
        <h3><?= $titulo_resultado ?></h3>
        
        <?php if ($erro_busca): ?>
            <p class="message error"><?= htmlspecialchars($erro_busca); ?></p>
        <?php elseif (empty($resultados)): ?>
            <p>Nenhum registro encontrado para os filtros selecionados.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <?php if ($resultados[0]['tipo'] === 'turma'): ?>
                            <th>Turma</th><th>Data</th><th>Horário</th><th>Professor</th><th>Un. Curricular</th><th>Capacidade</th><th>Sala</th>
                        <?php elseif ($resultados[0]['tipo'] === 'professor'): ?>
                            <th>Professor</th><th>Un. Curricular</th><th>Data</th><th>Horário</th><th>Turma</th><th>Sala</th>
                        <?php elseif ($resultados[0]['tipo'] === 'sala'): ?>
                            <th>Sala</th><th>Data</th><th>Horário</th><th>Turma</th><th>Professor</th><th>Un. Curricular</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados as $aula): ?>
                        <tr>
                            <?php if ($aula['tipo'] === 'turma'): ?>
                                <td><?= htmlspecialchars($aula['nome_turma']); ?></td>
                                <td><?= $aula['data_aula'] ? htmlspecialchars(date('d/m/Y', strtotime($aula['data_aula']))) : '---'; ?></td>
                                <td><?= $aula['hora_inicio'] ? htmlspecialchars(substr($aula['hora_inicio'], 0, 5) . ' - ' . substr($aula['hora_fim'], 0, 5)) : '---'; ?></td>
                                <td><?= htmlspecialchars($aula['nome_professor'] ?? '---'); ?></td>
                                <td><?= htmlspecialchars($aula['unidade_curricular'] ?? '---'); ?></td>
                                <td><?= $aula['capacidade'] ? htmlspecialchars($aula['capacidade']) . ' pessoas' : '---'; ?></td>
                                <td><?= $aula['numero_sala'] ? htmlspecialchars($aula['bloco'] . ' - ' . $aula['numero_sala']) : '---'; ?></td>
                            <?php elseif ($aula['tipo'] === 'professor'): ?>
                                <td><?= htmlspecialchars($aula['nome_professor']); ?></td>
                                <td><?= htmlspecialchars($aula['unidade_curricular']); ?></td>
                                <td><?= $aula['data_aula'] ? htmlspecialchars(date('d/m/Y', strtotime($aula['data_aula']))) : '---'; ?></td>
                                <td><?= $aula['hora_inicio'] ? htmlspecialchars(substr($aula['hora_inicio'], 0, 5) . ' - ' . substr($aula['hora_fim'], 0, 5)) : '---'; ?></td>
                                <td><?= htmlspecialchars($aula['nome_turma'] ?? '---'); ?></td>
                                <td><?= $aula['numero_sala'] ? htmlspecialchars($aula['bloco'] . ' - ' . $aula['numero_sala']) : '---'; ?></td>
                            <?php elseif ($aula['tipo'] === 'sala'): ?>
                                <td><?= htmlspecialchars($aula['bloco'] . ' - ' . $aula['numero_sala']); ?></td>
                                <td><?= $aula['data_aula'] ? htmlspecialchars(date('d/m/Y', strtotime($aula['data_aula']))) : '---'; ?></td>
                                <td><?= $aula['hora_inicio'] ? htmlspecialchars(substr($aula['hora_inicio'], 0, 5) . ' - ' . substr($aula['hora_fim'], 0, 5)) : '---'; ?></td>
                                <td><?= htmlspecialchars($aula['nome_turma'] ?? '---'); ?></td>
                                <td><?= htmlspecialchars($aula['nome_professor'] ?? '---'); ?></td>
                                <td><?= htmlspecialchars($aula['unidade_curricular'] ?? '---'); ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</main>

<script>
    function clearDatesInForm(buttonEl) {
        const form = buttonEl.closest('form');
        if (form) {
            const dateInputs = form.querySelectorAll('input[type="date"]');
            dateInputs.forEach(input => {
                input.value = '';
            });
        }
    }
</script>

