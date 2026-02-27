-- Adicionar coluna 'capacidade' na tabela mesas (caso não exista)
ALTER TABLE mesas ADD COLUMN IF NOT EXISTS capacidade INT DEFAULT 4 AFTER numero;

-- Atualizar registros existentes que ficaram com NULL
UPDATE mesas SET capacidade = 4 WHERE capacidade IS NULL;
