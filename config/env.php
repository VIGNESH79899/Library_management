<?php
/**
 * ============================================================
 *  ANTIGRAVITY LMS — Environment Loader
 *  File: config/env.php
 *
 *  Parses the project-root .env file and loads each key into
 *  the process environment via putenv() AND $_ENV[].
 *
 *  Features:
 *   - Skips blank lines and # comments
 *   - Trims surrounding whitespace and optional wrapping quotes
 *   - Never overwrites a key already set in the real environment
 *     (real server env vars always take priority over .env)
 *   - Does NOT require any external library (pure PHP)
 *   - Compatible with PHP 7.4+ and shared hosting
 *
 *  Usage:
 *   require_once __DIR__ . '/env.php';   // call once at bootstrap
 *   Env::load(__DIR__ . '/../.env');
 *
 *  Then anywhere in the app:
 *   $smtpUser = Env::get('SMTP_USER');             // throws on missing
 *   $appEnv   = Env::get('APP_ENV', 'production'); // default on missing
 * ============================================================
 */

declare(strict_types=1);

class Env
{
    /** @var bool Whether the .env file has been loaded yet */
    private static bool $loaded = false;

    /**
     * Parse the given .env file and populate getenv() / $_ENV.
     * Safe to call multiple times — loads only once per request.
     *
     * @param string $path Absolute path to the .env file
     * @throws RuntimeException if the file cannot be read
     */
    public static function load(string $path): void
    {
        if (self::$loaded) {
            return;
        }

        if (!is_file($path) || !is_readable($path)) {
            // Not a hard crash — the real environment might already
            // have all required variables set (e.g. on a live server).
            error_log("[Env] Warning: .env file not found at {$path}");
            self::$loaded = true;
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and blank lines
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Must contain an = sign
            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // Strip optional surrounding quotes (' or ")
            if (
                strlen($value) >= 2 &&
                (
                    (str_starts_with($value, '"')  && str_ends_with($value, '"'))  ||
                    (str_starts_with($value, "'")  && str_ends_with($value, "'"))
                )
            ) {
                $value = substr($value, 1, -1);
            }

            // Never overwrite a variable already set in the real env
            // (e.g., set via Apache SetEnv or server control panel)
            if (getenv($key) === false) {
                putenv("{$key}={$value}");
                $_ENV[$key]    = $value;
                $_SERVER[$key] = $value;
            }
        }

        self::$loaded = true;
    }

    /**
     * Retrieve an environment variable by key.
     *
     * @param string      $key     Variable name (e.g. 'SMTP_USER')
     * @param string|null $default Return this if key is missing.
     *                             Pass NULL (the default) to throw instead.
     *
     * @return string              The value
     * @throws RuntimeException    If key is missing AND no default given
     */
    public static function get(string $key, ?string $default = null): string
    {
        $value = getenv($key);

        if ($value === false || $value === '') {
            if ($default !== null) {
                return $default;
            }
            throw new RuntimeException(
                "[Env] Required environment variable \"{$key}\" is not set. " .
                "Check your .env file or server environment config."
            );
        }

        return $value;
    }

    /**
     * Return an integer environment variable.
     *
     * @param string $key
     * @param int    $default
     * @return int
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
     * Return a boolean environment variable.
     * Truthy values: "true", "1", "yes", "on" (case-insensitive).
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
