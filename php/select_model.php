<?php

/* VSTENO - Vector Steno Tool with Enhanced Notational Options
 * (c) 2018 - Marcel Maci (m.maci@gmx.ch)
 
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <http://www.gnu.org/licenses/>.
 */

require "vsteno_template_top.php"; 
require_once "session.php"; 
$_SESSION['return_address'] = "input.php";
require_once "import_model.php";

if ($_POST['model_name'] === GetDBUserModelName()) {
    $_SESSION['model_standard_or_custom'] = "custom";
    $_SESSION['actual_model'] = GetDBUserModelName();
} else {
    $_SESSION['model_standard_or_custom'] = "standard";
    $_SESSION['selected_std_model'] = $_POST['model_name'];
    $_SESSION['actual_model'] = $_POST['model_name']; // needed for input form
}
$model_name = $_SESSION['selected_std_model'];
$model_purgatorium = GetDBName( "purgatorium" );
$model_elysium = GetDBName( "elysium" );
$model_olympus = GetDBName( "olympus" );

// load model and initialize session variables
$text_to_parse = LoadModelFromDatabase($model_name);
$output = StripOutComments($text_to_parse);
$output = StripOutTabsAndNewlines($output);
$header_section = GetSection($output, "header");
$session_subsection = GetSubSection($header_section, "session");
ImportSession(); // apparently, I wrote a function for that in the past ... good to discover that again ... :) :) :)

// show selected model
echo "<h1>Standard</h1><p>Modell $model_name ($model_purgatorium/$model_elysium/$model_olympus) gewählt.</p>";
echo '<a href="input.php"><br><button>zurück</button></a></p>';

require "vsteno_template_bottom.php"; 
?>