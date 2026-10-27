<?php

namespace App\Models;

use PDO;

class StockModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get stock matrix breakdown by product and location
     */
    public function getStockByLocation(): array
    {
        $sql = "
            SELECT 
                sq.id AS quant_id,
                p.id AS product_id,
                p.sku,
                p.name AS product_name,
                p.uom,
                p.cost_price,
                l.id AS location_id,
                l.code AS location_code,
                l.name AS location_name,
                l.location_type,
                sq.on_hand_qty,
                sq.reserved_qty,
                (sq.on_hand_qty - sq.reserved_qty) AS available_qty,
                (sq.on_hand_qty * p.cost_price) AS valuation
            FROM stock_quants sq
            JOIN products p ON sq.product_id = p.id
            JOIN locations l ON sq.location_id = l.id
            ORDER BY l.id ASC, p.id ASC
        ";

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Get unique locations list
     */
    public function getLocations(): array
    {
        return $this->db->query("SELECT * FROM locations ORDER BY id ASC")->fetchAll();
    }
}
