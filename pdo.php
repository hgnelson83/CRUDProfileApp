<?php
$pdo = new PDO('mysql:host=localhost;port=3306;dbname=misc',
   'fred', 'zap');
// Error logs are located at: C:/MAMP/logs/php_error.log
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
