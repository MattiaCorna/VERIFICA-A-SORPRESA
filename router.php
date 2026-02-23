<?php
// Router per il server di sviluppo PHP - Solo API REST

$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// Se non è un file o cartella reale, passa tutto a api.php
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Tutto passa a api.php (incluso /api/*)
require __DIR__ . '/api.php';
