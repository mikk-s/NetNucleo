<?php
include_once("helpers/url.php");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NetNucleo</title>

 
  <link rel="stylesheet" href="<?= $BASE_URL ?>css/style.css">

  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <script src="script.js"></script>
    <header class="site-header">
        <div class="header-container">
 <h1 class="site-title">NETNÚCLEO - HORTO</h1>
        </div>

<?php if (isset($_SESSION["usuario"])): ?>
 <div class="header-menu">

 <div class="nav-left"></div>

 <div class="nav-center">
 <ul>
 <li class="CadButton">
 <p>Cadastros</p>
 <div class="dropdown-content1">
 <a href="<?= $BASE_URL ?>cadastro.php">Professores</a>
 <a href="<?= $BASE_URL ?>cadastro_salas.php">Salas</a>
 <a href="<?= $BASE_URL ?>cadastro_turmas.php">Turmas</a>
 </div>
 </li>
 <li class="VisButton">
 <p>Consultas</p>
 <div class="dropdown-content2">
 <a href="<?= $BASE_URL ?>visualizar_professores.php">Professores</a>
 <a href="<?= $BASE_URL ?>visualizar_salas.php">Salas</a>
 <a href="<?= $BASE_URL ?>visualizar_turmas.php">Turmas</a>
 <a href="<?= $BASE_URL ?>visualizar_atividades.php">Atividades</a>
 </div>
 </li>
 <li class="ExButton">
 <p>Excluir</p>
 <div class="dropdown-content3">
 <a href="<?= $BASE_URL ?>excluir_professores.php">Excluir Professores</a>
 <a href="<?= $BASE_URL ?>excluir_salas.php">Excluir Salas</a>
 <a href="<?= $BASE_URL ?>excluir_turmas.php">Excluir Turmas</a>
 <a href="<?= $BASE_URL ?>excluir_atividades.php">Excluir Atividades</a>
 </div>
 </li>
 <li class="SairButton">
 <a href="<?= $BASE_URL ?>deslogar.php"> <p>Sair</p></a>
 </li>
 </ul>
 </div>

 <div class="nav-right">
 <span class="login-status">Logado como <?=$_SESSION["usuario"]?></span>
 </div>

 </div>
<?php endif; ?>
    </header>
</body>
</html>