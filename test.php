<?php
$host = '127.0.0.1';
$user = 'root';        // Hier DEINE Daten eintragen
$pass = 'CheckPreis7272!';
$db   = 'net';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    echo "✅ VERBINDUNG ERFOLGREICH! Die Datenbank funktioniert.";
} catch (PDOException $e) {
    echo "❌ FEHLER: " . $e->getMessage();
}
?>