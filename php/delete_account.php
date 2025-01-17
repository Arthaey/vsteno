<?php
require_once "vsteno_template_top.php";
require_once "session.php";

if (!$_SESSION['user_logged_in']) {
?>
<p>Sie sind nicht eingeloggt.</p>
<p>Um einen Benutzeraccount zu löschen müssen Sie eingeloggt sein. Zusätzlich müssen Sie Ihre Benutzerdaten noch einmal eingeben, um das Konto zu löschen.</p>
<a href="login.php"><button>zurück</button></a><br><br>
<?php } else { ?>
<h1>Einloggen</h1>
<p>Geben Sie Ihren Benutzernamen und Ihr Passwort ein:</p>
<form action="../php/delete_account_execute.php" method="post">
<table><tr><td>Login:<br>
Password:
</td>

<td> <input type="text" name="username"  size="30" value=""><br>
<input type="text" name="password"  size="30" value=""><br>
</td> 
</tr>
</table>
<p><b>ACHTUNG:</b><br>
<i>Wenn Sie auf den Button klicken, wird der <b>Account, Ihr Custom-Model und <u>alle</u> Wörterbücher</b>, die damit verbunden sind (Purgatorium und Elysium) <b>ohne Rückfrage und unwiderruflich</b> gelöscht!</b></i></p>
<input type="submit" name="action" value="löschen">
</form>
<?php
}

require_once "vsteno_template_bottom.php";
?>