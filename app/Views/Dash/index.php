<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Dashboard - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .summary-card {
        border: none;
        border-radius: 16px;
        color: white;
        transition: transform 0.3s ease;
        overflow: hidden;
    }
    
    .summary-card:hover {  transform: translateY(-5px); }

    .card-purple { background: linear-gradient(135deg, #6C5CE7 0%, #4834D4 100%); }
    .card-blue { background: linear-gradient(135deg, #3498DB 0%, #2980B9 100%); }
    .card-dark { background: linear-gradient(135deg, #34495E 0%, #2C3E50 100%); }

    .metric-box {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(5px);
        border-radius: 12px;
        padding: 1rem 0.5rem;
    }
    
    .metric-icon { font-size: 2rem; }
    .recent-activity-badge { background: rgba(0,0,0,0.1); border-radius: 8px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);">Dashboard Overview</h2>
        <p class="text-secondary mb-0">High-level summary of your financial data.</p>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row g-4 mb-4">
    <!-- General Summary -->
    <div class="col-lg-4 col-md-6">
        <div class="card summary-card card-purple h-100 shadow-sm">
            <div class="card-body p-4">
                <h4 class="card-title fw-bold mb-4"><i class="fa-solid fa-chart-line me-2"></i> General Summary</h4>
                <div class="row g-3 text-center mb-4">
                    <div class="col-4">
                        <div class="metric-box">
                            <div class="metric-icon mb-1">💰</div>
                            <h5 class="fw-bold mb-0"><?= number_format($total_uploads) ?></h5>
                            <small class="text-white-50 text-uppercase fw-semibold" style="font-size:0.75rem;">All Records</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="metric-box">
                            <div class="metric-icon mb-1">💳</div>
                            <h5 class="fw-bold mb-0">0</h5>
                            <small class="text-white-50 text-uppercase fw-semibold" style="font-size:0.75rem;">Balance</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="metric-box">
                            <div class="metric-icon mb-1">📦</div>
                            <h5 class="fw-bold mb-0">0</h5>
                            <small class="text-white-50 text-uppercase fw-semibold" style="font-size:0.75rem;">Fuliza</small>
                        </div>
                    </div>
                </div>
                <div class="recent-activity-badge p-3 text-center">
                    <i class="fa-solid fa-clock-rotate-left me-2"></i> Recent Activity: <?= count($recent_uploads) ?> tracked uploads
                </div>
            </div>
        </div>
    </div>

    <!-- Sent Summary -->
    <div class="col-lg-4 col-md-6">
        <div class="card summary-card card-blue h-100 shadow-sm">
            <div class="card-body p-4">
                <h4 class="card-title fw-bold mb-4"><i class="fa-solid fa-arrow-up-right-dots me-2"></i> Sent Summary</h4>
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div class="metric-box">
                            <div class="metric-icon mb-1">📤</div>
                            <h5 class="fw-bold mb-0">0</h5>
                            <small class="text-white-50 text-uppercase fw-semibold" style="font-size:0.75rem;">All Sent</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="metric-box">
                            <div class="metric-icon mb-1">📲</div>
                            <h5 class="fw-bold mb-0">0</h5>
                            <small class="text-white-50 text-uppercase fw-semibold" style="font-size:0.75rem;">M-Pesa</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="metric-box">
                            <div class="metric-icon mb-1">🏠</div>
                            <h5 class="fw-bold mb-0">0</h5>
                            <small class="text-white-50 text-uppercase fw-semibold" style="font-size:0.75rem;">Till / Paybill</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Received Summary -->
    <div class="col-lg-4 col-md-12">
        <div class="card summary-card card-dark h-100 shadow-sm">
            <div class="card-body p-4">
                <h4 class="card-title fw-bold mb-4"><i class="fa-solid fa-arrow-down-long me-2"></i> Received Summary</h4>
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div class="metric-box">
                            <div class="metric-icon mb-1">📥</div>
                            <h5 class="fw-bold mb-0">0</h5>
                            <small class="text-white-50 text-uppercase fw-semibold" style="font-size:0.75rem;">All Received</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="metric-box">
                            <div class="metric-icon mb-1">🏧</div>
                            <h5 class="fw-bold mb-0">0</h5>
                            <small class="text-white-50 text-uppercase fw-semibold" style="font-size:0.75rem;">Agent</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="metric-box">
                            <div class="metric-icon mb-1">🏛️</div>
                            <h5 class="fw-bold mb-0">0</h5>
                            <small class="text-white-50 text-uppercase fw-semibold" style="font-size:0.75rem;">Bank</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
