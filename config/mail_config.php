<?php
/**
 * ============================================================
 *  ANTIGRAVITY LMS — Mail Configuration
 *  File: config/mail_config.php
 * ============================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/env.php';

final class MailConfig
{
    public function __construct(
        public readonly string $host,
        public readonly int    $port,
        public readonly string $user,
        public readonly string $pass,
        public readonly string $from,
        public readonly string $fromName,
        public readonly string $contactEmail,
    ) {}

    /**
     * Load SMTP configuration from environment.
     * Works with:
     *   - Local .env file
     *   - Render environment variables
     */
    public static function load(): self
    {
        // Try loading local .env (safe if missing)
        Env::load(dirname(__DIR__) . '/.env');

        // Fetch values directly from environment
        $host = getenv('SMTP_HOST') ?: '';
        $port = (int)(getenv('SMTP_PORT') ?: 587);
        $user = getenv('SMTP_USER') ?: '';
        $pass = getenv('SMTP_PASS') ?: '';
        $from = getenv('SMTP_FROM') ?: $user;
        $fromName = getenv('SMTP_FROM_NAME') ?: 'AuroraLib';
        $contactEmail = getenv('SMTP_CONTACT') ?: $from;

        // Basic validation
        if (!$host || !$user || !$pass) {
            throw new RuntimeException(
                'SMTP configuration missing. Check Render Environment Variables.'
            );
        }

        if (!filter_var($user, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(
                "SMTP_USER \"{$user}\" is not a valid email."
            );
        }

        if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(
                "SMTP_FROM \"{$from}\" is not a valid email."
            );
        }

        if ($port < 1 || $port > 65535) {
            throw new RuntimeException(
                "SMTP_PORT \"{$port}\" is invalid."
            );
        }

        return new self(
            $host,
            $port,
            $user,
            $pass,
            $from,
            $fromName,
            $contactEmail
        );
    }
}