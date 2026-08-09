<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> Email Notifications - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-envelope me-2"></i> Email Notifications</h2>
        <p class="text-secondary mb-0">Configure the SMTP server and choose which events email your users.</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card settings-card mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-server me-2"></i> SMTP Configuration</h5>
                <form id="smtpForm">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">SMTP Host</label>
                            <input type="text" class="form-control" name="smtp_host" value="<?= esc($config['smtp_host']) ?>" placeholder="smtp.yourhost.com">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Port</label>
                            <input type="number" class="form-control" name="smtp_port" value="<?= esc($config['smtp_port']) ?>" placeholder="587">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Username</label>
                            <input type="text" class="form-control" name="smtp_user" value="<?= esc($config['smtp_user']) ?>" autocomplete="off" placeholder="SMTP username">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="smtp_pass" value="<?= esc($config['smtp_pass']) ?>" autocomplete="new-password" placeholder="SMTP password">
                                <button class="btn btn-outline-secondary" type="button" data-toggle-pass="smtp_pass"><i class="fa-solid fa-eye"></i></button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Encryption</label>
                            <select class="form-select" name="smtp_crypto">
                                <option value="tls" <?= ($config['smtp_crypto'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
                                <option value="ssl" <?= ($config['smtp_crypto'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                <option value="none" <?= ($config['smtp_crypto'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">From Email</label>
                            <input type="email" class="form-control" name="from_email" value="<?= esc($config['from_email']) ?>" placeholder="noreply@example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">From Name</label>
                            <input type="text" class="form-control" name="from_name" value="<?= esc($config['from_name']) ?>" placeholder="Mpesa Analyzer">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="enabled" value="1" role="switch" id="emailEnabled" <?= !empty($config['enabled']) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="emailEnabled">Enable email notifications</label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold"><i class="fa-solid fa-floppy-disk me-1"></i> Save SMTP Settings</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card settings-card mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-paper-plane me-2"></i> Send Test Email</h5>
                <p class="text-muted small mb-3">Verify the SMTP configuration by sending a test email.</p>
                <form id="testEmailForm" class="d-flex gap-2 flex-wrap">
                    <?= csrf_field() ?>
                    <input type="email" class="form-control flex-grow-1" name="test_email" placeholder="recipient@example.com" required>
                    <button type="submit" class="btn btn-outline-primary rounded-pill px-4 fw-semibold"><i class="fa-solid fa-paper-plane me-1"></i> Send Test Email</button>
                </form>
                <div id="testResult" class="mt-3"></div>
            </div>
        </div>

        <div class="card settings-card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-bell me-2"></i> Email Triggers</h5>
                <p class="text-muted small mb-3">Choose the events that should email your users. Turning a trigger off stops those emails from being sent.</p>
                <form id="triggersForm">
                    <?= csrf_field() ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr><th>Event</th><th>Description</th><th class="text-end">Enabled</th></tr>
                            </thead>
                            <tbody>
                                <?php
                                $triggerMeta = [
                                    'signup' => 'Send a welcome email when a new user signs up.',
                                    'password_reset' => 'Send password reset / magic link emails.',
                                    'report' => 'Send scheduled spending reports.',
                                    'ml_complete' => 'Notify a user when their ML analysis finishes and results are ready.',
                                ];
                                foreach ($triggerMeta as $key => $desc):
                                ?>
                                <tr>
                                    <td><strong><?= esc(ucwords(str_replace('_', ' ', $key))) ?></strong></td>
                                    <td class="text-muted small"><?= esc($desc) ?></td>
                                    <td class="text-end">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="triggers[<?= esc($key) ?>]" value="1" role="switch" <?= !empty($triggers[$key]) ? 'checked' : '' ?>>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold"><i class="fa-solid fa-floppy-disk me-1"></i> Save Triggers</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card settings-card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);">SMTP Status</h5>
                <?php if (empty($config['enabled'])): ?>
                    <div class="alert alert-warning py-2 small mb-3"><i class="fa-solid fa-triangle-exclamation me-1"></i> Email notifications are currently <strong>disabled</strong>.</div>
                <?php elseif ($config['smtp_host'] === ''): ?>
                    <div class="alert alert-warning py-2 small mb-3"><i class="fa-solid fa-triangle-exclamation me-1"></i> Enabled but no SMTP host configured.</div>
                <?php else: ?>
                    <div class="alert alert-success py-2 small mb-3"><i class="fa-solid fa-circle-check me-1"></i> SMTP configured and enabled.</div>
                <?php endif; ?>
                <ul class="small text-muted mb-0">
                    <li class="mb-1"><strong>Host:</strong> <?= esc($config['smtp_host'] ?: '—') ?></li>
                    <li class="mb-1"><strong>Port:</strong> <?= esc($config['smtp_port']) ?></li>
                    <li class="mb-1"><strong>Encryption:</strong> <?= esc(($config['smtp_crypto'] ?? 'tls') === 'none' ? 'none' : strtoupper($config['smtp_crypto'] ?? 'tls')) ?></li>
                    <li class="mb-1"><strong>From:</strong> <?= esc($config['from_email'] ?: '—') ?></li>
                </ul>
                <hr>
                <p class="small text-muted mb-0">
                    Shield-sent emails (password reset / magic links) automatically use the same SMTP configuration.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('smtpForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const body = new FormData(this);
    fetch('<?= base_url('admin/notifications/save-config') ?>', { method: 'POST', body })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                showAlert('Email Notifications', res.message, 'success');
            } else {
                showAlert('Email Notifications', res.message, 'danger');
            }
        });
});

document.getElementById('testEmailForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const body = new FormData(this);
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';
    fetch('<?= base_url('admin/notifications/send-test-email') ?>', { method: 'POST', body })
        .then(r => r.json())
        .then(res => {
            document.getElementById('testResult').innerHTML =
                '<div class="alert alert-' + (res.status === 'success' ? 'success' : 'danger') + ' py-2 small mb-0">' +
                '<i class="fa-solid fa-' + (res.status === 'success' ? 'circle-check' : 'circle-xmark') + ' me-1"></i> ' + res.message + '</div>';
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Send Test Email';
        });
});

document.getElementById('triggersForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const body = new FormData(this);
    fetch('<?= base_url('admin/notifications/save-triggers') ?>', { method: 'POST', body })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                showAlert('Email Notifications', res.message, 'success');
            } else {
                showAlert('Email Notifications', res.message, 'danger');
            }
        });
});

document.querySelectorAll('[data-toggle-pass]').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.querySelector('input[name="' + btn.dataset.togglePass + '"]');
        input.type = input.type === 'password' ? 'text' : 'password';
        btn.innerHTML = input.type === 'password' ? '<i class="fa-solid fa-eye"></i>' : '<i class="fa-solid fa-eye-slash"></i>';
    });
});
</script>
<?= $this->endSection() ?>
