<h1>Your <?= esc(ucfirst($frequency ?? 'Monthly')) ?> Spending Report 📊</h1>
<p class="lead">Your scheduled Mpesa Analyzer report for <strong><?= esc($period ?? 'this period') ?></strong> is ready.</p>
<p>Here's a quick summary:</p>
<div class="info-box">
    <h3>Report Summary</h3>
    <div class="info-row">
        <span class="info-label">Period</span>
        <span class="info-value"><?= esc($period ?? 'N/A') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Frequency</span>
        <span class="info-value"><span class="badge badge-info"><?= esc(ucfirst($frequency ?? 'monthly')) ?></span></span>
    </div>
    <div class="info-row">
        <span class="info-label">Total Transactions</span>
        <span class="info-value"><?= esc(number_format($totalTransactions ?? 0)) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Total Spent</span>
        <span class="info-value">KES <?= esc(number_format($totalSpent ?? 0, 2)) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Top Category</span>
        <span class="info-value"><?= esc($topCategory ?? 'N/A') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Average Daily Spend</span>
        <span class="info-value">KES <?= esc(number_format($avgDaily ?? 0, 2)) ?></span>
    </div>
</div>
<a href="<?= esc(base_url('reports')) ?>" class="button">View Full Report</a>
<div class="divider"></div>
<p style="font-size: 14px; color: #6b7280;">You can adjust your report frequency or disable scheduled reports in <a href="<?= esc(base_url('settings/notifications')) ?>">Email Preferences</a>.</p>
