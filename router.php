<?php
// Router per il server di sviluppo PHP

$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// Se non è un file o cartella reale, passa tutto a api.php
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Per la root /, servi index.html
if ($uri === '/') {
    include __DIR__ . '/index.html';
    return true;
}

// Per tutto il resto (incluso /api/*), usa api.php
require __DIR__ . '/api.php';
