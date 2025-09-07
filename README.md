NetNúcleo - Sistema de Gerenciamento de Aulas
NetNúcleo é uma aplicação web desenvolvida em PHP para o gerenciamento de cadastros de aulas, salas e professores. O sistema permite um controle organizado de agendamentos, com funcionalidades de cadastro, consulta e exclusão de registros.

Funcionalidades Principais
Autenticação de Usuários:

Sistema de login e cadastro de usuários (professores) com senhas criptografadas.

Sessões para proteger o acesso às páginas restritas.

Função de Logout para encerrar a sessão de forma segura.

Gerenciamento de Professores:

Cadastro de novos professores (usuários) no sistema.

Consulta da lista de professores cadastrados.

Exclusão de professores, com uma regra de segurança que impede a exclusão se o professor ainda tiver aulas associadas.

Gerenciamento de Salas:

Cadastro de novas salas com número e bloco.

Consulta das salas disponíveis.

Exclusão de salas cadastradas.

Gerenciamento de Aulas:

Cadastro de aulas, associando uma sala, disciplina, professor, data, dia e período.

Consulta de todas as aulas agendadas.

Exclusão de aulas individualmente ou todas as aulas de um professor específico.

Tecnologias Utilizadas
Backend: PHP 8

Frontend: HTML5, CSS3, JavaScript (Vanilla)

Banco de Dados: MySQL com PDO para conexão segura.

Servidor Local: WAMP (ou qualquer outro ambiente como XAMPP, MAMP).

Como Executar o Projeto
Siga os passos abaixo para configurar e rodar a aplicação em seu ambiente local.

Pré-requisitos
Ter um ambiente de servidor local instalado (WAMP, XAMPP, etc.) que suporte PHP e MySQL.

Um cliente de banco de dados, como o phpMyAdmin.

Passo 1: Clone o Repositório
Clone ou faça o download deste repositório para a pasta www (no WAMP) ou htdocs (no XAMPP) do seu servidor local.

Passo 2: Configure o Banco de Dados
Acesse o phpMyAdmin (ou seu cliente de preferência).

Crie um novo banco de dados com o nome netnucleo.

Selecione o banco netnucleo e execute as seguintes queries SQL para criar as tabelas necessárias:

-- Tabela para os usuários (professores)
CREATE TABLE `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(255) NOT NULL,
  `login` VARCHAR(100) NOT NULL UNIQUE,
  `senha` VARCHAR(255) NOT NULL
);

-- Tabela para as salas
CREATE TABLE `salas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `n_sala` VARCHAR(50) NOT NULL,
  `bloco` VARCHAR(10) NOT NULL
);

-- Tabela para as aulas agendadas
CREATE TABLE `aulass` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `n_sala` VARCHAR(50) NOT NULL,
  `disciplina` VARCHAR(255) NOT NULL,
  `professor` VARCHAR(255) NOT NULL,
  `data` DATE NOT NULL,
  `dia` VARCHAR(50) NOT NULL,
  `periodo` VARCHAR(50) NOT NULL
);

Passo 3: Configure a Conexão
Abra o arquivo conexao.php e verifique se as credenciais de acesso ao banco de dados correspondem à sua configuração local (por padrão, usuário: root, senha: "").

Passo 4: Acesse a Aplicação
Abra seu navegador e acesse a URL correspondente à pasta do projeto. Exemplo: http://localhost/NetNucleo-1/

A página inicial será a de login. Você pode criar um novo usuário na página de cadastro.

