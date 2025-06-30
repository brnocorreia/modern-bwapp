<?php

// Includes de segurança e configuração do bWAPP
include("security.php");
include("security_level_check.php");
include("selections.php");
include("functions_external.php");
// Assume-se que 'connect.php' foi atualizado para usar MySQLi e retorna um objeto $link
include("connect.php");

// A função de sanitização do bWAPP permanece a mesma
function sqli($data)
{
    switch ($_COOKIE["security_level"]) {
        case "0":
            $data = no_check($data);
            break;
        case "1":
            $data = sqli_check_1($data);
            break;
        case "2":
            $data = sqli_check_2($data);
            break;
        default:
            $data = no_check($data);
            break;
    }

    return $data;
}

// Variáveis para armazenar os resultados e mensagens
$movies = [];
$message = ""; // Para mensagens de erro ou status

// Processa o formulário apenas se for uma requisição POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'search') {
    
    // Ativa o modo de exceção do MySQLi para um tratamento de erros moderno
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $title_to_search = $_POST["title"] ?? "";

        // Aplica a função de "sanitização" do bWAPP para o nível de segurança atual
        // Esta é a parte que, dependendo do nível, permite a injeção de SQL
        $sanitized_title = sqli($title_to_search);

        // --- VULNERABILIDADE INTENCIONAL ---
        // A consulta abaixo concatena a entrada do usuário diretamente no SQL.
        // Isto é INTENCIONALMENTE VULNERÁVEL para os propósitos educacionais do bWAPP.
        // NÃO FAÇA ISSO EM CÓDIGO DE PRODUÇÃO.
        $sql = "SELECT * FROM movies WHERE title LIKE '%" . $sanitized_title . "%'";
        
        $result = $link->query($sql);
        $movies = $result->fetch_all(MYSQLI_ASSOC);

        if (empty($movies)) {
            $message = "Nenhum filme foi encontrado!";
        }
        
        // --- EXEMPLO DE CÓDIGO SEGURO (usando Prepared Statements) ---
        /*
        $sql_secure = "SELECT * FROM movies WHERE title LIKE ?";
        $stmt = $link->prepare($sql_secure);
        $search_param = "%" . $title_to_search . "%";
        $stmt->bind_param("s", $search_param);
        $stmt->execute();
        $result = $stmt->get_result();
        $movies = $result->fetch_all(MYSQLI_ASSOC);
        */
        // --- FIM DO EXEMPLO SEGURO ---

        $link->close();

    } catch (mysqli_sql_exception $e) {
        $message = '<font color="red">Erro no Banco de Dados: ' . htmlspecialchars($e->getMessage()) . '</font>';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" type="text/css" href="stylesheets/stylesheet.css" media="screen" />
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
    <script src="js/html5.js"></script>
    <title>bWAPP - SQL Injection</title>
</head>
<body>

<header>
    <h1>bWAPP</h1>
    <h2>an extremely buggy web app !</h2>
</header>

<div id="menu">
    <table>
        <tr>
            <td><a href="portal.php">Bugs</a></td>
            <td><a href="password_change.php">Change Password</a></td>
            <td><a href="user_extra.php">Create User</a></td>
            <td><a href="security_level_set.php">Set Security Level</a></td>
            <td><a href="reset.php" onclick="return confirm('All settings will be cleared. Are you sure?');">Reset</a></td>
            <td><a href="credits.php">Credits</a></td>
            <td><a href="http://itsecgames.blogspot.com" target="_blank">Blog</a></td>
            <td><a href="logout.php" onclick="return confirm('Are you sure you want to leave?');">Logout</a></td>
            <td><font color="red">Welcome <?= isset($_SESSION["login"]) ? htmlspecialchars(ucwords($_SESSION["login"]), ENT_QUOTES, 'UTF-8') : "" ?></font></td>
        </tr>
    </table>
</div>

<div id="main">
    <h1>SQL Injection (POST/Search)</h1>
    <form action="" method="POST">
        <p>
            <label for="title">Search for a movie:</label>
            <input type="text" id="title" name="title" size="25" value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8') : '' ?>">
            <button type="submit" name="action" value="search">Search</button>
        </p>
    </form>
    <br />
    <table id="table_yellow">
        <tr height="30" bgcolor="#ffb717" align="center">
            <td width="200"><b>Title</b></td>
            <td width="80"><b>Release</b></td>
            <td width="140"><b>Character</b></td>
            <td width="80"><b>Genre</b></td>
            <td width="80"><b>IMDb</b></td>
        </tr>
        <?php if (!empty($movies)): ?>
            <?php foreach ($movies as $movie): ?>
                <tr height="30">
                    <td><?= htmlspecialchars($movie["title"], ENT_QUOTES, 'UTF-8') ?></td>
                    <td align="center"><?= htmlspecialchars($movie["release_year"], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($movie["main_character"], ENT_QUOTES, 'UTF-8') ?></td>
                    <td align="center"><?= htmlspecialchars($movie["genre"], ENT_QUOTES, 'UTF-8') ?></td>
                    <td align="center"><a href="http://www.imdb.com/title/<?= htmlspecialchars($movie["imdb"], ENT_QUOTES, 'UTF-8') ?>" target="_blank">Link</a></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr height="30">
                <td colspan="5"><?= $message // Exibe a mensagem de 'nenhum filme encontrado' ou erro ?></td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<div id="side">
    <a href="http://twitter.com/MME_IT" target="blank_" class="button"><img src="./images/twitter.png" alt="Twitter"></a>
    <a href="http://be.linkedin.com/in/malikmesellem" target="blank_" class="button"><img src="./images/linkedin.png" alt="LinkedIn"></a>
    <a href="http://www.facebook.com/pages/MME-IT-Audits-Security/104153019664877" target="blank_" class="button"><img src="./images/facebook.png" alt="Facebook"></a>
    <a href="http://itsecgames.blogspot.com" target="blank_" class="button"><img src="./images/blogger.png" alt="Blogger"></a>
</div>
<div id="disclaimer">
    <p>bWAPP is licensed under <a rel="license" href="http://creativecommons.org/licenses/by-nc-nd/4.0/" target="_blank"><img style="vertical-align:middle" src="./images/cc.png" alt="Creative Commons"></a> &copy; 2014 MME BVBA / Follow <a href="http://twitter.com/MME_IT" target="_blank">@MME_IT</a> on Twitter and ask for our cheat sheet, containing all solutions! / Need an exclusive <a href="http://www.mmebvba.com" target="_blank">training</a>?</p>
</div>
<div id="bee">
    <img src="./images/bee_1.png" alt="bWAPP Bee">
</div>
<div id="security_level">
    <form action="" method="POST">
        <label>Set your security level:</label><br />
        <select name="security_level">
            <option value="0">low</option>
            <option value="1">medium</option>
            <option value="2">high</option>
        </select>
        <button type="submit" name="form_security_level" value="submit">Set</button>
        <font size="4">Current: <b><?= htmlspecialchars($security_level, ENT_QUOTES, 'UTF-8') ?></b></font>
    </form>
</div>
<div id="bug">
    <form action="" method="POST">
        <label>Choose your bug:</label><br />
        <select name="bug">
        <?php
        // Lists the options from the array 'bugs' (bugs.txt)
        foreach ($bugs as $key => $value) {
            $bug = explode(",", trim($value));
            // Usando htmlspecialchars para evitar XSS no nome do bug
            echo "<option value='" . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($bug[0], ENT_QUOTES, 'UTF-8') . "</option>";
        }
        ?>
        </select>
        <button type="submit" name="form_bug" value="submit">Hack</button>
    </form>
</div>

</body>
</html>