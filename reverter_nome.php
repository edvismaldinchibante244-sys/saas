<?php
include 'config/database.php';
$db = (new Database())->getConnection();
$db->query("UPDATE usuarios SET nome = 'Administrador' WHERE id = 1");
echo "Nome revertido para Administrador";
