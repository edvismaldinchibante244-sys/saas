-- Adicionar coluna super_admin na tabela usuarios
ALTER TABLE usuarios
ADD COLUMN super_admin TINYINT(1) DEFAULT 0
AFTER ativo;
-- Atualizar usuário admin@ sistema.com para ser super admin (se existir)
UPDATE usuarios
SET super_admin = 1
WHERE email = 'admin@sistema.com';