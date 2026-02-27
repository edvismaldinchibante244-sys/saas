-- Adicionar campo foto na tabela usuarios
ALTER TABLE usuarios ADD COLUMN foto VARCHAR(255) AFTER ativo;

-- Atualizar usuário admin com foto padrão (vazio = usa gerador)
UPDATE usuarios SET foto = '' WHERE id = 1;
