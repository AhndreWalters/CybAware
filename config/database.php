<?php
/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
define('DB_SERVER', 'cybaware-mysql-db-cybaware.l.aivencloud.com');
define('DB_NAME', 'cybaware-db');
define('DB_USERNAME', 'avnadmin');
define('DB_PASSWORD', 'AVNS_CtY4DwoGW-hpgGDEtqs');
define('DB_PORT', '27855');
 
/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
 
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>