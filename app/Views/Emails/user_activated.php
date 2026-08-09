<h1>Your Account Has Been Activated ✅</h1>
<p class="lead">Your Mpesa Analyzer account has been activated. You now have full access to all features.</p>
<div class="info-box">
    <h3>Activation Details</h3>
    <div class="info-row">
        <span class="info-label">Status</span>
        <span class="info-value"><span class="badge badge-success">Active</span></span>
    </div>
    <div class="info-row">
        <span class="info-label">Activated By</span>
        <span class="info-value"><?= esc($activatedBy ?? 'System Administrator') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Effective Date</span>
        <span class="info-value"><?= esc($effectiveAt ?? date('Y-m-d H:i:s T')) ?></span>
    </div>
</div>
<p>You can now log in and use all features of Mpesa Analyzer.</p>
<a href="<?= esc(base_url('login')) ?>" class="button">Log In Now</a>
<div class="divider"></div>
<p style="font-size: 14px; color: #6b7280;">If you didn't expect this activation, please contact support.</p>
