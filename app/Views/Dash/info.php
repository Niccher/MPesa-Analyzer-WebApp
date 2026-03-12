<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Info & Settings - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 24px;
        margin-top: 20px;
    }

    .info-card {
        background: white;
        border-radius: var(--radius);
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }

    .info-card h3 {
        color: var(--primary);
        font-weight: 600;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .setting-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .setting-item:last-child {
        border-bottom: none;
    }

    .setting-label {
        font-weight: 500;
        color: var(--text-dark);
        display: flex;
        flex-direction: column;
    }

    .setting-sub {
        font-size: 0.8rem;
        color: var(--text-light);
        margin-top: 4px;
    }

    .stat-badge {
        background: rgba(93, 95, 239, 0.1);
        color: var(--primary);
        padding: 5px 12px;
        border-radius: 20px;
        font-weight: 700;
    }

    .btn-action {
        background: var(--primary);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.2s;
        font-weight: 500;
        text-decoration: none;
    }

    .btn-action:hover {
        background: #4A4CD3;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="header-section">
    <h2 style="font-weight: 700; color: var(--primary);">System Information</h2>
    <p style="color: var(--text-light);">Manage your account and view application statuses.</p>
</div>

<div class="info-grid">
    <!-- User Profile Card -->
    <div class="info-card">
        <h3><i class="fa-solid fa-user"></i> Account Profile</h3>
        <div class="setting-item">
            <div class="setting-label">
                Username
                <span class="setting-sub"><?= auth()->user()->username ?? 'N/A' ?></span>
            </div>
        </div>
        <div class="setting-item">
            <div class="setting-label">
                Email Address
                <span class="setting-sub"><?= auth()->user()->email ?? 'N/A' ?></span>
            </div>
        </div>
        <div class="setting-item">
            <div class="setting-label">
                Password
                <span class="setting-sub">Last updated recently</span>
            </div>
            <a href="#" class="btn-action">Update</a>
        </div>
    </div>

    <!-- System Stats Card -->
    <div class="info-card">
        <h3><i class="fa-solid fa-server"></i> Application Data</h3>
        <div class="setting-item">
            <div class="setting-label">
                Total Tracked Records
                <span class="setting-sub">Raw SMS messages safely parsed</span>
            </div>
            <span class="stat-badge"><?= number_format($total_processed) ?></span>
        </div>
        <div class="setting-item">
            <div class="setting-label">
                Active Devices
                <span class="setting-sub">Android phones linked to account</span>
            </div>
            <span class="stat-badge">1</span>
        </div>
        <div class="setting-item">
            <div class="setting-label">
                Data Sync Status
                <span class="setting-sub">Connection to Android App</span>
            </div>
            <span class="stat-badge" style="background: rgba(46, 213, 115, 0.1); color: #2ED573;">Online</span>
        </div>
    </div>

    <!-- Data Dictionary Card -->
    <div class="info-card" style="grid-column: 1 / -1;">
        <h3><i class="fa-solid fa-book"></i> Category Legend (Data Dictionary)</h3>
        <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 15px;">A quick reference guide for how the system categorizes your transactions.</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #2ED573;">
                <strong style="display: block; margin-bottom: 5px;">Receive</strong>
                <span style="font-size: 0.85rem; color: #666;">Money sent directly to your phone by another user.</span>
            </div>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid var(--primary);">
                <strong style="display: block; margin-bottom: 5px;">Sent To LNM</strong>
                <span style="font-size: 0.85rem; color: #666;">Lipa Na M-Pesa (Buy Goods or Paybill).</span>
            </div>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #FFA502;">
                <strong style="display: block; margin-bottom: 5px;">Withdraw</strong>
                <span style="font-size: 0.85rem; color: #666;">Cash extracted from an M-Pesa Agent.</span>
            </div>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #FF4757;">
                <strong style="display: block; margin-bottom: 5px;">Error / Failed</strong>
                <span style="font-size: 0.85rem; color: #666;">Transactions that did not go through (wrong PIN, insufficient funds).</span>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
