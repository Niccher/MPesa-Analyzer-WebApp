<h1>Your Data Has Been Deleted 🗑️</h1>
<p class="lead">As requested, we have permanently deleted your M-Pesa transaction data from Mpesa Analyzer.</p>
<div class="info-box">
    <h3>Deletion Summary</h3>
    <div class="info-row">
        <span class="info-label">SMS Messages Deleted</span>
        <span class="info-value"><?= esc(number_format($smsDeleted ?? 0)) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Transactions Removed</span>
        <span class="info-value"><?= esc(number_format($transactionsDeleted ?? 0)) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Upload Files Removed</span>
        <span class="info-value"><?= esc(number_format($filesDeleted ?? 0)) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Account Status</span>
        <span class="info-value"><span class="badge badge-warning">Preserved</span></span>
    </div>
    <div class="info-row">
        <span class="info-label">Deleted At</span>
        <span class="info-value"><?= esc($deletedAt ?? date('Y-m-d H:i:s T')) ?></span>
    </div>
</div>
<p>Your account remains active. You can upload new statements at any time to start fresh analysis.</p>
<a href="<?= esc(base_url('dashboard')) ?>" class="button">Go to Dashboard</a>
<div class="divider"></div>
<p style="font-size: 14px; color: #6b7280;">This action cannot be undone. If you didn't request this deletion, please contact support immediately.</p>
