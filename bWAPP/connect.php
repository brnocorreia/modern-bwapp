<?php

/*

bWAPP, or a buggy web application, is a free and open source deliberately insecure web application.
It helps security enthusiasts, developers and students to discover and to prevent web vulnerabilities.
bWAPP covers all major known web vulnerabilities, including all risks from the OWASP Top 10 project!
It is for security-testing and educational purposes only.

Enjoy!

Malik Mesellem
Twitter: @MME_IT

bWAPP is licensed under a Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License (http://creativecommons.org/licenses/by-nc-nd/4.0/). Copyright © 2014 MME BVBA. All rights reserved.

*/

// Connection settings
include("config.inc.php"); // This file should define $server, $username, $password, $database

// Connects to the server and selects the database using mysqli
// The database name is passed as the fourth argument to mysqli_connect
$link = mysqli_connect($server, $username, $password, $database);

// Checks the connection
if(!$link)
{
    // @mail($recipient, "Could not connect to server/database: ", mysqli_connect_error()); // Use mysqli_connect_error() for connection issues
    
    // mysqli_connect_error() provides a description of the connection error.
    // It's more specific than mysqli_error($link) before a successful connection.
    die("Could not connect to the server or database: " . mysqli_connect_error() . " (Error No: " . mysqli_connect_errno() . ")");
}

// No need for a separate mysql_select_db as the database is selected by mysqli_connect()
// The old code was:
// $database_selected = mysql_select_db($database, $link); // Note: original code re-used $database variable here for the result.
// if(!$database_selected)
// {
// die("Could not connect to the database: " . mysql_error());
// }

// Optional: Set character set (highly recommended for modern applications)
if (!mysqli_set_charset($link, "utf8mb4")) {
    // For bWAPP, you might not need to die here, but it's good to be aware.
    // printf("Error loading character set utf8mb4: %s\n", mysqli_error($link));
    // For simplicity in bWAPP, we might omit dying on charset error, but log or display it.
}

// The connection $link (which is now a mysqli object) is ready to be used by other scripts.
// Do not close the connection here if other scripts need it:
// mysqli_close($link); 

?>