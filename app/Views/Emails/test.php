<h1>✅ Email Settings Verified!</h1>
<p class="lead">Your SMTP configuration is working correctly. This email uses the same styled template system as all other Mpesa Analyzer notifications.</p>
<div class="info-box">
    <h3>Email Details</h3>
    <div class="info-row">
        <span class="info-label">Recipient</span>
        <span class="info-value"><?= esc($to ?? '') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">SMTP Host</span>
        <span class="info-value"><?= esc($smtpHost ?? 'smtp.gmail.com') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Port</span>
        <span class="info-value"><?= esc($smtpPort ?? 587) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Encryption</span>
        <span class="info-value"><?= esc(ucfirst($smtpCrypto ?? 'tls')) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">From Address</span>
        <span class="info-value"><?= esc($fromEmail ?? '') ?></span>
    </div>
</div>
<a href="<?= esc(base_url('admin/notifications')) ?>" class="button">Back to Notifications</a>
<div class="divider"></div>
<p style="font-size: 14px; color: #6b7280;">Sent from the Mpesa Analyzer admin panel. All email notifications — including signup, password resets, spending reports, and system alerts — use this same styled template system.</p>
