<?php
declare(strict_types=1);

/**
 * ============================================================
 *  ANTIGRAVITY LMS — Environment Loader (Production Safe)
 * ============================================================
 */

class Env
{
    private static bool $loaded = false;

    /**
     * Load .env file (optional in production)
     */
    public static function load(string $path): void
    {
        if (self::$loaded) {
            return;
        }

        if (!is_file($path) || !is_readable($path)) {
            // In production (.env usually doesn't exist)
            error_log("[Env] .env file not found at {$path} — using system environment only.");
            self::$loaded = true;
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // Remove wrapping quotes
            if (
                strlen($value) >= 2 &&
                (
                    (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))
                )
            ) {
                $value = substr($value, 1, -1);
            }

            // DO NOT overwrite real server env variables
            if (getenv($key) === false) {
                putenv("{$key}={$value}");
                $_ENV[$key]    = $value;
                $_SERVER[$key] = $value;
            }
        }

        self::$loaded = true;
    }

    /**
     * Get environment variable (production-ready)
     */
    public static function get(string $key, ?string $default = null): string
    {
        // 1️⃣ Check real environment (Render / Docker)
        $value = getenv($key);

        if ($value !== false) {
            return $value;
        }

        // 2️⃣ Check $_ENV fallback
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }

        // 3️⃣ Check $_SERVER fallback
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }

        // 4️⃣ Default if provided
        if ($default !== null) {
            return $default;
        }

        throw new RuntimeException(
            "[Env] Required environment variable \"{$key}\" is not set."
        );
    }

    /**
     * Get integer environment variable
     */
    public static function int(string $key, int $default = 0): int
    {
        try {
            return (int) self::get($key);
        } catch (RuntimeException) {
            return $default;
        }
    }

    /**
     * Get boolean environment variable
     */
    public static function bool(string $key, bool $default = false): bool
    {
        try {
            return in_array(
                strtolower(self::get($key)),
                ['true', '1', 'yes', 'on'],
                true
            );
        } catch (RuntimeException) {
            return $default;
        }
    }
}