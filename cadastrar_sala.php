<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    $_SESSION['erro'] = "Você não está logado! Por favor, faça o login.";
    header("Location: login.php");
    exit();
}
 
include_once("templates/header.php");
?>
<link rel="stylesheet" href="css/style.css">
<main class="form-container">
<form method="post" class="form-card">
    <h2>Cadastrar Sala</h2>
    <label for="nome">Sala:</label>
    <input type="text" id="nome" name="n_sala" required>
    
    <label for="bloco">Bloco:</label>
<select name="bloco" required>
    <option value="A" >A</option>
    <option value="B">B</option>
    <option value="C">C</option>
    <option value="D">D</option>

    </select>
   
    <button type="submit" class="submit-button">Cadastrar</button>
</form>
</main>
<?php 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    include "conexao.php";

    $n_sala = $_POST["n_sala"];
    $bloco = $_POST["bloco"];

    if((isset($_POST["n_sala"])  )) {
        $n_sala_check = $_POST["n_sala"];
      

        $check_sql = "SELECT COUNT(*) FROM salas WHERE n_sala = :n_sala AND bloco = :bloco";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bindParam(":n_sala", $n_sala_check);
        $check_stmt->bindParam(":bloco", $bloco);
        $check_stmt->execute();
        $count = $check_stmt->fetchColumn();

        if ($count > 0) {
            echo "<script>alert('Essa sala já existe.');</script>";
            exit();
        }
        else {
            $sql = "INSERT INTO salas (n_sala, bloco) VALUES (:n_sala, :bloco)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":n_sala", $n_sala); 
            $stmt->bindParam(":bloco", $bloco);
           
        
            if ($stmt->execute()) {
                echo "<script>alert('Sala cadastrada com sucesso!');</script>";
               
                
            } else {
                echo "<script>alert('Erro ao cadastrar sala.');</script>";
            }
        }
    }


}
?>