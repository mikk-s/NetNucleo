<?php
session_start();
require "conexao.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

include_once("templates/header.php");

// Determina qual aba está ativa. O padrão é 'aulas'.
$aba_ativa = isset($_GET['aba']) ? $_GET['aba'] : 'aulas';

// Prepara variáveis para o título e a busca no banco
$titulo_aba = '';
$itens_para_excluir = [];
$erro = '';

try {
    if ($aba_ativa == 'professores') {
        $titulo_aba = 'Professores';
        // Busca todos os professores distintos que existem na tabela de aulas
        $itens_para_excluir = $conn->query("SELECT DISTINCT professor AS nome FROM aulass ORDER BY professor")->fetchAll(PDO::FETCH_ASSOC);

    } elseif ($aba_ativa == 'disciplinas') {
        $titulo_aba = 'Disciplinas (Turmas)';
        // Busca todas as disciplinas distintas
        $itens_para_excluir = $conn->query("SELECT DISTINCT disciplina AS nome FROM aulass ORDER BY disciplina")->fetchAll(PDO::FETCH_ASSOC);

    } else { // Aba padrão 'aulas'
        $titulo_aba = 'Aulas Individuais';
        // Busca todas as aulas para exclusão individual
        $itens_para_excluir = $conn->query("SELECT id, data, disciplina, professor, n_sala FROM aulass ORDER BY data DESC")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $erro = "Erro ao carregar dados: " . $e->getMessage();
}
//baila tu cuerpo alegria macarena 
/*Hey, boy, I'ma get ya
I'ma get you real good and I bet ya (it's Megan Thee Stallion)
Hey, boy, once I get ya
You'll be, oh, so glad that I met ya (and TWICE) (ow!)

Step one, do my highlight
Make me shine so bright in the moonlight
Step two, silhouette tight
Baby, even my shadow looks good, right?
Step three, when I arrive
Make you look my way with your heart eyes
Step four, got you on the floor
Make you say: More, more, more

When I say: Hi
I'm feeling all your attention on me
Hi, no reason to be so shy with me
I ain't gonna bite, come on over (no)
I know you wanna move a little closer (yeah)
I got a plan to get you with me

I got you on my radar, soon you're gonna be with me
My strategy, strategy will get ya, get ya, baby
Winning is my trademark, soon you'll never wanna leave
My strategy, strategy will get ya, get ya, baby

Hey, boy, I'ma get ya
I'ma get you real good and I bet ya
Hey, boy, once I get ya
You'll be, oh, so glad that I met ya

When your cheeks go red (that's cute)
I wanna dance, you said (oh, cool)
Till I'm in your head (it's cruel)
And you can't forget

You're feeling things now and you're confused
Watching my body getting loose
You don't know what you're gonna do
You're mine

When I say: Hi
I'm feeling all your attention on me
Hi, no reason to be so shy with me
I ain't gonna bite, come on over (no)
I know you wanna move a little closer (yeah)
I got a plan to get you with me

I got you on my radar, soon you're gonna be with me
My strategy, strategy will get ya, get ya, baby
Winning is my trademark, soon you'll never wanna leave
My strategy, strategy will get ya, get ya, baby (real hot girl, shh)

Do you like that? (Huh?)
When I smack it and you watch it bounce it right back?
He really lost it when he saw me do the right, left (hmm)
I'm a man eater, you just a light snack (baow)
I got him pressed like he's workin' on his triceps (hmm)
I'ma flirt, I'ma tease, they be hurt after me
Told him: Baby, what's a player to the G-A-M-E?
Sand need to worry 'bout him bringin' me to the beach
Jealous? Who? Girl, please

Left, right, left, right, do it to the beat (do it to the beat)
Talk with my body, that's my strategy (baow, baow)
Other girls try, but I'm really hard to beat (I'm really hard to beat)
He'll be mine off my strategy (yeah, yeah)
Left, right, left, right, do it to the beat (do it to the beat)
Talk with my body, that's my strategy (baow, baow)
Other girls try, but I'm really hard to beat (yeah, I'm really hard to beat)
He'll be mine off my strategy (yeah, yeah)

My strategy, strategy
Like gravity, gravity
One look at me, look at me
I bet ya, bet ya, bet ya, boy
You'll be down on your knees
Calling me up, begging me: Don't leave
My strategy, strategy will get ya, get ya, get ya, boy (ah-ooh)

Hey, boy, I'ma get ya
I'ma get you real good and I bet ya (real good and I bet ya)
Hey, boy, once I get ya
You'll be, oh, so glad that I met ya (oh, so glad that I, ooh)

Hey, boy, I'ma get ya (hey, boy, yeah)
I'ma get you real good and I bet ya (good and I bet ya)
Hey, boy, once I get ya (hey, boy)
You'll be, oh, so glad that I met ya (oh, so glad that I met ya)

Hey, boy, I'ma get ya
I'ma get you real good and I bet ya */
?>
<link rel="stylesheet" href="css/style.css">
<style>
    /* Estilos simples para as abas de navegação */
    .nav-abas { margin-bottom: 20px; border-bottom: 2px solid #ccc; padding-bottom: 10px; }
    .nav-abas a { padding: 10px 15px; text-decoration: none; color: #0056b3; border: 1px solid transparent; }
    .nav-abas a.active { font-weight: bold; border: 1px solid #ccc; border-bottom: 2px solid white; background-color: white; position: relative; top: 3px; }
</style>

<main class="form-container">
    <div class="form-card">
        <h2>Área de Exclusão</h2>

        <nav class="nav-abas">
            <a href="excluir.php?aba=aulas" class="<?php echo ($aba_ativa == 'aulas') ? 'active' : ''; ?>">Excluir Aulas</a>
            <a href="excluir.php?aba=professores" class="<?php echo ($aba_ativa == 'professores') ? 'active' : ''; ?>">Excluir Professores</a>
            <a href="excluir.php?aba=disciplinas" class="<?php echo ($aba_ativa == 'disciplinas') ? 'active' : ''; ?>">Excluir Disciplinas</a>
        </nav>

        <h3><?php echo $titulo_aba; ?></h3>

        <?php if (isset($_SESSION['mensagem'])): ?>
            <div class="message"><?php echo $_SESSION['mensagem']; ?></div>
            <?php unset($_SESSION['mensagem']); ?>
        <?php endif; ?>

        <?php if ($erro): ?>
            <p class="message error"><?php echo $erro; ?></p>
        <?php elseif (empty($itens_para_excluir)): ?>
            <p>Nenhum item para excluir encontrado.</p>
        <?php else: ?>
            <table>
                <thead>
                    <?php if ($aba_ativa == 'aulas'): ?>
                        <tr><th>Data</th><th>Disciplina</th><th>Professor</th><th>Ação</th></tr>
                    <?php else: ?>
                        <tr><th>Nome</th><th>Ação</th></tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                    <?php foreach ($itens_para_excluir as $item): ?>
                        <tr>
                            <?php if ($aba_ativa == 'aulas'): ?>
                                <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($item['data']))); ?></td>
                                <td><?php echo htmlspecialchars($item['disciplina']); ?></td>
                                <td><?php echo htmlspecialchars($item['professor']); ?></td>
                                <td>
                                    <form action="excluir_aulas.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta aula?');">
                                        <input type="hidden" name="id_aula" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn-excluir">Excluir</button>
                                    </form>
                                </td>
                            <?php elseif ($aba_ativa == 'professores'): ?>
                                <td><?php echo htmlspecialchars($item['nome']); ?></td>
                                <td>
                                    <form action="excluir_professores.php" method="POST" onsubmit="return confirm('ATENÇÃO: Isso apagará TODAS as aulas deste professor. Deseja continuar?');">
                                        <input type="hidden" name="nome_professor" value="<?php echo htmlspecialchars($item['nome']); ?>">
                                        <button type="submit" class="btn-excluir">Excluir Professor</button>
                                    </form>
                                </td>
                            <?php elseif ($aba_ativa == 'disciplinas'): ?>
                                <td><?php echo htmlspecialchars($item['nome']); ?></td>
                                <td>
                                    <form action="excluir_disciplinas.php" method="POST" onsubmit="return confirm('ATENÇÃO: Isso apagará TODAS as aulas desta disciplina. Deseja continuar?');">
                                        <input type="hidden" name="nome_disciplina" value="<?php echo htmlspecialchars($item['nome']); ?>">
                                        <button type="submit" class="btn-excluir">Excluir Disciplina</button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>
</html>xonauta marina e zico, junto com nossos amigos eeeeeeeee o