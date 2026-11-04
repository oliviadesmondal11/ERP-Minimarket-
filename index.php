<?php
/**
 * Fallback entry point jika server dijalankan langsung dari root folder
 * tanpa flag `-t public` (contoh: `php -S localhost:8000`).
 */
require_once __DIR__ . '/public/index.php';

