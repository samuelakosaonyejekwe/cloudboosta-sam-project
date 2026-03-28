<?php

require_once __DIR__ . '/../src/Response.php';
require_once __DIR__ . '/../src/Validator.php';
require_once __DIR__ . '/../src/CustomersService.php';
require_once __DIR__ . '/../src/OrdersService.php';

function readJsonBody(): array {
  $raw = file_get_contents('php://input');
  if (!$raw) return [];
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed. Use POST.', 405);
  }

  $body = readJsonBody();

  $customer_name  = Validator::requireString($body, 'customer_name', 120);
  $customer_email = Validator::requireEmail($body, 'customer_email');
  $customer_phone = Validator::optionalString($body, 'customer_phone', 40);

  if (!isset($body['items']) || !is_array($body['items']) || count($body['items']) < 1) {
    Response::error('items must be a non-empty array', 400);
  }

  $items = [];
  foreach ($body['items'] as $it) {
    if (!is_array($it)) Response::error('Invalid item format', 400);
    $pid = Validator::requireInt($it, 'product_id', 1, 1000000);
    $qty = Validator::requireInt($it, 'qty', 1, 1000);
    $items[] = ['product_id' => $pid, 'qty' => $qty];
  }

  $customerId = CustomersService::upsert($customer_name, $customer_email, $customer_phone);
  $order = OrdersService::create($customerId, $items);

  Response::ok(['order' => $order]);
} catch (InvalidArgumentException $e) {
  Response::error($e->getMessage(), 400);
} catch (Throwable $e) {
  Response::error('Failed to create order', 500, ['exception' => $e->getMessage()]);
}
