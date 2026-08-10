<?php

namespace App\Libraries;

class Notifier
{
    private const DEFAULT_TRIGGERS = [
        'signup'            => true,
        'password_reset'    => true,
        'report'            => true,
        'ml_complete'       => true,
        'new_device'        => true,
        'user_deleted_data' => true,
        'user_account_deleted' => true,
        'user_upgraded'     => true,
        'user_downgraded'   => true,
        'user_activated'    => true,
        'user_deactivated'  => true,
        'maintenance_started' => false,
        'maintenance_stopped' => false,
    ];

    /**
     * Trigger metadata (label + description) for the admin UI.
     */
    private const TRIGGER_META = [
        'signup'            => ['label' => 'Welcome Email', 'group' => 'User Lifecycle', 'description' => 'Send a welcome email when a new user signs up.'],
        'password_reset'    => ['label' => 'Password Reset / Magic Link', 'group' => 'Security', 'description' => 'Send password reset / magic link emails.'],
        'report'            => ['label' => 'Scheduled Reports', 'group' => 'Reporting', 'description' => 'Send scheduled spending reports.'],
        'ml_complete'       => ['label' => 'ML Analysis Complete', 'group' => 'Analysis', 'description' => 'Notify a user when their ML analysis finishes and results are ready.'],
        'new_device'        => ['label' => 'New Device Connected', 'group' => 'Security', 'description' => 'Notify a user when a new device connects to their account.'],
        'user_deleted_data' => ['label' => 'Data Deleted', 'group' => 'User Lifecycle', 'description' => 'Notify a user when they request to delete their data.'],
        'user_account_deleted' => ['label' => 'Account Deleted', 'group' => 'User Lifecycle', 'description' => 'Notify a user when their account is permanently deleted.'],
        'user_upgraded'     => ['label' => 'Role Upgraded', 'group' => 'Admin', 'description' => 'Notify a user when their role is upgraded (e.g. to admin).'],
        'user_downgraded'   => ['label' => 'Role Downgraded', 'group' => 'Admin', 'description' => 'Notify a user when their role is downgraded (e.g. from admin).'],
        'user_activated'    => ['label' => 'Account Activated', 'group' => 'User Lifecycle', 'description' => 'Notify a user when their account is activated.'],
        'user_deactivated'  => ['label' => 'Account Deactivated', 'group' => 'User Lifecycle', 'description' => 'Notify a user when their account is deactivated.'],
        'maintenance_started' => ['label' => 'Maintenance Started', 'group' => 'System', 'description' => 'Notify all users when maintenance mode starts.'],
        'maintenance_stopped' => ['label' => 'Maintenance Stopped', 'group' => 'System', 'description' => 'Notify all users when maintenance mode stops.'],
    ];

    /**
     * Trigger → email template mapping.
     */
    private const TRIGGER_TEMPLATES = [
        'signup'              => 'Emails/signup',
        'password_reset'      => 'Emails/password_reset',
        'report'              => 'Emails/report',
        'ml_complete'         => 'Emails/ml_complete',
        'new_device'          => 'Emails/new_device',
        'user_deleted_data'   => 'Emails/user_deleted_data',
        'user_account_deleted' => 'Emails/user_account_deleted',
        'user_upgraded'       => 'Emails/user_upgraded',
        'user_downgraded'     => 'Emails/user_downgraded',
        'user_activated'      => 'Emails/user_activated',
        'user_deactivated'    => 'Emails/user_deactivated',
        'maintenance_started' => 'Emails/maintenance_started',
        'maintenance_stopped' => 'Emails/maintenance_stopped',
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
     * Metadata (key, label, description) for user-defined triggers.
     *
     * @return array<int, array{key: string, label: string, description: string}>
     */
    public static function customTriggers(): array
    {
        $db = \Config\Database::connect();
        $row = $db->table('tbl_Settings')
            ->where('`key`', 'email_triggers_custom')
            ->get()
            ->getRow();

        $saved = $row ? json_decode($row->value ?? '[]', true) : [];

        if (!is_array($saved)) {
            return [];
        }

        $out = [];
        foreach ($saved as $item) {
            if (!is_array($item) || empty($item['key'])) {
                continue;
            }
            $out[] = [
                'key'         => trim((string) $item['key']),
                'label'       => trim((string) ($item['label'] ?? $item['key'])),
                'description' => trim((string) ($item['description'] ?? '')),
            ];
        }

        return $out;
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
     * Trigger metadata (label/group/description) for built-in and custom triggers.
     *
     * @return array<int, array{key: string, label: string, group: string, description: string, is_custom: bool}>
     */
    public static function triggerMeta(): array
    {
        $meta = [];
        foreach (self::TRIGGER_META as $key => $info) {
            $meta[] = [
                'key'         => $key,
                'label'       => $info['label'],
                'group'       => $info['group'],
                'description' => $info['description'],
                'is_custom'   => false,
            ];
        }

        foreach (self::customTriggers() as $custom) {
            $meta[] = [
                'key'         => $custom['key'],
                'label'       => $custom['label'],
                'group'       => 'Custom',
                'description' => $custom['description'],
                'is_custom'   => true,
            ];
        }

        return $meta;
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
      * Send an email to a single recipient using a template.
      *
      * @param array $data  Variables passed to the view, including the "as-at" timestamp.
     * @param string $trigger  Logical trigger name (e.g. 'signup') recorded in the email log.
     * @return array{status: string, message: string}
     */
    public static function sendTemplate(string $to, string $subject, string $template, array $data = [], string $trigger = ''): array
    {
        $templateKey = $template;
        $data = array_merge($data, [
            'title'      => $subject,
            'sentAt'     => date('Y-m-d H:i:s T'),
            'emailId'    => $templateKey,
            '_recipient' => $to,
        ]);

        // Render the HTML body (a template that gets embedded in the layout).
        $contentView = self::templatePath($template);
        if ($contentView === '') {
            return ['status' => 'error', 'message' => 'Unknown email template: ' . $template];
        }

        $content = view($contentView, $data, ['debug' => false]);

        // Emails are HTML-only: the styled layout embeds the content view.
        $html = view('Emails/layout', array_merge($data, ['content' => $content]), ['debug' => false]);

        $logTrigger = $trigger !== '' ? $trigger : self::templateBaseName($template);
        return self::sendRaw($to, $subject, $html, '', $logTrigger);
    }

    /**
     * Derive a short trigger name from a view path (e.g. "Emails/signup" → "signup").
     *
     * @internal
     */
    private static function templateBaseName(string $template): string
    {
        return basename(str_replace('\\', '/', $template));
    }

    /**
     * Map a trigger key to its template base (without _text), or '' if unknown.
     *
     * @internal
     */
    private static function templatePath(string $template, bool $text = false): string
    {
        $suffix = $text ? '_text' : '';

        // Full view path like "Emails/signup".
        $view = $template . $suffix;
        $file = APPPATH . 'Views/' . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';

        return is_file($file) ? $view : '';
    }

    /**
     * Send an email using the configured SMTP settings with HTML + plain-text.
     *
     * @return array{status: string, message: string}
     */
    public static function send(string $to, string $subject, string $message, bool $html = false): array
    {
        return self::sendRaw($to, $subject, $html ? $message : '', $html ? '' : $message);
    }

    /**
     * Send a styled test email to verify SMTP configuration.
     *
     * @return array{status: string, message: string}
     */
    public static function sendTestEmail(string $to): array
    {
        $config = self::config();

        $data = [
            'to'         => $to,
            'smtpHost'   => $config['smtp_host'],
            'smtpPort'   => $config['smtp_port'],
            'smtpCrypto' => $config['smtp_crypto'],
            'fromEmail'  => $config['from_email'],
        ];

        return self::sendTemplate($to, 'Mpesa Analyzer Test Email', 'Emails/test', $data);
    }

    /**
     * Record an email send attempt in tbl_Email_Log.
     */
    public static function logEmail(string $trigger, string $to, string $subject, string $status, string $detail = ''): void
    {
        try {
            $db = \Config\Database::connect();
            if (!$db->tableExists('tbl_Email_Log')) {
                return;
            }
            $db->table('tbl_Email_Log')->insert([
                'trigger'    => $trigger,
                'to_email'   => $to,
                'subject'    => $subject,
                'status'     => $status,
                'message'    => $detail,
                'sent_at'    => gmdate('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // Logging must never break email sending.
        }
    }

    /**
     * Raw SMTP sender shared by both legacy send() and template-based sendTemplate().
     *
     * @return array{status: string, message: string}
     */
    private static function sendRaw(string $to, string $subject, string $html = '', string $text = '', string $trigger = ''): array
    {
        $config = self::config();

        if (!$config['enabled']) {
            return ['status' => 'error', 'message' => 'Email notifications are disabled in the admin settings.'];
        }

        if ($config['smtp_host'] === '') {
            return ['status' => 'error', 'message' => 'SMTP host is not configured. Add it in Admin > Email Notifications.'];
        }

        $hasHtml = $html !== '';
        $hasText = $text !== '';

        $mail = \Config\Services::email([
            'protocol'   => 'smtp',
            'SMTPHost'   => $config['smtp_host'],
            'SMTPUser'   => $config['smtp_user'],
            'SMTPPass'   => $config['smtp_pass'],
            'SMTPPort'   => (int)$config['smtp_port'],
            'SMTPCrypto' => $config['smtp_crypto'] === 'none' ? '' : $config['smtp_crypto'],
            'SMTPTimeout' => 10,
            'wordWrap'   => true,
            'charset'    => 'UTF-8',
            'newline'    => "\r\n",
            'CRLF'       => "\r\n",
            'mailType'   => $hasHtml ? 'html' : 'text',
        ], false);

        $mail->setCRLF("\r\n");
        $mail->setNewline("\r\n");

        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->setTo($to);
        $mail->setSubject($subject);

        if ($hasHtml && $hasText) {
            $mail->setMessage($html);
            $mail->setAltMessage($text);
        } elseif ($hasHtml) {
            $mail->setMessage($html);
        } elseif ($hasText) {
            $mail->setMessage($text);
        } else {
            $mail->setMessage('(empty)');
        }

        if ($mail->send()) {
            self::logEmail($trigger, $to, $subject, 'success', 'Email sent to ' . $to . '.');
            return ['status' => 'success', 'message' => 'Email sent to ' . $to . '.'];
        }

        $debug = $mail->printDebugger(['headers', 'subject', 'body', 'message']);
        $msg = is_array($debug) ? implode("\n", $debug) : (string)$debug;

        self::logEmail($trigger, $to, $subject, 'error', $msg);

        if (strpos($config['smtp_host'], 'gmail.com') !== false || strpos($config['smtp_host'], 'google') !== false) {
            $msg .= "\n\nIf using Gmail: Google no longer accepts regular account passwords for SMTP. Generate a Gmail App Password (Google Account → Security → App passwords) and use it as the SMTP password.";
        }

        return ['status' => 'error', 'message' => 'Failed to send email: ' . $msg];
    }

    /**
     * Send an email to multiple recipients using a template (batch-friendly).
     *
     * @param array $recipients  Array of ['email' => ..., 'data' => [...]] or just ['email1@...', ...]
     * @param string $trigger  Logical trigger name (e.g. 'maintenance_started') recorded in the email log.
     * @return array{status: string, sent: int, failed: int, message: string}
     */
    public static function sendToMany(string $subject, string $template, array $recipients, string $trigger = ''): array
    {
        $sent = 0;
        $failed = 0;
        $logTrigger = $trigger !== '' ? $trigger : self::templateBaseName($template);

        foreach ($recipients as $recipient) {
            if (is_array($recipient)) {
                $to = $recipient['email'];
                unset($recipient['email']);
                $data = $recipient;
            } else {
                $to = (string) $recipient;
                $data = [];
            }

            $result = self::sendTemplate($to, $subject, $template, $data, $logTrigger);
            if ($result['status'] === 'success') {
                $sent++;
            } else {
                $failed++;
            }
        }

        return [
            'status' => 'success',
            'sent'   => $sent,
            'failed' => $failed,
            'message' => "Sent {$sent} email(s). Failed: {$failed}.",
        ];
    }

    /**
     * Send a trigger-based email to a single user.
     *
     * @param array $extraData  Extra variables passed to the template.
     * @return array{status: string, message: string}
     */
    public static function sendTrigger(string $to, string $trigger, array $extraData = []): array
    {
        if (!isset(self::TRIGGER_TEMPLATES[$trigger])) {
            return ['status' => 'error', 'message' => 'No template registered for trigger: ' . $trigger];
        }

        // Subject is stored in language strings for i18n; fall back to a sensible default.
        $subject = self::subjectFor($trigger, $extraData);

        return self::sendTemplate($to, $subject, self::TRIGGER_TEMPLATES[$trigger], $extraData, $trigger);
    }

    /**
     * Human-readable subject per trigger.
     */
    public static function subjectFor(string $trigger, array $data = []): string
    {
        $subjects = [
            'signup'              => 'Welcome to Mpesa Analyzer!',
            'password_reset'      => 'Your Login Link',
            'report'              => 'Your Mpesa Analyzer ' . ucfirst((string)($data['frequency'] ?? 'Monthly')) . ' Report',
            'ml_complete'         => 'Your Mpesa Analyzer analysis is ready',
            'new_device'          => 'New device connected to your account',
            'user_deleted_data'   => 'Your Mpesa Analyzer data has been deleted',
            'user_account_deleted' => 'Your Mpesa Analyzer account has been deleted',
            'user_upgraded'       => 'Your role has been upgraded',
            'user_downgraded'     => 'Your role has been changed',
            'user_activated'      => 'Your Mpesa Analyzer account is now active',
            'user_deactivated'    => 'Your Mpesa Analyzer account has been deactivated',
            'maintenance_started' => 'Mpesa Analyzer maintenance started',
            'maintenance_stopped' => 'Mpesa Analyzer maintenance is complete',
        ];

        return $subjects[$trigger] ?? 'Notification from Mpesa Analyzer';
    }

    /**
     * Retrieve a page of the email send log from tbl_Email_Log.
     *
     * @return array<int,array>
     */
    public static function logEntries(int $limit = 50, int $offset = 0): array
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('tbl_Email_Log')) {
            return [];
        }

        return $db->table('tbl_Email_Log')
            ->orderBy('id', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();
    }

    /**
     * Total number of entries in the email log.
     */
    public static function logCount(): int
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('tbl_Email_Log')) {
            return 0;
        }

        return (int) $db->table('tbl_Email_Log')->selectCount('id')->get()->getRow()->id ?? 0;
    }
}
