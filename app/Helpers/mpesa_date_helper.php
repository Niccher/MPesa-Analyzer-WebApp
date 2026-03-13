<?php

if (!function_exists('format_mpesa_date')) {
    /**
     * Standardizes date to: Year, Month Name, Day of Week Name, Time AM/PM
     * Example: 2026, March 13, Friday 07:10 PM
     */
    function format_mpesa_date($time): string {
        if (empty($time)) return 'N/A';
        
        // Handle millisecond timestamp (common in M-Pesa JS uploads)
        if (is_numeric($time) && $time > 1000000000000) {
            $timestamp = (int)($time / 1000);
        } else {
            $timestamp = is_numeric($time) ? (int)$time : strtotime($time);
        }

        if (!$timestamp) return 'Invalid Date';

        return date('Y, F d, l h:i A', $timestamp);
    }
}
