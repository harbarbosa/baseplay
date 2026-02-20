<?php

if (!function_exists('format_date_br')) {
    function format_date_br(?string $value, string $fallback = '-'): string
    {
        if (empty($value)) {
            return $fallback;
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return $fallback;
        }

        return date('d/m/Y', $timestamp);
    }
}

if (!function_exists('format_datetime_br')) {
    function format_datetime_br(?string $value, string $fallback = '-'): string
    {
        if (empty($value)) {
            return $fallback;
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return $fallback;
        }

        return date('d/m/Y H:i', $timestamp);
    }
}
