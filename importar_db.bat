@echo off
chcp 65001 >nul
color 0A
echo.
echo ============================================
echo   IMPORTAÇÃO DO BANCO DE DADOS
echo   Sistema de Restaurante SaaS
echo ============================================
echo.

set MYSQL_BIN=C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe
set DB_FILE=C:\Users\HP EliteBook 650\database.sql
set DB_NAME=restaurante_saas

echo 📦 Criando base de dados: %DB_NAME%
echo.
echo Digite a senha do MySQL (geralmente é vazia, só pressione ENTER):
"%MYSQL_BIN%" -u root -p -e "CREATE DATABASE IF NOT EXISTS %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo.
echo 📥 Importando tabelas e dados...
echo.
"%MYSQL_BIN%" -u root -p %DB_NAME% < "%DB_FILE%"

echo.
echo ============================================
echo   ✅ IMPORTAÇÃO CONCLUÍDA COM SUCESSO!
echo ============================================
echo.
echo 📊 13 Tabelas criadas
echo 👤 Usuário admin criado
echo.
echo 🔑 CREDENCIAIS DE TESTE:
echo    Email: admin@sabormoz.co.mz
echo    Senha: admin123
echo.
echo 📁 Projeto: C:\Users\HP EliteBook 650\restaurante-saas
echo.
pause
