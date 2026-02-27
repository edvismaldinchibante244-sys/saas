<?php

/**
 * ============================================
 * PROTEÇÃO DE ROTAS - SUPER ADMIN
 * Incluir esse arquivo em páginas exclusivas do Super Admin
 * ============================================
 */

session_start();

// Verificar se usuário está logado
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: ../index.php");
    exit;
}

// Verificar se é super admin
if (!isset($_SESSION['super_admin']) || $_SESSION['super_admin'] !== 1) {
    header("HTTP/1.0 403 Forbidden");
    echo "Acesso negado! Apenas Super Admin.";
    exit;
}

// Função para verificar se é super admin
function isSuperAdmin()
{
    return isset($_SESSION['super_admin']) && $_SESSION['super_admin'] === 1;
}
