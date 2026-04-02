<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
$pdo->exec('CREATE DATABASE IF NOT EXISTS khoaluanbe_dev;');
echo "Database created successfully.";
