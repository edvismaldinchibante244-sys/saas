<?php
/**
 * ============================================
 * PROTEÇÃO DE ROTAS
 * Incluir esse arquivo em todas as páginas protegidas
 * ============================================
 */

session_start();

// Verificar se usuário está logado
if(!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: index.php");
    exit;
}

// Verificar se a sessão ainda é válida (opcional - timeout de 2 horas)
if(isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

$_SESSION['last_activity'] = time();

// Função para verificar permissão por perfil
function checkPermission($perfis_permitidos) {
    if(!in_array($_SESSION['perfil'], $perfis_permitidos)) {
        header("HTTP/1.0 403 Forbidden");
        echo "Acesso negado!";
        exit;
    }
}
?>
