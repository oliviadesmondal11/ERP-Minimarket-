<?php

namespace App\Models;

use PDO;

class ProductModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get all products with category and total stock on hand
     */
    public function getAll(): array
    {
        $sql = "
            SELECT 
                p.id,
                p.sku,
                p.barcode,
                p.name,
                p.uom,
                p.cost_price,
                p.sale_price,
                p.is_active,
                pc.code AS category_code,
                pc.name AS category_name,
                COALESCE(SUM(sq.on_hand_qty), 0) AS total_on_hand,
                COALESCE(SUM(sq.reserved_qty), 0) AS total_reserved,
                COALESCE(SUM(sq.on_hand_qty - sq.reserved_qty), 0) AS total_available,
                COALESCE(SUM(sq.on_hand_qty * p.cost_price), 0) AS total_valuation
            FROM products p
            JOIN product_categories pc ON p.category_id = pc.id
            LEFT JOIN stock_quants sq ON p.id = sq.product_id
            GROUP BY p.id, pc.code, pc.name
            ORDER BY p.id ASC
        ";

        return $this->db->query($sql)->fetchAll();
    }
}
