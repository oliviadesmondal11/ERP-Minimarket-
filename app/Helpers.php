<?php

namespace App;

class Helpers
{
    /**
     * Format integer Rupiah into 'Rp 9.628.900'
     * Uses strict integer math without floating point precision loss
     */
    public static function formatRupiah(int|string|null $amount): string
    {
        if ($amount === null || $amount === '') {
            return 'Rp 0';
        }
        $val = (int)$amount;
        return 'Rp ' . number_format($val, 0, ',', '.');
    }

    /**
     * Format integer quantity
     */
    public static function formatQty(int|string|null $qty, string $uom = 'pcs'): string
    {
        if ($qty === null || $qty === '') {
            return '0 ' . $uom;
        }
        $val = (int)$qty;
        return number_format($val, 0, ',', '.') . ' ' . $uom;
    }

    /**
     * Sanitize HTML output
     */
    public static function e(?string $string): string
    {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}
