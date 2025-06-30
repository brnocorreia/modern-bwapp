<?php

/**
 * bWAPP, or a buggy web application, is a free and open source deliberately insecure web application.
 * It helps security enthusiasts, developers and students to discover and to prevent web vulnerabilities.
 * bWAPP covers all major known web vulnerabilities, including all risks from the OWASP Top 10 project!
 * It is for security-testing and educational purposes only.
 *
 * Enjoy!
 *
 * Malik Mesellem
 * Twitter: @MME_IT
 *
 * bWAPP is licensed under a Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License (http://creativecommons.org/licenses/by-nc-nd/4.0/).
 * Copyright © 2014 MME BVBA. All rights reserved.
 *
 * ---
 * MODERNIZED FOR PHP 8.2
 * ---
 * - Uses exception handling for database errors.
 * - Cleans up SQL queries using Heredoc syntax.
 * - Simplifies installation logic.
 */

// Ativa a exibição de erros para fins de depuração durante a instalação.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Variáveis de estado iniciais
$message = 'Clique <a href="install.php?install=yes">aqui</a> para instalar o bWAPP.';
$installationSuccess = false;

// Verifica se o parâmetro de instalação foi passado via GET
if (isset($_GET["install"]) && $_GET["install"] === "yes") {
    
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        include("config.inc.php");

        $db_connection = new mysqli($server, $username, $password);

        $db_connection->query("CREATE DATABASE IF NOT EXISTS bWAPP CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        $db_connection->select_db("bWAPP");
        
        $sql_users = <<<SQL
        CREATE TABLE IF NOT EXISTS users (
            id INT(10) NOT NULL AUTO_INCREMENT,
            login VARCHAR(100) DEFAULT NULL,
            password VARCHAR(100) DEFAULT NULL,
            email VARCHAR(100) DEFAULT NULL,
            secret VARCHAR(100) DEFAULT NULL,
            activation_code VARCHAR(100) DEFAULT NULL,
            activated TINYINT(1) DEFAULT '0',
            reset_code VARCHAR(100) DEFAULT NULL,
            admin TINYINT(1) DEFAULT '0',
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        SQL;
        $db_connection->query($sql_users);

        $sql_blog = <<<SQL
        CREATE TABLE IF NOT EXISTS blog (
            id INT(10) NOT NULL AUTO_INCREMENT,
            owner VARCHAR(100) DEFAULT NULL,
            entry VARCHAR(500) DEFAULT NULL,
            date DATETIME DEFAULT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        SQL;
        $db_connection->query($sql_blog);
        
        $sql_visitors = <<<SQL
        CREATE TABLE IF NOT EXISTS visitors (
            id INT(10) NOT NULL AUTO_INCREMENT,
            ip_address VARCHAR(50) DEFAULT NULL,
            user_agent VARCHAR(500) DEFAULT NULL,
            date DATETIME DEFAULT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        SQL;
        $db_connection->query($sql_visitors);

        $sql_movies = <<<SQL
        CREATE TABLE IF NOT EXISTS movies (
            id INT(10) NOT NULL AUTO_INCREMENT,
            title VARCHAR(100) DEFAULT NULL,
            release_year VARCHAR(100) DEFAULT NULL,
            genre VARCHAR(100) DEFAULT NULL,
            main_character VARCHAR(100) DEFAULT NULL,
            imdb VARCHAR(100) DEFAULT NULL,
            tickets_stock INT(10) DEFAULT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        SQL;
        $db_connection->query($sql_movies);

        $sql_heroes = <<<SQL
        CREATE TABLE IF NOT EXISTS heroes (
            id INT(10) NOT NULL AUTO_INCREMENT,
            login VARCHAR(100) DEFAULT NULL,
            password VARCHAR(100) DEFAULT NULL,
            secret VARCHAR(100) DEFAULT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        SQL;
        $db_connection->query($sql_heroes);
        
        $db_connection->query("TRUNCATE TABLE users");
        $db_connection->query("TRUNCATE TABLE movies");
        $db_connection->query("TRUNCATE TABLE heroes");
        
        $sql_insert_users = <<<SQL
        INSERT INTO users (login, password, email, secret, activation_code, activated, reset_code, admin) VALUES
        ('A.I.M.', '6885858486f31043e5839c735d99457f045affd0', 'bwapp-aim@mailinator.com', 'A.I.M. or Authentication Is Missing', NULL, 1, NULL, 1),
        ('bee', '6885858486f31043e5839c735d99457f045affd0', 'bwapp-bee@mailinator.com', 'Any bugs?', NULL, 1, NULL, 1);
        SQL;
        $db_connection->query($sql_insert_users);
        
        $sql_insert_movies = <<<SQL
        INSERT INTO movies (title, release_year, genre, main_character, imdb, tickets_stock) VALUES 
        ('G.I. Joe: Retaliation', '2013', 'action', 'Cobra Commander', 'tt1583421', 100),
        ('Iron Man', '2008', 'action', 'Tony Stark', 'tt0371746', 53),
        ('Man of Steel', '2013', 'action', 'Clark Kent', 'tt0770828', 78),
        ('Terminator Salvation', '2009', 'sci-fi', 'John Connor', 'tt0438488', 100),
        ('The Amazing Spider-Man', '2012', 'action', 'Peter Parker', 'tt0948470', 13),
        ('The Cabin in the Woods', '2011', 'horror', 'Some zombies', 'tt1259521', 666),
        ('The Dark Knight Rises', '2012', 'action', 'Bruce Wayne', 'tt1345836', 3),
        ('The Fast and the Furious', '2001', 'action', 'Brian O\'Connor', 'tt0232500', 40),
        ('The Incredible Hulk', '2008', 'action', 'Bruce Banner', 'tt0800080', 23),
        ('World War Z', '2013', 'horror', 'Gerry Lane', 'tt0816711', 0);
        SQL;
        $db_connection->query($sql_insert_movies);

        $sql_insert_heroes = <<<SQL
        INSERT INTO heroes (login, password, secret) VALUES
        ('neo', 'trinity', 'Oh why didn\'t I took that BLACK pill?'),
        ('alice', 'loveZombies', 'There\'s a cure!'),
        ('thor', 'Asgard', 'Oh, no... this is Earth... isn\'t it?'),
        ('wolverine', 'Log@N', 'What\'s a Magneto?'),
        ('johnny', 'm3ph1st0ph3l3s', 'I\'m the Ghost Rider!'),
        ('seline', 'm00n', 'It wasn\'t the Lycans. It was you.');
        SQL;
        $db_connection->query($sql_insert_heroes);
        
        // Se tudo correu bem, define a mensagem de sucesso
        $message = "bWAPP foi instalado com sucesso! As tabelas foram criadas e populadas.";
        $installationSuccess = true;
        
        // Fecha a conexão
        $db_connection->close();

    } catch (mysqli_sql_exception $e) {
        // Se qualquer operação de banco de dados falhar, captura a exceção
        $message = '<font color="red">A instalação falhou. Erro: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</font>';
    } catch (Exception $e) {
        // Captura outras exceções genéricas (ex: falha ao incluir o config.inc.php)
        $message = '<font color="red">Ocorreu um erro inesperado: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</font>';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="stylesheets/stylesheet.css" media="screen" />
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
    <title>bWAPP - Instalação</title>
</head>
<body>

<header>
    <h1>bWAPP</h1>
    <h2>an extremely buggy web app !</h2>
</header>

<div id="menu">
    <table>
        <tr>
            <?php if ($installationSuccess): ?>
                <td><a href="login.php">Login</a></td>
                <td><a href="user_new.php">New User</a></td>
                <td><a href="info.php">Info</a></td>
                <td><a href="training.php">Talks & Training</a></td>
                <td><a href="http://itsecgames.blogspot.com" target="_blank">Blog</a></td>
            <?php else: ?>
                <td><font color="#ffb717">Instalar</font></td>
                <td><a href="info_install.php">Info</a></td>
                <td><a href="training_install.php">Talks & Training</a></td>
                <td><a href="http://itsecgames.blogspot.com" target="_blank">Blog</a></td>
            <?php endif; ?>
        </tr>
    </table>
</div>

<div id="main">
    <h1>Instalação</h1>
    <p><?= $message ?></p>
</div>

<div id="side">
    <a href="http://twitter.com/MME_IT" target="_blank" class="button"><img src="./images/twitter.png" alt="Twitter"></a>
    <a href="http://be.linkedin.com/in/malikmesellem" target="_blank" class="button"><img src="./images/linkedin.png" alt="LinkedIn"></a>
    <a href="http://www.facebook.com/pages/MME-IT-Audits-Security/104153019664877" target="_blank" class="button"><img src="./images/facebook.png" alt="Facebook"></a>
    <a href="http://itsecgames.blogspot.com" target="_blank" class="button"><img src="./images/blogger.png" alt="Blogger"></a>
</div>

<div id="disclaimer">
    <p>bWAPP is licensed under <a rel="license" href="http://creativecommons.org/licenses/by-nc-nd/4.0/" target="_blank"><img style="vertical-align:middle" src="./images/cc.png" alt="Creative Commons License"></a> &copy; 2014 MME BVBA / Follow <a href="http://twitter.com/MME_IT" target="_blank">@MME_IT</a> on Twitter and ask for our cheat sheet, containing all solutions! / Need an exclusive <a href="http://www.mmebvba.com" target="_blank">training</a>?</p>
</div>

<div id="bee">
    <img src="./images/bee_1.png" alt="bWAPP Bee">
</div>

</body>
</html>