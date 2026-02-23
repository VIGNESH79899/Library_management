<?php
/**
 * Dynamic Base URL Configuration
 * Detects if the app is running on localhost or production.
 */

if (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1')) {
    define('BASE_URL', '/Library-management');
} else {
    define('BASE_URL', '');
}
