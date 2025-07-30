<?php

if (!function_exists('formatDateYMD')) {
    function formatDateYMD($date)
    {
        if (is_numeric($date)) {
            $date = date('Y-m-d', $date);
        }

        if (!$date) {
            return 'N/A';
        }

        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $date->format('Y/m/d');
    }
}

if (!function_exists('formatDateDMY')) {
    function formatDateDMY($date)
    {
        if (is_numeric($date)) {
            $date = date('d-m-Y', $date);
        }

        if (!$date) {
            return 'N/A';
        }

        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $date->format('d/m/Y');
    }
}

if (!function_exists('formatDateYMDHIS')) {
    function formatDateYMDHIS($date)
    {
        if (is_numeric($date)) {
            $date = date('Y-m-d H:i:s', $date);
        }

        if (!$date) {
            return 'N/A';
        }

        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $date->format('Y/m/d H:i:s');
    }
}

if (!function_exists('formatDateDMYHIS')) {
    function formatDateDMYHIS($date)
    {
        if (is_numeric($date)) {
            $date = date('d-m-Y H:i:s', $date);
        }

        if (!$date) {
            return 'N/A';
        }

        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $date->format('d/m/Y H:i:s');
    }
}
