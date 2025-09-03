<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    $_SESSION['erro'] = "Você não está logado! Por favor, faça o login.";
    header("Location: login.php");
    exit();
}

include_once("templates/header.php");
?>
<main class="form-container">
<form method="post" class="form-card">
    <h2>Cadastrar Turma</h2>
    <label for="nome">Sala:</label>
    <input type="text" id="nome" name="n_sala" required>
    
    <label for="aula">Aula:</label>
    <input type="number" id="aula" name="aula" required>
    
    <label for="data">Data:</label>
    <input type="date" id="data" name="data" required>
    
    <label for="dia">Dia da Semana:</label>
    <select id="dia" name="dia" required>
        <option value="segunda">Segunda-feira</option>
        <option value="terca">Terça-feira</option>
        <option value="quarta">Quarta-feira</option>
        <option value="quinta">Quinta-feira</option>
        <option value="sexta">Sexta-feira</option>
        <option value="sabado">Sábado</option>
        <option value="domingo">Domingo</option>
    </select>
    
    <label for="periodo">Período:</label>
    <select id="periodo" name="periodo" required>
        <option value="manha">Manhã</option>
        <option value="tarde">Tarde</option>
        <option value="noite">Noite</option>
    </select>
    
    <button type="submit" class="submit-button">Cadastrar</button>
</form>
</main>
<?php 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    include "conexao.php";
        
    $n_sala = $_POST["n_sala"];
    $aula = $_POST["aula"];
    $data = $_POST["data"];
    $dia = $_POST["dia"];
    $periodo = $_POST["periodo"];

    if((isset($_POST["n_sala"])  && isset($_POST["data"]) && isset($_POST["periodo"]))) {
        $n_sala_check = $_POST["n_sala"];
        $data_check = $_POST["data"];
        $periodo_check = $_POST["periodo"];

        $check_sql = "SELECT COUNT(*) FROM salas WHERE n_sala = :n_sala AND data = :data AND periodo = :periodo";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bindParam(":n_sala", $n_sala_check);
        $check_stmt->bindParam(":data", $data_check);
        $check_stmt->bindParam(":periodo", $periodo_check);
        $check_stmt->execute();
        $count = $check_stmt->fetchColumn();

        if ($count > 0) {
            echo "<script>alert('Já existe uma aula cadastrada para esta sala, dia e período.');</script>";
            exit();
        }
        else {
            $sql = "INSERT INTO salas (n_sala, aula, data, dia, periodo) VALUES (:n_sala, :aula, :data, :dia, :periodo)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":n_sala", $n_sala);
            $stmt->bindParam(":aula", $aula);
            $stmt->bindParam(":data", $data);
            $stmt->bindParam(":dia", $dia);
            $stmt->bindParam(":periodo", $periodo);
        
            if ($stmt->execute()) {
                echo "<script>alert('Sala cadastrada com sucesso!');</script>";
                exit();
            } else {
                echo "<script>alert('Erro ao cadastrar sala.');</script>";
            }
        }
    }


}