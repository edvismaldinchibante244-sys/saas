-- ============================================
-- CORRIGIR SENHA DO ADMIN MANUALMENTE
-- ============================================

-- Execute este comando no MySQL ou phpMyAdmin:

UPDATE usuarios 
SET senha = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE email = 'admin@sabormoz.co.mz';

-- Senha: admin123
