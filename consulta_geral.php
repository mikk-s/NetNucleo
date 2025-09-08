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
$filtro_data_turma = $_POST['data_filtro_turma'] ?? '';
$filtro_professor_id = $_POST['professor_id'] ?? '';
$filtro_data_professor = $_POST['data_filtro_professor'] ?? '';
$filtro_sala_id = $_POST['sala_id'] ?? '';
$filtro_data_sala = $_POST['data_filtro_sala'] ?? '';


// --- LÓGICA PARA POPULAR OS MENUS DE SELEÇÃO (DROPDOWNS) ---
try {
    // Busca os dados para preencher os formulários
    $professores = $conn->query("SELECT id, nome FROM professores ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
    $turmas = $conn->query("SELECT id, nome_turma FROM turmas ORDER BY nome_turma")->fetchAll(PDO::FETCH_ASSOC);
    $salas = $conn->query("SELECT id, numero_sala, bloco FROM salas ORDER BY bloco, numero_sala")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Em caso de erro, inicializa como arrays vazios para não quebrar o HTML
    $professores = [];
    $turmas = [];
    $salas = [];
    $erro_geral = "Erro ao carregar dados para os filtros: " . $e->getMessage();
}

// Variáveis para guardar os resultados e títulos
$resultados_turma = [];
$resultados_professor = [];
$resultados_sala = [];
$titulo_resultado_turma = '';
$titulo_resultado_professor = '';
$titulo_resultado_sala = '';

// --- PROCESSAMENTO DOS FORMULÁRIOS ---

// VERIFICA SE A PESQUISA POR TURMA FOI FEITA
if (isset($_POST['buscar_por_turma'])) {
    if (!empty($filtro_turma_id) || !empty($filtro_data_turma)) {
        $sql = "SELECT a.data_aula, a.hora_inicio, a.hora_fim, t.nome_turma, p.nome AS nome_professor, p.unidade_curricular, s.numero_sala, s.bloco, s.capacidade
                FROM aulas a
                JOIN professores p ON a.professor_id = p.id
                JOIN salas s ON a.sala_id = s.id
                JOIN turmas t ON a.turma_id = t.id";
        
        $where = [];
        $params = [];
        $titulo_resultado_turma = 'Resultado da Busca'; // Título padrão

        if ($filtro_turma_id && $filtro_turma_id !== 'all') {
            $where[] = "a.turma_id = ?";
            $params[] = $filtro_turma_id;
            $stmt_nome = $conn->prepare("SELECT nome_turma FROM turmas WHERE id = ?");
            $stmt_nome->execute([$filtro_turma_id]);
            $turma_info = $stmt_nome->fetch(PDO::FETCH_ASSOC);
            $titulo_resultado_turma = 'Aulas da Turma: ' . htmlspecialchars($turma_info['nome_turma']);
        } elseif ($filtro_turma_id === 'all') {
             $titulo_resultado_turma = 'Todas as Aulas';
        }

        if (!empty($filtro_data_turma)) {
            $where[] = "a.data_aula = ?";
            $params[] = $filtro_data_turma;
            $data_formatada = htmlspecialchars(date('d/m/Y', strtotime($filtro_data_turma)));
             $titulo_resultado_turma .= ($titulo_resultado_turma === 'Resultado da Busca' ? 'Aulas do dia ' : ' no dia ') . $data_formatada;
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY a.data_aula, a.hora_inicio, t.nome_turma";
        
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $resultados_turma = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $erro_turma = "Erro ao buscar aulas: " . $e->getMessage();
        }
    }
}


// VERIFICA SE A PESQUISA POR PROFESSOR FOI FEITA
if (isset($_POST['buscar_por_professor'])) {
    // Caso especial: Mostrar todos os professores, com ou sem aulas.
    if ($filtro_professor_id === 'all') {
        $titulo_resultado_professor = 'Lista de Professores e Aulas Agendadas';
        if (!empty($filtro_data_professor)) {
            $titulo_resultado_professor .= ' no dia ' . htmlspecialchars(date('d/m/Y', strtotime($filtro_data_professor)));
        }
        try {
            $params = [];
            $aulas_subquery = "aulas";

            if (!empty($filtro_data_professor)) {
                $aulas_subquery = "(SELECT * FROM aulas WHERE data_aula = ?)";
                $params[] = $filtro_data_professor;
            }

            $sql = "SELECT p.nome AS nome_professor, p.unidade_curricular, a.data_aula, a.hora_inicio, a.hora_fim, t.nome_turma, s.numero_sala, s.bloco
                    FROM professores p
                    LEFT JOIN {$aulas_subquery} a ON p.id = a.professor_id
                    LEFT JOIN turmas t ON a.turma_id = t.id
                    LEFT JOIN salas s ON a.sala_id = s.id
                    ORDER BY p.nome, a.data_aula";

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $resultados_professor = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $erro_professor = "Erro ao buscar agenda: " . $e->getMessage();
        }
    }
    // Lógica para buscas específicas (por professor específico ou apenas por data)
    elseif (!empty($filtro_professor_id) || !empty($filtro_data_professor)) {
        $sql = "SELECT a.data_aula, a.hora_inicio, a.hora_fim, t.nome_turma, s.numero_sala, s.bloco, p.unidade_curricular, p.nome AS nome_professor
                FROM aulas a
                JOIN turmas t ON a.turma_id = t.id
                JOIN salas s ON a.sala_id = s.id
                JOIN professores p ON a.professor_id = p.id";
        
        $where = [];
        $params = [];
        $titulo_resultado_professor = 'Resultado da Busca';

        if (!empty($filtro_professor_id)) {
            $where[] = "a.professor_id = ?";
            $params[] = $filtro_professor_id;
            $stmt_nome = $conn->prepare("SELECT nome FROM professores WHERE id = ?");
            $stmt_nome->execute([$filtro_professor_id]);
            $prof_info = $stmt_nome->fetch(PDO::FETCH_ASSOC);
            $titulo_resultado_professor = 'Agenda de: ' . htmlspecialchars($prof_info['nome']);
        }

        if (!empty($filtro_data_professor)) {
            $where[] = "a.data_aula = ?";
            $params[] = $filtro_data_professor;
            $data_formatada = htmlspecialchars(date('d/m/Y', strtotime($filtro_data_professor)));
            $titulo_resultado_professor .= ($titulo_resultado_professor === 'Resultado da Busca' ? 'Aulas do dia ' : ' no dia ') . $data_formatada;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY a.data_aula, a.hora_inicio";
        
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $resultados_professor = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $erro_professor = "Erro ao buscar agenda: " . $e->getMessage();
        }
    }
}

// VERIFICA SE A PESQUISA POR SALA FOI FEITA
if (isset($_POST['buscar_por_sala'])) {
     // Caso especial: Mostrar todas as salas, com ou sem aulas.
    if ($filtro_sala_id === 'all') {
        $titulo_resultado_sala = 'Lista de Salas e Aulas Agendadas';
        if (!empty($filtro_data_sala)) {
            $titulo_resultado_sala .= ' no dia ' . htmlspecialchars(date('d/m/Y', strtotime($filtro_data_sala)));
        }
        try {
            $params = [];
            $aulas_subquery = "aulas";

            if (!empty($filtro_data_sala)) {
                $aulas_subquery = "(SELECT * FROM aulas WHERE data_aula = ?)";
                $params[] = $filtro_data_sala;
            }

            $sql = "SELECT s.numero_sala, s.bloco, a.data_aula, a.hora_inicio, a.hora_fim, t.nome_turma, p.nome AS nome_professor, p.unidade_curricular
                    FROM salas s
                    LEFT JOIN {$aulas_subquery} a ON s.id = a.sala_id
                    LEFT JOIN turmas t ON a.turma_id = t.id
                    LEFT JOIN professores p ON a.professor_id = p.id
                    ORDER BY s.bloco, s.numero_sala, a.data_aula";

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $resultados_sala = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $erro_sala = "Erro ao buscar aulas da sala: " . $e->getMessage();
        }
    }
    // Lógica para buscas específicas (por sala específica ou apenas por data)
    elseif(!empty($filtro_sala_id) || !empty($filtro_data_sala)) {
         $sql = "SELECT a.data_aula, a.hora_inicio, a.hora_fim, t.nome_turma, p.nome AS nome_professor, p.unidade_curricular, s.numero_sala, s.bloco
                FROM aulas a
                JOIN turmas t ON a.turma_id = t.id
                JOIN professores p ON a.professor_id = p.id
                JOIN salas s ON a.sala_id = s.id";
            
        $where = [];
        $params = [];
        $titulo_resultado_sala = 'Resultado da Busca';

        if (!empty($filtro_sala_id)) {
            $where[] = "a.sala_id = ?";
            $params[] = $filtro_sala_id;
            $stmt_nome = $conn->prepare("SELECT numero_sala, bloco FROM salas WHERE id = ?");
            $stmt_nome->execute([$filtro_sala_id]);
            $sala_info = $stmt_nome->fetch(PDO::FETCH_ASSOC);
            $titulo_resultado_sala = 'Aulas na Sala: ' . htmlspecialchars($sala_info['bloco'] . ' - ' . $sala_info['numero_sala']);
        }
        
        if (!empty($filtro_data_sala)) {
            $where[] = "a.data_aula = ?";
            $params[] = $filtro_data_sala;
            $data_formatada = htmlspecialchars(date('d/m/Y', strtotime($filtro_data_sala)));
            $titulo_resultado_sala .= ($titulo_resultado_sala === 'Resultado da Busca' ? 'Aulas do dia ' : ' no dia ') . $data_formatada;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY a.data_aula, a.hora_inicio";

        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $resultados_sala = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $erro_sala = "Erro ao buscar aulas da sala: " . $e->getMessage();
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
                <select name="turma_id" id="turma_id">
                    <option value="">-- Nenhuma --</option>
                    <option value="all" <?= ($filtro_turma_id === 'all') ? 'selected' : '' ?>>Todas as aulas</option>
                    <?php foreach ($turmas as $turma): ?>
                        <option value="<?= $turma['id']; ?>" <?= ($filtro_turma_id == $turma['id']) ? 'selected' : '' ?>><?= htmlspecialchars($turma['nome_turma']); ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="data_filtro_turma">Filtrar por Data (Opcional):</label>
                <input type="date" name="data_filtro_turma" id="data_filtro_turma" value="<?= htmlspecialchars($filtro_data_turma) ?>">
                <button type="submit" name="buscar_por_turma" class="submit-button">Pesquisar Turma</button>
            </form>
        </fieldset>

        <fieldset class="form-card">
            <legend>Pesquisar Agenda por Professor</legend>
            <form method="POST">
                <label for="professor_id">Selecione o Professor:</label>
                <select name="professor_id" id="professor_id">
                    <option value="">-- Nenhum --</option>
                    <option value="all" <?= ($filtro_professor_id === 'all') ? 'selected' : '' ?>>Todos os professores</option>
                    <?php foreach ($professores as $professor): ?>
                        <option value="<?= $professor['id']; ?>" <?= ($filtro_professor_id == $professor['id']) ? 'selected' : '' ?>><?= htmlspecialchars($professor['nome']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="data_filtro_professor">Filtrar por Data (Opcional):</label>
                <input type="date" name="data_filtro_professor" id="data_filtro_professor" value="<?= htmlspecialchars($filtro_data_professor) ?>">

                <button type="submit" name="buscar_por_professor" class="submit-button">Pesquisar Professor</button>
            </form>
        </fieldset>
        
        <fieldset class="form-card">
            <legend>Pesquisar Aulas por Sala</legend>
            <form method="POST">
                <label for="sala_id">Selecione a Sala:</label>
                <select name="sala_id" id="sala_id">
                    <option value="">-- Nenhuma --</option>
                    <option value="all" <?= ($filtro_sala_id === 'all') ? 'selected' : '' ?>>Todas as salas</option>
                    <?php foreach ($salas as $sala): ?>
                        <option value="<?= $sala['id']; ?>" <?= ($filtro_sala_id == $sala['id']) ? 'selected' : '' ?>><?= htmlspecialchars($sala['bloco'] . ' - ' . $sala['numero_sala']); ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="data_filtro_sala">Filtrar por Data (Opcional):</label>
                <input type="date" name="data_filtro_sala" id="data_filtro_sala" value="<?= htmlspecialchars($filtro_data_sala) ?>">
                <button type="submit" name="buscar_por_sala" class="submit-button">Pesquisar Sala</button>
            </form>
        </fieldset>
    </div>

    <div class="form-container">
        <div class="form-card">
        <h2>Resultados</h2>

        <?php if (isset($_POST['buscar_por_turma'])): ?>
            <h3><?= $titulo_resultado_turma ?></h3>
            <?php if (isset($erro_turma)): ?>
                <p class="message error"><?= htmlspecialchars($erro_turma); ?></p>
            <?php elseif (empty($resultados_turma)): ?>
                <p>Nenhuma aula encontrada para os filtros selecionados.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Horário</th>
                            <?php if ($filtro_turma_id === 'all' || empty($filtro_turma_id)): ?>
                                <th>Turma</th>
                            <?php endif; ?>
                            <th>Professor</th>
                            <th>Unidade Curricular</th>
                            <th>Capacidade</th>
                            <th>Sala</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultados_turma as $aula): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($aula['data_aula']))); ?></td>
                                <td><?= htmlspecialchars(substr($aula['hora_inicio'], 0, 5) . ' - ' . substr($aula['hora_fim'], 0, 5)); ?></td>
                                <?php if ($filtro_turma_id === 'all' || empty($filtro_turma_id)): ?>
                                    <td><?= htmlspecialchars($aula['nome_turma']); ?></td>
                                <?php endif; ?>
                                <td><?= htmlspecialchars($aula['nome_professor']); ?></td>
                                <td><?= htmlspecialchars($aula['unidade_curricular']); ?></td>
                                <td><?= htmlspecialchars($aula['capacidade']) . ' pessoas'; ?></td>
                                <td><?= htmlspecialchars($aula['bloco'] . ' - ' . $aula['numero_sala']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (isset($_POST['buscar_por_professor'])): ?>
            <h3><?= $titulo_resultado_professor ?></h3>
            <?php if (isset($erro_professor)): ?>
                <p class="message error"><?= htmlspecialchars($erro_professor); ?></p>
            <?php elseif (empty($resultados_professor)): ?>
                <p>Nenhuma aula encontrada para os filtros selecionados.</p>
            <?php else: ?>
                <table>
                    <thead><tr>
                        <th>Professor</th>
                        <th>Unidade Curricular</th>
                        <th>Data da Aula</th>
                        <th>Horário</th>
                        <th>Turma</th>
                        <th>Sala</th>
                    </tr></thead>
                    <tbody>
                        <?php foreach ($resultados_professor as $aula): ?>
                            <tr>
                                <td><?= htmlspecialchars($aula['nome_professor']); ?></td>
                                <td><?= htmlspecialchars($aula['unidade_curricular']); ?></td>
                                <td><?= $aula['data_aula'] ? htmlspecialchars(date('d/m/Y', strtotime($aula['data_aula']))) : '---'; ?></td>
                                <td><?= $aula['hora_inicio'] ? htmlspecialchars(substr($aula['hora_inicio'], 0, 5) . ' - ' . substr($aula['hora_fim'], 0, 5)) : '---'; ?></td>
                                <td><?= htmlspecialchars($aula['nome_turma'] ?? '---'); ?></td>
                                <td><?= $aula['bloco'] ? htmlspecialchars($aula['bloco'] . ' - ' . $aula['numero_sala']) : '---'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php if (isset($_POST['buscar_por_sala'])): ?>
            <h3><?= $titulo_resultado_sala ?></h3>
            <?php if (isset($erro_sala)): ?>
                <p class="message error"><?= htmlspecialchars($erro_sala); ?></p>
            <?php elseif (empty($resultados_sala)): ?>
                <p>Nenhuma aula encontrada para os filtros selecionados.</p>
            <?php else: ?>
                <table>
                    <thead><tr>
                        <th>Sala</th>
                        <th>Data da Aula</th>
                        <th>Horário</th>
                        <th>Turma</th>
                        <th>Professor</th>
                        <th>Unidade Curricular</th>
                    </tr></thead>
                    <tbody>
                        <?php foreach ($resultados_sala as $aula): ?>
                            <tr>
                                <td><?= htmlspecialchars($aula['bloco'] . ' - ' . $aula['numero_sala']); ?></td>
                                <td><?= $aula['data_aula'] ? htmlspecialchars(date('d/m/Y', strtotime($aula['data_aula']))) : '---'; ?></td>
                                <td><?= $aula['hora_inicio'] ? htmlspecialchars(substr($aula['hora_inicio'], 0, 5) . ' - ' . substr($aula['hora_fim'], 0, 5)) : '---'; ?></td>
                                <td><?= htmlspecialchars($aula['nome_turma'] ?? '---'); ?></td>
                                <td><?= htmlspecialchars($aula['nome_professor'] ?? '---'); ?></td>
                                <td><?= htmlspecialchars($aula['unidade_curricular'] ?? '---'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
        </div>
    </div>
</main>

