<?php
// db.php - DATABASE + LANGUAGE LOADER
require_once 'config.php';
require_once 'languages.php';

// Sprache auswählen (Fallback auf Englisch, falls falsch konfiguriert)
$activeLang = isset($language) ? $language : 'en';
if(!isset($lang[$activeLang])) $activeLang = 'en';
$L = $lang[$activeLang]; // $L ist jetzt unser Wörterbuch!

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<b>DB Error:</b> " . $e->getMessage());
}
?>