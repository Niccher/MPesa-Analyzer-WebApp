<h1>Maintenance Complete ✅</h1>
<p class="lead">Mpesa Analyzer maintenance has finished. The platform is now fully operational.</p>
<div class="info-box">
    <h3>Maintenance Summary</h3>
    <div class="info-row">
        <span class="info-label">Status</span>
        <span class="info-value"><span class="badge badge-success">Normal Operation</span></span>
    </div>
    <div class="info-row">
        <span class="info-label">Started At</span>
        <span class="info-value"><?= esc($startedAt ?? 'N/A') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Ended At</span>
        <span class="info-value"><?= esc($endedAt ?? date('Y-m-d H:i:s T')) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Duration</span>
        <span class="info-value"><?= esc($duration ?? 'N/A') ?></span>
    </div>
</div>
<p>All features are now available. You can log in and continue using Mpesa Analyzer normally.</p>
<a href="<?= esc(base_url('login')) ?>" class="button">Log In Now</a>
<div class="divider"></div>
<p style="font-size: 14px; color: #6b7280;">Scheduled jobs have resumed. If you experience any issues, please contact support.</p>
