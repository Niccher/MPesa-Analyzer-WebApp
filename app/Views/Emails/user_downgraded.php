<h1>Your Role Has Been Changed ⬇️</h1>
<p class="lead">Your account permissions have been updated. Your role is now <strong><?= esc($newRole ?? 'User') ?></strong>.</p>
<div class="info-box">
    <h3>Change Details</h3>
    <div class="info-row">
        <span class="info-label">Previous Role</span>
        <span class="info-value"><span class="badge badge-warning"><?= esc($oldRole ?? 'Admin') ?></span></span>
    </div>
    <div class="info-row">
        <span class="info-label">New Role</span>
        <span class="info-value"><span class="badge badge-info"><?= esc($newRole ?? 'User') ?></span></span>
    </div>
    <div class="info-row">
        <span class="info-label">Changed By</span>
        <span class="info-value"><?= esc($changedBy ?? 'System Administrator') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Effective Date</span>
        <span class="info-value"><?= esc($effectiveAt ?? date('Y-m-d H:i:s T')) ?></span>
    </div>
</div>
<p>Some admin features may no longer be accessible. Your regular user features remain unchanged.</p>
<a href="<?= esc(base_url('dashboard')) ?>" class="button">Go to Dashboard</a>
<div class="divider"></div>
<p style="font-size: 14px; color: #6b7280;">If you believe this change was made in error, please contact your system administrator.</p>
