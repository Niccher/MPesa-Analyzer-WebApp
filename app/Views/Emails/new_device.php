<h1>New Device Connected 📱</h1>
<p class="lead">A new device was linked to your Mpesa Analyzer account.</p>
<div class="info-box">
    <h3>Device Details</h3>
    <div class="info-row">
        <span class="info-label">Device Name</span>
        <span class="info-value"><?= esc($deviceName ?? 'Unknown Device') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Device Type</span>
        <span class="info-value"><?= esc($deviceType ?? 'Mobile') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Platform</span>
        <span class="info-value"><?= esc($platform ?? 'Unknown') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">IP Address</span>
        <span class="info-value"><?= esc($ipAddress ?? 'Unknown') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Location (approx.)</span>
        <span class="info-value"><?= esc($location ?? 'Unknown') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Connected At</span>
        <span class="info-value"><?= esc($connectedAt ?? date('Y-m-d H:i:s T')) ?></span>
    </div>
</div>
<p>If this was you, no action is needed. If you don't recognize this device, please secure your account immediately.</p>
<a href="<?= esc(base_url('settings/security')) ?>" class="button">Review Devices</a>
<div class="divider"></div>
<p style="font-size: 14px; color: #6b7280;">You can manage all connected devices from your <a href="<?= esc(base_url('settings/security')) ?>">Security Settings</a>.</p>
