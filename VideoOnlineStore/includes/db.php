<?php
$host = "TABBYNEKO\SQLEXPRESS"; 
$db = "VideoOnlineStore"; 
$user = "sa"; 
$pass = "123456"; 


$dsn = "sqlsrv:server=$host;Database=$db";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
