<?php

require_once __DIR__ . '/Db.php';

class CustomersService {
  // For a simple lab: if customer email already exists, reuse it.
  public static function upsert(string $name, string $email, ?string $phone): int {
    $pdo = Db::pdo();

    $stmt = $pdo->prepare('SELECT id FROM customers WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $existing = $stmt->fetch();

    if ($existing && isset($existing['id'])) {
      $id = (int)$existing['id'];
      $upd = $pdo->prepare('UPDATE customers SET name=:name, phone=:phone WHERE id=:id');
      $upd->execute([':name' => $name, ':phone' => $phone, ':id' => $id]);
      return $id;
    }

    $ins = $pdo->prepare('INSERT INTO customers (name, email, phone) VALUES (:name, :email, :phone)');
    $ins->execute([':name' => $name, ':email' => $email, ':phone' => $phone]);
    return (int)$pdo->lastInsertId();
  }
}
