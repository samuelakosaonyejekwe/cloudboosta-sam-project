<?php

require_once __DIR__ . '/../src/Response.php';
require_once __DIR__ . '/../src/Validator.php';
require_once __DIR__ . '/../src/ProductsService.php';

try {
  if (!isset($_GET['id'])) {
    Response::error('Missing query parameter: id', 400);
  }
  $id = (int)$_GET['id'];
  if ($id < 1) Response::error('Invalid id', 400);

  $product = ProductsService::getById($id);
  if (!$product) {
    Response::error('Product not found', 404);
  }

  Response::ok(['product' => $product]);
} catch (Throwable $e) {
  Response::error('Failed to get product', 500, ['exception' => $e->getMessage()]);
}
