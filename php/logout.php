<?php session_start();
// terminate session with normal display after logout
// do this operation without testing password
$_SESSION['display_mode'] = "normal";
$_SESSION['token_color'] = "black";
$_SESSION['title_color'] = "black";
$_SESSION['introduction_color'] = "black";
$_SESSION['output_page_number_color'] = "black";
$_SESSION['output_line_number_color'] = "black";

require_once "vsteno_template_top.php";
require_once "session.php";

$_SESSION['user_logged_in'] = false;
$_SESSION['user_username'] = "";
$_SESSION['user_privilege'] = 0;
$_SESSION['model_standard_or_custom'] = "standard";

?>
<h1>Ausloggen</h1>
<p>Sie haben sich ausgeloggt.</p>
<a href="input.php"><button>zurück</button></a><br><br>   

<?php
require_once "vsteno_template_bottom.php";

?>