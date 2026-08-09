<?php

namespace App\Libraries;

class Notifier
{
    private const DEFAULT_TRIGGERS = [
        'signup'         => true,
        'password_reset' => true,
        'report'         => true,
        'ml_complete'    => true,
    ];

    private const DEFAULT_CONFIG = [
        'smtp_host'   => '',
        'smtp_port'   => 587,
        'smtp_user'   => '',
        'smtp_pass'   => '',
        'smtp_crypto' => 'tls',
        'from_email'  => 'noreply@example.com',
        'from_name'   => 'Mpesa Analyzer',
        'enabled'     => false,
    ];

    /**
     * Load the email/SMTP configuration from tbl_Settings (email_* keys).
     */
    public static function config(): array
    {
        $db = \Config\Database::connect();
        $rows = $db->table('tbl_Settings')
            ->where('`key` LIKE', 'email_%')
            ->get()
            ->getResultArray();

        $config = self::DEFAULT_CONFIG;
        foreach ($rows as $row) {
            $key = str_replace('email_', '', $row['key']);
            if (array_key_exists($key, $config)) {
                $value = $row['value'];
                if (is_bool($config[$key])) {
                    $value = (bool)$value;
                }
                if (is_int($config[$key])) {
                    $value = (int)$value;
                }
                $config[$key] = $value;
            }
        }

        return $config;
    }

    /**
     * Load the email trigger toggles (email_triggers JSON key).
     */
    public static function triggers(): array
    {
        $db = \Config\Database::connect();
        $row = $db->table('tbl_Settings')
            ->where('`key`', 'email_triggers')
            ->get()
            ->getRow();

        $saved = $row ? json_decode($row->value ?? '{}', true) : [];

        return array_merge(self::DEFAULT_TRIGGERS, is_array($saved) ? $saved : []);
    }

    /**
     * Whether a given trigger is enabled.
     */
    public static function isTriggerEnabled(string $trigger): bool
    {
        $triggers = self::triggers();

        return !empty($triggers[$trigger]);
    }

    /**
     * Whether the global email switch is on and an SMTP host is configured.
     */
    public static function isReady(): bool
    {
        $config = self::config();

        return $config['enabled'] && $config['smtp_host'] !== '';
    }

    /**
     * Send an email using the configured SMTP settings.
     *
     * @return array{status: string, message: string}
     */
    public static function send(string $to, string $subject, string $message, bool $html = false): array
    {
        $config = self::config();

        if (!$config['enabled']) {
            return ['status' => 'error', 'message' => 'Email notifications are disabled in the admin settings.'];
        }

        if ($config['smtp_host'] === '') {
            return ['status' => 'error', 'message' => 'SMTP host is not configured. Add it in Admin > Email Notifications.'];
        }

        $mail = \Config\Services::email([
            'protocol'   => 'smtp',
            'SMTPHost'   => $config['smtp_host'],
            'SMTPUser'   => $config['smtp_user'],
            'SMTPPass'   => $config['smtp_pass'],
            'SMTPPort'   => (int)$config['smtp_port'],
            'SMTPCrypto' => $config['smtp_crypto'] === 'none' ? '' : $config['smtp_crypto'],
            'SMTPTimeout' => 10,
            'mailType'   => $html ? 'html' : 'text',
            'wordWrap'   => true,
        ], false);

        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->setTo($to);
        $mail->setSubject($subject);
        $mail->setMessage($message);

        if ($mail->send()) {
            return ['status' => 'success', 'message' => 'Email sent to ' . $to . '.'];
        }

        $debug = $mail->printDebugger(['headers', 'subject', 'body', 'message']);

        return ['status' => 'error', 'message' => 'Failed to send email: ' . (is_array($debug) ? implode(' ', $debug) : $debug)];
    }
}
