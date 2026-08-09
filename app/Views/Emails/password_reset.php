<h1>Your Magic Login Link 🔐</h1>
<p class="lead">Click the button below to securely log in to your Mpesa Analyzer account. This link is unique to you and expires in <strong>15 minutes</strong>.</p>
<a href="<?= esc($magicLink ?? '#') ?>" class="button">Log In Securely</a>
<div class="divider"></div>
<div class="info-box">
    <h3>Login Details</h3>
    <div class="info-row">
        <span class="info-label">IP Address</span>
        <span class="info-value"><?= esc($ipAddress ?? 'Unknown') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Device</span>
        <span class="info-value"><?= esc($userAgent ?? 'Unknown') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Date & Time</span>
        <span class="info-value"><?= esc($date ?? date('Y-m-d H:i:s T')) ?></span>
    </div>
</div>
<p style="font-size: 14px; color: #6b7280;">If you didn't request this login link, please ignore this email. Your account remains secure.</p>
