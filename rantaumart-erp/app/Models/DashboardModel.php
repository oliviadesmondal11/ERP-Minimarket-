<?php

namespace App\Models;

use PDO;

class DashboardModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getBaselineMetrics(): array
    {
        $totalOnHand = (int)$this->db->query("SELECT COALESCE(SUM(on_hand_qty), 0) FROM stock_quants")->fetchColumn();
        
        $totalStockValue = (int)$this->db->query("
            SELECT COALESCE(SUM(sq.on_hand_qty * p.cost_price), 0) 
            FROM stock_quants sq 
            JOIN products p ON sq.product_id = p.id
        ")->fetchColumn();

        $totalAR = (int)$this->db->query("
            SELECT COALESCE(SUM(amount_residual), 0) 
            FROM account_invoices 
            WHERE invoice_type = 'customer_invoice' AND status = 'posted'
        ")->fetchColumn();

        $totalAP = (int)$this->db->query("
            SELECT COALESCE(SUM(amount_residual), 0) 
            FROM account_invoices 
            WHERE invoice_type = 'vendor_bill' AND status = 'posted'
        ")->fetchColumn();

        $bankBalance = (int)$this->db->query("
            SELECT COALESCE(SUM(aji.debit - aji.credit), 0) 
            FROM account_journal_items aji 
            JOIN chart_of_accounts coa ON aji.account_id = coa.id 
            WHERE coa.code = '1010'
        ")->fetchColumn();

        $posCash = (int)$this->db->query("
            SELECT COALESCE(SUM(aji.debit - aji.credit), 0) 
            FROM account_journal_items aji 
            JOIN chart_of_accounts coa ON aji.account_id = coa.id 
            WHERE coa.code = '1020'
        ")->fetchColumn();

        if ($posCash === 0) {
            $posCash = (int)$this->db->query("
                SELECT COALESCE(opening_cash, 0) FROM pos_sessions WHERE status = 'opened' ORDER BY id LIMIT 1
            ")->fetchColumn();
        }

        $availAir600 = (int)$this->db->query("
            SELECT (sq.on_hand_qty - sq.reserved_qty) 
            FROM stock_quants sq 
            JOIN products p ON sq.product_id = p.id 
            JOIN locations l ON sq.location_id = l.id 
            WHERE p.sku = 'AIR-600' AND l.code = 'WH-PKU'
        ")->fetchColumn();

        // Location valuation breakdowns
        $valWhPku = (int)$this->db->query("
            SELECT COALESCE(SUM(sq.on_hand_qty * p.cost_price), 0) 
            FROM stock_quants sq 
            JOIN products p ON sq.product_id = p.id 
            JOIN locations l ON sq.location_id = l.id 
            WHERE l.code = 'WH-PKU'
        ")->fetchColumn();

        $valStorePku = (int)$this->db->query("
            SELECT COALESCE(SUM(sq.on_hand_qty * p.cost_price), 0) 
            FROM stock_quants sq 
            JOIN products p ON sq.product_id = p.id 
            JOIN locations l ON sq.location_id = l.id 
            WHERE l.code = 'STORE-PKU'
        ")->fetchColumn();

        $valStoreDum = (int)$this->db->query("
            SELECT COALESCE(SUM(sq.on_hand_qty * p.cost_price), 0) 
            FROM stock_quants sq 
            JOIN products p ON sq.product_id = p.id 
            JOIN locations l ON sq.location_id = l.id 
            WHERE l.code = 'STORE-DUM'
        ")->fetchColumn();

        return [
            'total_on_hand' => $totalOnHand,
            'total_stock_value' => $totalStockValue,
            'total_ar' => $totalAR,
            'total_ap' => $totalAP,
            'bank_balance' => $bankBalance,
            'pos_cash' => $posCash,
            'available_air600_wh' => $availAir600,
            'val_wh_pku' => $valWhPku,
            'val_store_pku' => $valStorePku,
            'val_store_dum' => $valStoreDum,
        ];
    }

    public function getSystemSummary(): array
    {
        return [
            'product_count' => (int)$this->db->query("SELECT COUNT(*) FROM products")->fetchColumn(),
            'location_count' => (int)$this->db->query("SELECT COUNT(*) FROM locations")->fetchColumn(),
            'partner_count' => (int)$this->db->query("SELECT COUNT(*) FROM partners")->fetchColumn(),
            'user_count' => (int)$this->db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        ];
    }

    public function getRecentAudits(int $limit = 8): array
    {
        return $this->db->query("SELECT * FROM audit_logs ORDER BY id DESC LIMIT {$limit}")->fetchAll();
    }
}
