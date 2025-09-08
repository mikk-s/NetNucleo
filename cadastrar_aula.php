<?php
session_start();
require "conexao.php";

// Verifica se o usuário está logado
if (!isset($_SESSION["usuario"])) {
    $_SESSION['erro'] = "Você não está logado! Por favor, faça o login.";
    header("Location: login.php");
    exit();
} 


$erro_cadastro = "";
$sucesso_cadastro = "";

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $turma_id = $_POST['turma_id'];
    $professor_id = $_POST['professor_id'];
    $sala_id = $_POST['sala_id'];
    $data_aula = $_POST['data_aula'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fim = $_POST['hora_fim'];


        try {
            // Verifica se a aula já existe
            $check_sql = "SELECT COUNT(*) FROM aulas WHERE turma_id = :turma_id AND professor_id = :professor_id AND sala_id = :sala_id AND data_aula = :data_aula AND hora_inicio = :hora_inicio AND hora_fim = :hora_fim";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bindParam(':turma_id', $turma_id);
            $check_stmt->bindParam(':professor_id', $professor_id);
            $check_stmt->bindParam(':sala_id', $sala_id);
            $check_stmt->bindParam(':data_aula', $data_aula);
            $check_stmt->bindParam(':hora_inicio', $hora_inicio);
            $check_stmt->bindParam(':hora_fim', $hora_fim);
            $check_stmt->execute();
            $aula_existe = $check_stmt->fetchColumn();

            if ($aula_existe > 0) {
                $erro_cadastro = "Esta aula já está cadastrada.";
            } else {
                $stmt = $conn->prepare("INSERT INTO aulas(turma_id, professor_id, sala_id, data_aula, hora_inicio, hora_fim) VALUES (:turma_id, :professor_id, :sala_id, :data_aula, :hora_inicio, :hora_fim)");
                $stmt->bindParam(':turma_id', $turma_id);
                $stmt->bindParam(':professor_id', $professor_id);
                $stmt->bindParam(':sala_id', $sala_id);
                $stmt->bindParam(':data_aula', $data_aula);
                $stmt->bindParam(':hora_inicio', $hora_inicio);
                $stmt->bindParam(':hora_fim', $hora_fim);


                $stmt->execute();
                if(empty($_POST['turma_id']) || empty($_POST['professor_id']) || empty($_POST['sala_id']) || empty($_POST['data_aula']) || empty($_POST['hora_inicio']) || empty($_POST['hora_fim'])) {
                    $erro_cadastro = "Por favor, preencha todos os campos.";
                } else {
                $sucesso_cadastro = "Aula cadastrada com sucesso!";
                }
        }
    }
    catch (PDOException $e) {
        $erro_cadastro = "Erro ao cadastrar aula: " . $e->getMessage();
    }
}

include_once("templates/header.php"); 
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Aula</title>
    <link rel="stylesheet" href="css/style.css"> 

</head>
<body>
    <main class="form-container">
        <div class="form-card">
            <h2>Cadastrar Aula</h2>

            <?php if (!empty($erro_cadastro)): ?>
                <p style="color: red;"><?php echo $erro_cadastro; ?></p>
            <?php endif; ?>
            <?php if (!empty($sucesso_cadastro)): ?>
                <p style="color: green;"><?php echo $sucesso_cadastro; ?></p>
            <?php endif; ?>

            <form action="cadastrar_aula.php" method="POST">
                <div class="form-group">
                    <label for="n_sala">Sala:</label>
                    <select id="n_sala" name="sala_id" required>
                        <option value="">Selecione uma sala</option>"></option>
                    <?php
                    $sql_salas = "SELECT id, numero_sala, bloco FROM salas ORDER BY bloco ASC";

                    $resultado_salas = $conn->query($sql_salas);
                     if ($resultado_salas->rowCount() > 0) {
                          
                            while($sala = $resultado_salas->fetch(PDO::FETCH_ASSOC)) {
 echo "<option value='" . htmlspecialchars($sala['id'])."'>" . htmlspecialchars($sala['numero_sala']) . " - Bloco " . htmlspecialchars($sala['bloco']) . "</option>";
                            }
                        } else {
                           
                            echo "<option value=''>Nenhuma sala cadastrada</option>";
                        }
                    ?>
                    </select>
                </div>

                <div class="form-group">
                <label for="turma_id">Selecione a Turma:</label>
        <select id="turma_id" name="turma_id" required>
            <option value="">-- Escolha uma turma --</option>
            <?php
                    $sql_turmas = "SELECT id, nome_turma FROM turmas ORDER BY nome_turma ASC";
                    $resultado_salas = $conn->query($sql_turmas);
                     if ($resultado_salas->rowCount() > 0) {
                          
                            while($sala = $resultado_salas->fetch(PDO::FETCH_ASSOC)) {
    echo "<option value='" . htmlspecialchars($sala['id'])."'>" . htmlspecialchars($sala['nome_turma']) . "</option>";
                            }
                        } else {
                           
                            echo "<option value=''>Nenhuma sala cadastrada</option>";
                        }
                    ?>
        </select>
  
            </div>

                 <div class="form-group">
                    <label for="professor">Professor:</label>
                    <select id="professor" name="professor_id" required>
                        <option value="">Selecione um professor</option>
                        <?php
                        try {
                            $sql_prof = "SELECT id, nome FROM professores ORDER BY nome ASC";
                            $resultado_prof = $conn->query($sql_prof);
                            if ($resultado_prof->rowCount() > 0) {
                                while($prof = $resultado_prof->fetch(PDO::FETCH_ASSOC)) {
                                  
                                    echo "<option value='" . htmlspecialchars($prof['id']) . "'>" . htmlspecialchars($prof['nome']) . "</option>";
                                }
                            } else {
                                
                                echo "<option value=''>Nenhum professor cadastrado</option>";
                            }
                        } catch (PDOException $e) {
                            echo "<option value=''>Erro ao carregar professores</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="data">Data:</label>
                    <input type="date" id="data" name="data_aula" required>
                </div>

                <div class="form-group">
                    <label for="hora_inicio">Hora de Início:</label>
                    <input type="time" id="hora_inicio" name="hora_inicio" required>
                </div>

                <div class="form-group">
                    <label for="hora_fim">Hora de Fim:</label>
                    <input type="time" id="hora_fim" name="hora_fim" required>
                </div>

                <button type="submit" class="submit-button">Cadastrar Aula</button>
            </form>
        </div>
    </main>
</body>
</html>