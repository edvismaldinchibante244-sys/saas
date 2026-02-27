<?php

/**
 * Debug - Verificar sessão atual
 */

session_start();

header('Content-Type: application/json');

echo json_encode([
    'session_exists' => session_status() === PHP_SESSION_ACTIVE,
    'session_id' => session_id(),
    'session_data' => $_SESSION,
    'cookie_params' => session_get_cookie_params()
], JSON_PRETTY_PRINT);
