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
    $n_sala = $_POST['n_sala'];
    $disciplina = $_POST['disciplina'];
    $professor = $_POST['professor'];
    $data = $_POST['data'];
    $dia = $_POST['dia'];
    $periodo = $_POST['periodo'];

    if (empty($n_sala) || empty($disciplina) || empty($professor) || empty($data) || empty($dia) || empty($periodo)) {
        $erro_cadastro = "Por favor, preencha todos os campos.";
    } else {
        try {
        
            $stmt = $conn->prepare("INSERT INTO aulass (n_sala,disciplina, professor, data, dia, periodo) VALUES (:n_sala, :disciplina, :professor,  :data, :dia, :periodo)");

           
            $stmt->bindParam(':n_sala', $n_sala);
            $stmt->bindParam(':disciplina', $disciplina);
            $stmt->bindParam(':professor', $professor);
            $stmt->bindParam(':data', $data);
            $stmt->bindParam(':dia', $dia);
            $stmt->bindParam(':periodo', $periodo);

            $stmt->execute();
            $sucesso_cadastro = "Aula cadastrada com sucesso!";
            exit();
        } catch (PDOException $e) {
            $erro_cadastro = "Erro ao cadastrar aula: " . $e->getMessage();
        }
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
                    <select id="n_sala" name="n_sala" required>
                        <option value="">Selecione uma sala</option>"></option>
                    <?php
                    $sql_salas = "SELECT n_sala, bloco FROM salas ORDER BY bloco ASC";

                    $resultado_salas = $conn->query($sql_salas);
                     if ($resultado_salas->rowCount() > 0) {
                          
                            while($sala = $resultado_salas->fetch(PDO::FETCH_ASSOC)) {
 echo "<option value='" . htmlspecialchars($sala['n_sala']) . " - Bloco " . htmlspecialchars($sala['bloco']) . "'>" . htmlspecialchars($sala['n_sala']) . " - Bloco " . htmlspecialchars($sala['bloco']) . "</option>";
                            }
                        } else {
                           
                            echo "<option value=''>Nenhuma sala cadastrada</option>";
                        }
                    ?>
                    </select>
                </div>

                <div class="form-group">
                <label for="disciplina">Disciplina:</label>
                <input type="text" id="disciplina" name="disciplina" required>
            </div>

                 <div class="form-group">
                    <label for="professor">Professor:</label>
                    <select id="professor" name="professor" required>
                        <option value="">Selecione um professor</option>
                        <?php
                        try {
                            $sql_prof = "SELECT nome FROM usuarios ORDER BY nome ASC";
                            $resultado_prof = $conn->query($sql_prof);
                            if ($resultado_prof->rowCount() > 0) {
                                while($prof = $resultado_prof->fetch(PDO::FETCH_ASSOC)) {
                                  
                                    echo "<option value='" . htmlspecialchars($prof['nome']) . "'>" . htmlspecialchars($prof['nome']) . "</option>";
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
                    <input type="date" id="data" name="data" required>
                </div>

                <div class="form-group">
                    <label for="dia">Dia da Semana:</label>
                    <select id="dia" name="dia" required>
                        <option value="">Selecione um dia</option>
                        <option value="Segunda-feira">Segunda-feira</option>
                        <option value="Terça-feira">Terça-feira</option>
                        <option value="Quarta-feira">Quarta-feira</option>
                        <option value="Quinta-feira">Quinta-feira</option>
                        <option value="Sexta-feira">Sexta-feira</option>
                        <option value="Sábado">Sábado</option>
                        <option value="Domingo">Domingo</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="periodo">Período:</label>
                    <select id="periodo" name="periodo" required>
                        <option value="">Selecione um período</option>
                        <option value="Manhã">Manhã</option>
                        <option value="Tarde">Tarde</option>
                        <option value="Noite">Noite</option>
                    </select>
                </div>

                <button type="submit" class="submit-button">Cadastrar Aula</button>
            </form>
        </div>
    </main>
</body>
</html>