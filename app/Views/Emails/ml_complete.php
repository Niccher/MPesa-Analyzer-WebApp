<h1>Your Analysis is Ready! 🤖</h1>
<p class="lead">Your M-Pesa transaction analysis has finished processing. New insights and categorized transactions are now available.</p>
<div class="info-box">
    <h3>Analysis Summary</h3>
    <div class="info-row">
        <span class="info-label">Messages Processed</span>
        <span class="info-value"><?= esc(number_format($messagesProcessed ?? 0)) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">New Transactions</span>
        <span class="info-value"><?= esc(number_format($newTransactions ?? 0)) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Categories Identified</span>
        <span class="info-value"><?= esc(number_format($categoriesFound ?? 0)) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Processing Time</span>
        <span class="info-value"><?= esc($duration ?? 'N/A') ?></span>
    </div>
</div>
<a href="<?= esc(base_url('dashboard')) ?>" class="button">View Insights</a>
<div class="divider"></div>
<p style="font-size: 14px; color: #6b7280;">Your data was analyzed using our ML backend. Results may improve over time as the model learns from your spending patterns.</p>
