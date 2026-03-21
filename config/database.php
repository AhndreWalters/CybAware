<?php
$env = parse_ini_file(dirname(__DIR__) . '/.env');

$link = mysqli_connect(
    $env['DB_SERVER'],
    $env['DB_USERNAME'],
    $env['DB_PASSWORD'],
    $env['DB_NAME'],
    $env['DB_PORT']
);

if ($link === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>