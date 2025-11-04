<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

echo json_encode(['ok' => true, 'message' => 'API root']);


