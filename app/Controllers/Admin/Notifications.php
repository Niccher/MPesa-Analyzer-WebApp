<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\Notifier;
use CodeIgniter\API\ResponseTrait;

class Notifications extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $config = Notifier::config();
        $triggers = Notifier::triggers();

        $shieldSettings = [];
        foreach (['Email.fromEmail', 'Email.fromName', 'Email.protocol', 'Email.SMTPHost', 'Email.SMTPUser', 'Email.SMTPPass', 'Email.SMTPPort', 'Email.SMTPCrypto', 'Email.mailType'] as $key) {
            $shieldSettings[$key] = setting($key);
        }

        return view('Admin/Notifications/index', [
            'bg_color'        => '#B1B8ED',
            'config'          => $config,
            'triggers'        => $triggers,
            'trigger_meta'    => Notifier::triggerMeta(),
            'custom_triggers' => Notifier::customTriggers(),
            'shield'          => $shieldSettings,
        ]);
    }

    public function saveConfig()
    {
        $config = [
            'smtp_host'   => trim((string)$this->request->getPost('smtp_host')),
            'smtp_port'   => (int)$this->request->getPost('smtp_port') ?: 587,
            'smtp_user'   => trim((string)$this->request->getPost('smtp_user')),
            'smtp_pass'   => (string)$this->request->getPost('smtp_pass'),
            'smtp_crypto' => in_array($this->request->getPost('smtp_crypto'), ['tls', 'ssl', 'none'], true) ? $this->request->getPost('smtp_crypto') : 'tls',
            'from_email'  => trim((string)$this->request->getPost('from_email')),
            'from_name'   => trim((string)$this->request->getPost('from_name')),
            'enabled'     => (bool)$this->request->getPost('enabled'),
        ];

        if ($config['from_email'] !== '' && !filter_var($config['from_email'], FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid "from" email address.']);
        }

        $db = \Config\Database::connect();
        $updates = [
            ['key' => 'email_smtp_host', 'value' => $config['smtp_host'], 'type' => 'string', 'description' => 'SMTP server host'],
            ['key' => 'email_smtp_port', 'value' => $config['smtp_port'], 'type' => 'integer', 'description' => 'SMTP server port'],
            ['key' => 'email_smtp_user', 'value' => $config['smtp_user'], 'type' => 'string', 'description' => 'SMTP username'],
            ['key' => 'email_smtp_pass', 'value' => $config['smtp_pass'], 'type' => 'string', 'description' => 'SMTP password'],
            ['key' => 'email_smtp_crypto', 'value' => $config['smtp_crypto'], 'type' => 'string', 'description' => 'SMTP encryption (tls/ssl/none)'],
            ['key' => 'email_from_email', 'value' => $config['from_email'], 'type' => 'string', 'description' => 'From email address'],
            ['key' => 'email_from_name', 'value' => $config['from_name'], 'type' => 'string', 'description' => 'From display name'],
            ['key' => 'email_enabled', 'value' => $config['enabled'] ? '1' : '0', 'type' => 'boolean', 'description' => 'Enable email notifications'],
        ];

        foreach ($updates as $u) {
            $db->table('tbl_Settings')->upsert($u);
        }

        $this->syncShieldEmailConfig($config);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Email/SMTP configuration saved.',
        ]);
    }

    public function sendTestEmail()
    {
        $to = trim((string)$this->request->getPost('test_email'));
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Enter a valid test email address.']);
        }

        $result = Notifier::sendTestEmail($to);

        return $this->response->setJSON($result);
    }

    public function saveTriggers()
    {
        $posted = $this->request->getPost('triggers');
        $triggers = [];

        // Save toggle state for every known trigger (built-in + custom).
        foreach (Notifier::triggerMeta() as $meta) {
            $triggers[$meta['key']] = !empty($posted[$meta['key']]);
        }

        $db = \Config\Database::connect();
        $db->table('tbl_Settings')->upsert([
            'key' => 'email_triggers',
            'value' => json_encode($triggers),
            'type' => 'json',
            'description' => 'Email notification triggers',
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Email trigger settings saved.',
        ]);
    }

    public function addTrigger()
    {
        $key = strtolower(trim((string) $this->request->getPost('trigger_key')));
        $label = trim((string) $this->request->getPost('trigger_label'));
        $description = trim((string) $this->request->getPost('trigger_description'));

        if ($key === '' || $label === '') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Trigger key and label are required.']);
        }

        if (!preg_match('/^[a-z][a-z0-9_]*$/', $key)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Trigger key must start with a letter and contain only lowercase letters, numbers and underscores.']);
        }

        // Built-in triggers are listed in Notifier::TRIGGER_META (private) — guard via triggerMeta().
        foreach (Notifier::triggerMeta() as $built) {
            if ($built['key'] === $key) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'That trigger already exists as a built-in trigger.']);
            }
        }

        $db = \Config\Database::connect();
        $custom = Notifier::customTriggers();

        foreach ($custom as $item) {
            if ($item['key'] === $key) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'A trigger with that key already exists.']);
            }
        }

        $custom[] = [
            'key' => $key,
            'label' => $label,
            'description' => $description,
        ];

        $db->table('tbl_Settings')->upsert([
            'key' => 'email_triggers_custom',
            'value' => json_encode($custom),
            'type' => 'json',
            'description' => 'Custom email notification triggers',
        ]);

        // New custom triggers are enabled by default so they take effect immediately.
        $triggers = Notifier::triggers();
        $triggers[$key] = true;
        $db->table('tbl_Settings')->upsert([
            'key' => 'email_triggers',
            'value' => json_encode($triggers),
            'type' => 'json',
            'description' => 'Email notification triggers',
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Trigger "' . $label . '" added and enabled.',
        ]);
    }

    public function deleteTrigger()
    {
        $key = trim((string) $this->request->getPost('trigger_key'));
        if ($key === '') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Missing trigger key.']);
        }

        $db = \Config\Database::connect();

        $custom = Notifier::customTriggers();
        $filtered = array_values(array_filter($custom, static fn ($item) => $item['key'] !== $key));

        $db->table('tbl_Settings')->upsert([
            'key' => 'email_triggers_custom',
            'value' => json_encode($filtered),
            'type' => 'json',
            'description' => 'Custom email notification triggers',
        ]);

        $triggers = Notifier::triggers();
        unset($triggers[$key]);
        $db->table('tbl_Settings')->upsert([
            'key' => 'email_triggers',
            'value' => json_encode($triggers),
            'type' => 'json',
            'description' => 'Email notification triggers',
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Trigger deleted.',
        ]);
    }

    /**
     * Mirror SMTP/from config into Shield's settings table so magic-link
     * (password reset) and Shield-sent emails use the same SMTP server.
     */
    private function syncShieldEmailConfig(array $config): void
    {
        $map = [
            'Email.fromEmail'  => $config['from_email'],
            'Email.fromName'   => $config['from_name'],
            'Email.protocol'   => 'smtp',
            'Email.SMTPHost'   => $config['smtp_host'],
            'Email.SMTPUser'   => $config['smtp_user'],
            'Email.SMTPPass'   => $config['smtp_pass'],
            'Email.SMTPPort'   => $config['smtp_port'],
            'Email.SMTPCrypto' => $config['smtp_crypto'] === 'none' ? '' : $config['smtp_crypto'],
            'Email.mailType'   => 'text',
        ];

        foreach ($map as $key => $value) {
            if ($key === 'Email.SMTPPort') {
                $value = (int)$value;
            }
            setting($key, $value);
        }
    }
}
