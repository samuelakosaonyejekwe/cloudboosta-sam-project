USE citymart;

DELETE FROM order_items;
DELETE FROM orders;
DELETE FROM customers;
DELETE FROM products;

INSERT INTO products (id, name, description, price, image_url, is_active) VALUES
(1, 'Premium Rice 5kg', 'Long grain rice, 5kg bag.', 12.99, '/assets/img/placeholder-product.png', 1),
(2, 'Cooking Oil 1L', 'Vegetable cooking oil, 1 litre.', 4.50, '/assets/img/placeholder-product.png', 1),
(3, 'Laundry Detergent', 'Fresh scent detergent, 2kg.', 8.75, '/assets/img/placeholder-product.png', 1),
(4, 'Bath Soap (Pack of 4)', 'Mild soap pack.', 3.20, '/assets/img/placeholder-product.png', 1);
