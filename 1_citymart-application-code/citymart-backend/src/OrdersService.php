<?php

require_once __DIR__ . '/Db.php';

class OrdersService {
  /**
   * Creates an order with items.
   * items: array of ['product_id' => int, 'qty' => int]
   */
  public static function create(int $customerId, array $items): array {
    $pdo = Db::pdo();
    $pdo->beginTransaction();

    try {
      // Create order header
      $ref = 'ORD-' . strtoupper(bin2hex(random_bytes(4))); // short reference
      $stmt = $pdo->prepare('INSERT INTO orders (customer_id, status, order_ref) VALUES (:cid, :status, :ref)');
      $stmt->execute([':cid' => $customerId, ':status' => 'PENDING', ':ref' => $ref]);
      $orderId = (int)$pdo->lastInsertId();

      // Insert order items
      $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, qty, unit_price) VALUES (:oid, :pid, :qty, :price)');

      foreach ($items as $it) {
        $pid = (int)$it['product_id'];
        $qty = (int)$it['qty'];

        // Get product price (ensure active)
        $p = $pdo->prepare('SELECT price FROM products WHERE id=:id AND is_active=1');
        $p->execute([':id' => $pid]);
        $row = $p->fetch();
        if (!$row) {
          throw new RuntimeException("Invalid product_id: $pid");
        }
        $price = (float)$row['price'];

        $itemStmt->execute([':oid' => $orderId, ':pid' => $pid, ':qty' => $qty, ':price' => $price]);
      }

      $pdo->commit();
      return ['order_id' => $orderId, 'order_ref' => $ref, 'status' => 'PENDING'];
    } catch (Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }
  }
}
