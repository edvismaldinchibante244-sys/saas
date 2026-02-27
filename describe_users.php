<?php
$conn = new PDO('mysql:host=localhost;dbname=restaurante_saas', 'root', '');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $conn->query('DESCRIBE usuarios');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . ' - ' . $row['Type'] . PHP_EOL;
}
