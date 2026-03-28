<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Response.php';
require_once __DIR__ . '/../src/ProductsService.php';

try {
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 50;
    $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

    if ($limit < 1 || $limit > 200) {
        $limit = 50;
    }

    if ($offset < 0) {
        $offset = 0;
    }

    $products = ProductsService::list($limit, $offset);
    Response::ok(['products' => $products]);
} catch (Throwable $e) {
    Response::error('Failed to list products', 500, ['exception' => $e->getMessage()]);
}
