<?php
// FULL RESET: Drop all tenant and central DBs, recreate clean
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', 'root');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Drop all tenant databases  
$result = $pdo->query("SHOW DATABASES LIKE 'tenant_%'");
while ($row = $result->fetch(PDO::FETCH_NUM)) {
    echo "Dropping tenant DB: {$row[0]}\n";
    $pdo->exec("DROP DATABASE `{$row[0]}`");
}

// Drop and recreate central database
echo "Dropping central DB...\n";
$pdo->exec("DROP DATABASE IF EXISTS `simpleakunting_central`");
echo "Creating fresh central DB...\n";
$pdo->exec("CREATE DATABASE `simpleakunting_central` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "Done! Central DB is clean.\n";
