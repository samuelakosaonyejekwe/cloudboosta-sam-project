<?php
declare(strict_types=1);

require_once __DIR__ . '/Db.php';

class ProductsService
{
    public static function list(int $limit = 50, int $offset = 0): array
    {
        $pdo = Db::pdo();

        $stmt = $pdo->prepare(
            'SELECT id, name, price, description, image_url, is_active
             FROM products
             WHERE is_active = 1
             ORDER BY id ASC
             LIMIT :lim OFFSET :off'
        );

        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function getById(int $id): ?array
    {
        $pdo = Db::pdo();

        $stmt = $pdo->prepare(
            'SELECT id, name, price, description, image_url, is_active
             FROM products
             WHERE id = :id AND is_active = 1'
        );

        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }
}
