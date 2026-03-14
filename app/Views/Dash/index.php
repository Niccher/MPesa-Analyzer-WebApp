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
        <p class="text-secondary mb-0">High-level summary of your financial data (Last 30 Days).</p>
    </div>
    <div class="d-flex gap-2">
        <button id="runAnalysis" class="btn btn-success rounded-pill px-4 shadow-sm">
            <i class="fa-solid fa-microchip me-2"></i> Analyse Data
        </button>
        <a href="<?= base_url('dashboard/search') ?>" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="fa-solid fa-magnifying-glass me-2"></i> Search and Filtering
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
// Sort alerts so Danger is top
$alerts = $smart_alerts ?? [];
usort($alerts, fn($a, $b) => $a['level'] === 'danger' ? -1 : 1);
?>

<?php if (!empty($alerts)): ?>
<div class="row mb-4">
    <div class="col-12">
        <h6 class="fw-bold mb-3 text-secondary text-uppercase small"><i class="fa-solid fa-robot me-2"></i>Intelligent Alerts</h6>
        <?php foreach ($alerts as $al): ?>
            <div class="alert alert-<?= $al['level'] ?> shadow-sm border-0 rounded-4 d-flex align-items-center gap-3 py-3 mb-2">
                <?php if ($al['type'] === 'low_balance'): ?>
                    <i class="fa-solid fa-wallet fa-2x"></i>
                <?php elseif ($al['type'] === 'unusual_activity'): ?>
                    <i class="fa-solid fa-shield-halved fa-2x"></i>
                <?php elseif ($al['type'] === 'fuliza_index'): ?>
                    <i class="fa-solid fa-percent fa-2x"></i>
                <?php endif; ?>
                <div>
                    <h6 class="fw-bold mb-1 text-dark"><?= $al['title'] ?></h6>
                    <div class="small text-dark"><?= $al['message'] ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($budget_alerts)): 
    $overLimit = array_filter($budget_alerts, fn($b) => $b['over_limit']);
    $nearLimit = array_filter($budget_alerts, fn($b) => !$b['over_limit'] && $b['percentage'] >= 80);
?>
    <?php if (!empty($overLimit)): ?>
    <div class="alert alert-danger shadow-sm border-0 rounded-4 d-flex align-items-center gap-3 mb-4">
        <i class="fa-solid fa-triangle-exclamation fa-2x"></i>
        <div>
            <h6 class="fw-bold mb-1">Budget Exceeded!</h6>
            <div class="small">
                <?php foreach ($overLimit as $b): ?>
                    <span class="me-3"><strong><?= htmlspecialchars($b['label'] ?: $b['category']) ?>:</strong> <?= number_format($b['percentage'], 0) ?>% used</span>
                <?php endforeach; ?>
            </div>
        </div>
        <a href="<?= base_url('dashboard/budget') ?>" class="btn btn-sm btn-danger ms-auto rounded-pill px-3 fw-bold">View Budget</a>
    </div>
    <?php endif; ?>

    <?php if (!empty($nearLimit) && empty($overLimit)): ?>
    <div class="alert alert-warning shadow-sm border-0 rounded-4 d-flex align-items-center gap-3 mb-4">
        <i class="fa-solid fa-bell fa-2x text-warning"></i>
        <div>
            <h6 class="fw-bold mb-1 text-dark">Approaching Budget Limit</h6>
            <div class="small text-dark">
                <?php foreach ($nearLimit as $b): ?>
                    <span class="me-3"><strong><?= htmlspecialchars($b['label'] ?: $b['category']) ?>:</strong> <?= number_format($b['percentage'], 0) ?>% used</span>
                <?php endforeach; ?>
            </div>
        </div>
        <a href="<?= base_url('dashboard/budget') ?>" class="btn btn-sm btn-warning text-dark ms-auto rounded-pill px-3 fw-bold">Manage Budget</a>
    </div>
    <?php endif; ?>
<?php endif; ?>

<div class="row g-4 mb-4">

    <!-- General Summary Card -->
    <div class="col-md-4">
        <div class="card summary-card card-purple h-100 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0 opacity-75">Finance Overview</h5>
                    <i class="fa-solid fa-wallet metric-icon opacity-50"></i>
                </div>
                <div class="metric-box text-center mb-0">
                    <h2 class="fw-bold mb-1">Ksh <?= number_format($metrics['current_balance'], 2) ?></h2>
                    <p class="small mb-0 opacity-75">Current Balance</p>
                </div>
                <div class="mt-3 small">
                    <div class="d-flex justify-content-between opacity-75 mb-1">
                        <span>Fuliza Limit:</span>
                        <span class="fw-bold">Ksh <?= number_format($metrics['fuliza_balance'], 2) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Spending Summary Card -->
    <div class="col-md-4">
        <div class="card summary-card card-blue h-100 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0 opacity-75">Spending (30 Days)</h5>
                    <i class="fa-solid fa-paper-plane metric-icon opacity-50"></i>
                </div>
                <div class="metric-box text-center mb-0">
                    <h2 class="fw-bold mb-1">Ksh <?= number_format($metrics['total_sent_30'], 2) ?></h2>
                    <p class="small mb-0 opacity-75">Total Outflow</p>
                </div>
                <div class="mt-3 small">
                    <div class="d-flex justify-content-between opacity-75 mb-1">
                        <span>Daily Avg:</span>
                        <span class="fw-bold text-white">Ksh <?= number_format($metrics['daily_avg_spend'] ?? 0, 0) ?></span>
                    </div>
                    <div class="d-flex justify-content-between opacity-75">
                        <span>Max Trans:</span>
                        <span class="fw-bold text-white">Ksh <?= number_format($metrics['max_transaction'] ?? 0, 0) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Income Summary Card -->
    <div class="col-md-4">
        <div class="card summary-card card-dark h-100 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0 opacity-75">Income (30 Days)</h5>
                    <i class="fa-solid fa-hand-holding-dollar metric-icon opacity-50"></i>
                </div>
                <div class="metric-box text-center mb-0">
                    <h2 class="fw-bold mb-1">Ksh <?= number_format($metrics['total_received_30'], 2) ?></h2>
                    <p class="small mb-0 opacity-75">Total Inflow</p>
                </div>
                <div class="mt-3 small">
                    <div class="d-flex justify-content-between opacity-75 mb-1">
                        <span>Banks/Other:</span>
                        <span class="fw-bold">Ksh <?= number_format($received_summary['banks'], 0) ?></span>
                    </div>
                    <div class="d-flex justify-content-between opacity-75">
                        <span>M-Shwari/KCB:</span>
                        <span class="fw-bold">Ksh <?= number_format($received_summary['mshwari_kcb'], 0) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($top_counterparties)): ?>
<div class="row g-4 mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-4 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Top Spending & Receiving Entities</h4>
                    <p class="text-secondary small mb-0">Your most frequent transaction partners and their total volumes.</p>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                    <i class="fa-solid fa-bolt me-1"></i> Smart Insights
                </span>
            </div>
            <div class="card-body px-4 pb-4">
                <?php 
                $maxVolume = !empty($top_counterparties) ? $top_counterparties[0]->total_amount : 1; 
                $colors = ['#5D5FEF', '#2ED573', '#FFA502', '#FF4757', '#1E90FF'];
                foreach ($top_counterparties as $index => $entity): 
                    $percentage = ($entity->total_amount / $maxVolume) * 100;
                    $color = $colors[$index % count($colors)];
                ?>
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 48px; height: 48px; background-color: <?= $color ?>20; border: 1px solid <?= $color ?>40;">
                                <i class="fa-solid <?= strpos(strtolower($entity->counterparty), 'bank') !== false ? 'fa-building-columns' : 'fa-user' ?>" style="color: <?= $color ?>;"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($entity->counterparty) ?></h6>
                                <small class="text-muted"><i class="fa-solid fa-repeat me-1"></i> <?= $entity->trans_count ?> transactions</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="h6 fw-bold mb-0" style="color: <?= $color ?>;">Ksh <?= number_format($entity->total_amount, 2) ?></span>
                        </div>
                    </div>
                    <div class="progress rounded-pill shadow-none" style="height: 8px; background-color: #f0f0f0;">
                        <div class="progress-bar rounded-pill" role="progressbar" 
                             style="width: <?= $percentage ?>%; background: <?= $color ?>;" 
                             aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
            </div>
        </div>
    </div>

<!-- SMS Detail Modal -->
<div class="modal fade" id="smsDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Transaction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="text-secondary small fw-bold text-uppercase">Time</label>
                    <p id="modal-sms-time" class="mb-0 fw-semibold"></p>
                </div>
                <div>
                    <label class="text-secondary small fw-bold text-uppercase">Message Content</label>
                    <div class="p-3 bg-light rounded-3 mt-1" id="modal-sms-body" style="white-space: pre-wrap; font-size: 0.9rem;"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal logic (Native JS)
        const smsModalEl = document.getElementById('smsDetailModal');
        const smsModal = new bootstrap.Modal(smsModalEl);
        const modalTime = document.getElementById('modal-sms-time');
        const modalBody = document.getElementById('modal-sms-body');

        // Dynamic binding for table buttons
        document.body.addEventListener('click', function(e) {
            const btn = e.target.closest('.view-sms-btn');
            if (btn) {
                modalTime.textContent = btn.getAttribute('data-time');
                modalBody.innerHTML = btn.getAttribute('data-body').replace(/\n/g, '<br>');
                smsModal.show();
            }
        });
        // Analysis button logic
        const analysisBtn = document.getElementById('runAnalysis');
        analysisBtn.addEventListener('click', function() {
            const originalText = analysisBtn.innerHTML;
            analysisBtn.disabled = true;
            analysisBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Analysing...';
            
            fetch('<?= base_url('dashboard/analyse') ?>')
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (!response.ok || !contentType || !contentType.includes('application/json')) {
                        return response.text().then(text => {
                            throw new Error(`Server returned ${response.status}: ${text.substring(0, 100)}...`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    alert(data.message);
                    if (data.status === 'success') {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Analysis Error:', error);
                    alert('Analysis failed: ' + error.message);
                })
                .finally(() => {
                    analysisBtn.disabled = false;
                    analysisBtn.innerHTML = originalText;
                });
        });
    });
</script>
<?= $this->endSection() ?>
