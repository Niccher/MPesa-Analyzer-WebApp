<h1>Your Role Has Been Upgraded ⬆️</h1>
<p class="lead">Your account permissions have been updated. You now have <strong><?= esc($newRole ?? 'Admin') ?></strong> access.</p>
<div class="info-box">
    <h3>Change Details</h3>
    <div class="info-row">
        <span class="info-label">Previous Role</span>
        <span class="info-value"><span class="badge badge-info"><?= esc($oldRole ?? 'User') ?></span></span>
    </div>
    <div class="info-row">
        <span class="info-label">New Role</span>
        <span class="info-value"><span class="badge badge-success"><?= esc($newRole ?? 'Admin') ?></span></span>
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
<p>Your new permissions are active immediately. You may need to refresh your session to see all changes.</p>
<a href="<?= esc(base_url('admin')) ?>" class="button">Open Admin Panel</a>
<div class="divider"></div>
<p style="font-size: 14px; color: #6b7280;">If you believe this change was made in error, please contact your system administrator.</p>
