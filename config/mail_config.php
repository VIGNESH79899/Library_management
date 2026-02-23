<?php
/**
 * ============================================================
 *  ANTIGRAVITY LMS — Mail Configuration
 *  File: config/mail_config.php
 *
 *  Single source of truth for SMTP settings.
 *  Reads from environment variables — NEVER hardcodes credentials.
 *
 *  Flow:
 *    1. Loads .env via the Env class (once per request, cached)
 *    2. Validates all required keys exist
 *    3. Returns a typed, immutable MailConfig value-object
 *
 *  Usage:
 *    require_once ROOT_DIR . '/config/mail_config.php';
 *    $cfg = MailConfig::load();
 *    $mail->Host     = $cfg->host;
 *    $mail->Username = $cfg->user;
 * ============================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/env.php';

/**
 * Immutable value-object holding validated SMTP configuration.
 * All properties are read-only (PHP 8.1+ readonly).
 */
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
     * Factory — loads and validates all SMTP config from env.
     *
     * @throws RuntimeException if any required variable is missing
     */
    public static function load(): self
    {
        // Load .env (idempotent — safe to call multiple times)
        Env::load(dirname(__DIR__) . '/.env');

        // ── Validate & retrieve each required key ─────────────
        // Env::get() throws a descriptive RuntimeException if missing
        $host        = Env::get('SMTP_HOST');
        $port        = Env::int('SMTP_PORT', 587);
        $user        = Env::get('SMTP_USER');
        $pass        = Env::get('SMTP_PASS');
        $from        = Env::get('SMTP_FROM');
        $fromName    = Env::get('SMTP_FROM_NAME', 'Antigravity Library');
        $contactEmail = Env::get('SMTP_CONTACT', $from);

        // ── Sanity checks ─────────────────────────────────────
        if (!filter_var($user, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(
                "[MailConfig] SMTP_USER \"{$user}\" is not a valid email address."
            );
        }
        if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(
                "[MailConfig] SMTP_FROM \"{$from}\" is not a valid email address."
            );
        }
        if ($port < 1 || $port > 65535) {
            throw new RuntimeException(
                "[MailConfig] SMTP_PORT \"{$port}\" must be between 1 and 65535."
            );
        }
        // Catch the placeholder value left by developers
        if (str_contains($pass, 'your-') || $pass === '') {
            throw new RuntimeException(
                "[MailConfig] SMTP_PASS appears to be unset. " .
                "Update .env with a real Gmail App Password."
            );
        }

        return new self($host, $port, $user, $pass, $from, $fromName, $contactEmail);
    }
}
