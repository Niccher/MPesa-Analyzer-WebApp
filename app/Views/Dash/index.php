<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Dashboard - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 24px;
    }

    .summary-card {
        background: var(--card-light);
        border-radius: var(--radius);
        padding: 24px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        color: white;
        transition: transform 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .summary-card:hover {
        transform: translateY(-5px);
    }

    .summary-card h2 {
        margin: 0 0 20px 0;
        font-size: 1.4rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        text-align: center;
    }

    .metric-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.15);
        padding: 15px 10px;
        border-radius: 12px;
        backdrop-filter: blur(5px);
    }

    .metric-icon {
        font-size: 1.8rem;
        margin-bottom: 5px;
    }

    .metric-value {
        font-size: 1.2rem;
        font-weight: 700;
    }

    .metric-label {
        font-size: 0.8rem;
        opacity: 0.9;
        font-weight: 500;
    }

    .recent-activity {
        margin-top: 20px;
        background: rgba(0,0,0,0.1);
        padding: 15px;
        border-radius: 10px;
        font-size: 0.9rem;
    }

    /* Card coloring variants */
    .card-purple { background: linear-gradient(135deg, #6C5CE7 0%, #4834D4 100%); }
    .card-blue { background: linear-gradient(135deg, #3498DB 0%, #2980B9 100%); }
    .card-dark { background: linear-gradient(135deg, #34495E 0%, #2C3E50 100%); }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }

    .summary-card {
        animation: fadeIn 0.5s ease-out forwards;
    }

    .summary-card:nth-child(2) { animation-delay: 0.1s; }
    .summary-card:nth-child(3) { animation-delay: 0.2s; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="dashboard-grid">
    <!-- General Summary -->
    <section class="summary-card card-purple">
        <h2><i class="fa-solid fa-chart-line"></i> General Summary</h2>
        <div class="metrics-grid">
            <div class="metric-item">
                <div class="metric-icon">💰</div>
                <span class="metric-value"><?= number_format($total_uploads) ?></span>
                <span class="metric-label">All Records</span>
            </div>
            <div class="metric-item">
                <div class="metric-icon">💳</div>
                <span class="metric-value">0</span>
                <span class="metric-label">Balance</span>
            </div>
            <div class="metric-item">
                <div class="metric-icon">📦</div>
                <span class="metric-value">0</span>
                <span class="metric-label">Fuliza</span>
            </div>
        </div>
        <div class="recent-activity">
            <i class="fa-solid fa-clock-rotate-left"></i> Recent Activity: <?= count($recent_uploads) ?> tracked uploads
        </div>
    </section>

    <!-- Sent Summary -->
    <section class="summary-card card-blue">
        <h2><i class="fa-solid fa-arrow-up-right-dots"></i> Sent Summary</h2>
        <div class="metrics-grid">
            <div class="metric-item">
                <div class="metric-icon">📤</div>
                <span class="metric-value">0</span>
                <span class="metric-label">All Sent</span>
            </div>
            <div class="metric-item">
                <div class="metric-icon">📲</div>
                <span class="metric-value">0</span>
                <span class="metric-label">M-Pesa</span>
            </div>
            <div class="metric-item">
                <div class="metric-icon">🏠</div>
                <span class="metric-value">0</span>
                <span class="metric-label">Till / Paybill</span>
            </div>
        </div>
    </section>

    <!-- Received Summary -->
    <section class="summary-card card-dark">
        <h2><i class="fa-solid fa-arrow-down-long"></i> Received Summary</h2>
        <div class="metrics-grid">
            <div class="metric-item">
                <div class="metric-icon">📥</div>
                <span class="metric-value">0</span>
                <span class="metric-label">All Received</span>
            </div>
            <div class="metric-item">
                <div class="metric-icon">🏧</div>
                <span class="metric-value">0</span>
                <span class="metric-label">Agent</span>
            </div>
            <div class="metric-item">
                <div class="metric-icon">🏛️</div>
                <span class="metric-value">0</span>
                <span class="metric-label">Bank</span>
            </div>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
