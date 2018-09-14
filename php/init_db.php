<?php 
/* this file can be used to create the tables necessary for vsteno database */
require_once "dbpw.php";

$conn = Connect2DB();

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "<br>");
}

// sql to create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
user_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(30) NOT NULL,
email VARCHAR(30),
pwhash VARCHAR(80),
privilege INT(1),
last_activity DATE,
reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table users created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// sql to create purgatorium table
$sql = "CREATE TABLE IF NOT EXISTS purgatorium (
word_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
word VARCHAR(30) NOT NULL,
std VARCHAR(50),
prt VARCHAR(50),
composed VARCHAR(50),
result CHAR(1),
user_id INT(6),
comment VARCHAR(250),
insertion_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table purgatorium created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// sql to create elysium table
$sql = "CREATE TABLE IF NOT EXISTS elysium (
word_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
word VARCHAR(30) NOT NULL,
number_forms INT(1),
recommended_form INT(1),
submitted_by INT(6),
reviewed_by INT(6),
single_bas VARCHAR(50),
single_std VARCHAR(50),
single_prt VARCHAR(50),
separated_bas VARCHAR(50),
separated_std VARCHAR(50),
separated_prt VARCHAR(50),
insertion_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table elysium created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

$conn->close();
?>
