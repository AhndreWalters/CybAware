<?php
define('DB_SERVER', 'cybaware-mysql-db-cybaware.l.aivencloud.com');
define('DB_NAME', 'CybAwareDB');
define('DB_USERNAME', 'avnadmin');
define('DB_PASSWORD', 'AVNS_CtY4DwoGW-hpgGDEtqs');
define('DB_PORT', '27855');
 
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
 
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>