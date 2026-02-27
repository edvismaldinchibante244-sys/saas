<?php
session_start();
include_once '../config/database.php';

header('Content-Type: application/json');

echo json_encode([
    'session' => $_SESSION,
    'super_admin' => isset($_SESSION['super_admin']) ? $_SESSION['super_admin'] : 'não definido',
    'logado' => isset($_SESSION['logado']) ? $_SESSION['logado'] : 'não definido'
]);
