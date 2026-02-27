<?php
/**
 * ============================================
 * PROCESSAMENTO DE LOGOUT
 * ============================================
 */

session_start();

// Destruir todas as variáveis de sessão
$_SESSION = array();

// Destruir a sessão
session_destroy();

// Redirecionar para login
header("Location: index.php");
exit;
?>
