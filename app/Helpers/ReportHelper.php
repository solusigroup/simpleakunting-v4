<?php

namespace App\Helpers;

class ReportHelper
{
    /**
     * Format currency value to Indonesian Rupiah format.
     */
    public static function formatCurrency($value, $showSymbol = true)
    {
        $formatted = number_format(abs($value), 0, ',', '.');
        
        if ($value < 0) {
            $formatted = "($formatted)";
        }
        
        return $showSymbol ? "Rp $formatted" : $formatted;
    }
    
    /**
     * Format percentage value with sign.
     */
    public static function formatPercentage($value, $decimals = 2)
    {
        $sign = $value >= 0 ? '+' : '';
        return $sign . number_format($value, $decimals, ',', '.') . '%';
    }
    
    /**
     * Get trend icon based on percentage value.
     */
    public static function getTrendIcon($percentage)
    {
        return match(true) {
            $percentage > 5 => '↑',
            $percentage < -5 => '↓',
            default => '→'
        };
    }
    
    /**
     * Get trend color based on percentage value.
     */
    public static function getTrendColor($percentage)
    {
        return match(true) {
            $percentage > 5 => '#2C5F2D', // Green
            $percentage < -5 => '#D32F2F', // Red
            default => '#999999' // Gray
        };
    }
    
    /**
     * Extract city name from address string.
     */
    public static function extractCity($address)
    {
        if (empty($address)) {
            return 'Jakarta';
        }
        
        $cities = [
            'Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang',
            'Makassar', 'Palembang', 'Tangerang', 'Depok', 'Bekasi',
            'Bogor', 'Malang', 'Yogyakarta', 'Denpasar', 'Balikpapan'
        ];
        
        foreach ($cities as $city) {
            if (stripos($address, $city) !== false) {
                return $city;
            }
        }
        
        return 'Jakarta'; // Default
    }
    
    /**
     * Calculate variance between two values.
     */
    public static function calculateVariance($period1Value, $period2Value)
    {
        $variance = $period2Value - $period1Value;
        
        if ($period1Value == 0) {
            $percentage = ($period2Value > 0) ? 100 : 0;
        } else {
            $percentage = (($period2Value - $period1Value) / abs($period1Value)) * 100;
        }
        
        $trend = match(true) {
            $percentage > 5 => 'increase',
            $percentage < -5 => 'decrease',
            default => 'stable'
        };
        
        return [
            'absolute' => $variance,
            'percentage' => round($percentage, 2),
            'trend' => $trend
        ];
    }
    
    /**
     * Format Indonesian date.
     */
    public static function formatDate($date, $format = 'd F Y')
    {
        $months = [
            'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
            'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
            'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
            'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
        ];
        
        $formatted = date($format, strtotime($date));
        
        foreach ($months as $eng => $ind) {
            $formatted = str_replace($eng, $ind, $formatted);
        }
        
        return $formatted;
    }
}
