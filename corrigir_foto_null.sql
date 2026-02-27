-- ============================================
-- CORRIGIR VALORES DA COLUNA 'foto'
-- ============================================
-- Executar este script no MySQL/phpMyAdmin

USE restaurante_saas;

-- Verificar valores atuais antes da correção
SELECT id, nome, foto, 
       CASE 
           WHEN foto = '' THEN 'STRING VAZIA'
           WHEN foto = 'NULL' THEN 'STRING NULL'
           WHEN foto IS NULL THEN 'VALOR NULL'
           WHEN foto IS NOT NULL THEN 'TEM VALOR'
       END AS status_foto
FROM usuarios;

-- Corrigir:Converter strings vazias para NULL
UPDATE usuarios SET foto = NULL WHERE foto = '' OR foto = 'NULL';

-- Verificar valores após correção
SELECT id, nome, foto FROM usuarios;
