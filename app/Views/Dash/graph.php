<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Premium Analytics - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    :root {
        --glass-bg: var(--card-bg);
        --glass-border: var(--card-border);
    }

    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: 4px;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.07);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(31, 38, 135, 0.12);
    }

    .analytics-header {
        background: linear-gradient(135deg, #5D5FEF 0%, #a29bfe 100%);
        border-radius: 4px;
        color: white;
        padding: 40px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .analytics-header::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }

    .stat-badge {
        padding: 10px 20px;
        border-radius: 4px;
        font-weight: 600;
        background: rgba(255,255,255,0.2);
        color: white;
        border: 1px solid rgba(255,255,255,0.3);
    }

    .chart-container {
        position: relative;
        height: 350px;
        width: 100%;
    }

    .insight-card {
        border-left: 5px solid var(--primary);
        padding: 20px;
        background: #f8faff;
        border-radius: 4px;
    }

    .insight-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        font-size: 1.5rem;
        margin-bottom: 15px;
    }

    .badge-dir { font-size: 0.65rem; padding: 0.2em 0.5em; }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="analytics-header shadow-sm border-0">
    <div class="row align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3 mb-2">
                <span class="stat-badge"><i class="fa-solid fa-chart-line me-1"></i> Live Analytics</span>
                <span class="stat-badge"><i class="fa-solid fa-calendar-day me-1"></i> Last 30 Days</span>
            </div>
            <h1 class="fw-bold mb-1">Financial Intelligence Dashboard</h1>
            <p class="opacity-75 mb-0">Discover spending patterns and cash flow insights from your processed data.</p>
        </div>
        <div class="col-md-4 text-md-end mt-4 mt-md-0">
            <a href="<?= base_url('dashboard/search') ?>" class="btn btn-light rounded-pill px-4 py-2 fw-bold shadow-sm">
                <i class="fa-solid fa-magnifying-glass me-2"></i> Filter Data
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php $cs = currency_symbol(); ?>

<!-- Summary Row -->
<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="glass-card card p-4 h-100 border-0">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="insight-icon bg-primary-subtle text-primary mb-0">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
                <div>
                    <h6 class="text-secondary fw-bold mb-0">Total Volume</h6>
                    <h3 class="fw-bold mb-0"><?= $cs ?> <?= number_format(array_sum($analytics['spending'] ?? []) + array_sum($analytics['receiving'] ?? []), 0) ?></h3>
                </div>
            </div>
            <div class="small text-success mt-2"><i class="fa-solid fa-circle-check me-1"></i> 30-day aggregate</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="glass-card card p-4 h-100 border-0">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="insight-icon bg-success-subtle text-success mb-0">
                    <i class="fa-solid fa-arrow-down-long"></i>
                </div>
                <div>
                    <h6 class="text-secondary fw-bold mb-0">Total Inflow</h6>
                    <h3 class="fw-bold mb-0 text-success"><?= $cs ?> <?= number_format(array_sum($analytics['receiving'] ?? []), 0) ?></h3>
                </div>
            </div>
            <div class="small text-muted mt-2"><?= number_format(count(array_filter($analytics['receiving'] ?? []))) ?> days with activity</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="glass-card card p-4 h-100 border-0">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="insight-icon bg-danger-subtle text-danger mb-0">
                    <i class="fa-solid fa-arrow-up-long"></i>
                </div>
                <div>
                    <h6 class="text-secondary fw-bold mb-0">Total Outflow</h6>
                    <h3 class="fw-bold mb-0 text-danger"><?= $cs ?> <?= number_format(array_sum($analytics['spending'] ?? []), 0) ?></h3>
                </div>
            </div>
            <div class="small text-muted mt-2">Sent, Paid, and Withdrawn</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="glass-card card p-4 h-100 border-0">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="insight-icon bg-warning-subtle text-warning mb-0">
                    <i class="fa-solid fa-fire-flame-curved"></i>
                </div>
                <div>
                    <h6 class="text-secondary fw-bold mb-0">Net Cash Flow</h6>
                    <?php $netFlow = array_sum($analytics['receiving'] ?? []) - array_sum($analytics['spending'] ?? []); ?>
                    <h3 class="fw-bold mb-0 <?= $netFlow >= 0 ? 'text-success' : 'text-danger' ?>"><?= $cs ?> <?= number_format(abs($netFlow), 0) ?></h3>
                </div>
            </div>
            <div class="small mt-2 <?= $netFlow >= 0 ? 'text-success' : 'text-danger' ?>">
                <i class="fa-solid fa-<?= $netFlow >= 0 ? 'circle-arrow-up' : 'circle-arrow-down' ?> me-1"></i>
                <?= $netFlow >= 0 ? 'Positive' : 'Negative' ?> cash flow
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Main Cash Flow Chart -->
    <div class="col-12">
        <div class="glass-card card border-0 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">Cash Flow Trajectory</h5>
                <button class="btn btn-light btn-sm rounded-pill px-3 shadow-none border" onclick="exportGraph()">
                    <i class="fa-solid fa-download me-1"></i> Export Image
                </button>
            </div>
            <div class="chart-container">
                <canvas id="mainAnalysisChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Category Distribution -->
    <div class="col-lg-7">
        <div class="glass-card card border-0 p-4 h-100">
            <h5 class="fw-bold mb-4">Spending Distribution</h5>
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="chart-container" style="height: 280px;">
                        <canvas id="donutChart"></canvas>
                    </div>
                </div>
                <div class="col-md-5">
                    <?php 
                    $colors = ['#5D5FEF', '#2ED573', '#FFA502', '#FF4757', '#1E90FF', '#a29bfe', '#fd79a8'];
                    $idx = 0;
                    $total = array_sum($analytics['categories'] ?? []) ?: 1;
                    foreach (array_filter($analytics['categories'] ?? []) as $cat => $val): 
                        $perc = round(($val / $total) * 100);
                    ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-bold text-secondary"><?= htmlspecialchars($cat) ?></span>
                            <span class="small fw-bold"><?= $perc ?>%</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 6px;">
                            <div class="progress-bar rounded-pill" style="width: <?= $perc ?>%; background-color: <?= $colors[$idx % count($colors)] ?>;"></div>
                        </div>
                    </div>
                    <?php $idx++; endforeach; ?>
                    <?php if ($idx === 0): ?>
                        <p class="text-muted small text-center py-4">No category data available yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Smart Insights -->
    <div class="col-lg-5">
        <div class="glass-card card border-0 p-4 h-100">
            <h5 class="fw-bold mb-4">AI Observations</h5>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($ai_observations as $obs):
                    $borderClass = match($obs['type'] ?? 'neutral') {
                        'success' => 'border-success',
                        'danger'  => 'border-danger',
                        'warning' => 'border-warning',
                        default   => 'border-primary',
                    };
                    $iconColor = match($obs['type'] ?? 'neutral') {
                        'success' => 'text-success',
                        'danger'  => 'text-danger',
                        'warning' => 'text-warning',
                        default   => 'text-primary',
                    };
                ?>
                <div class="insight-card <?= $borderClass ?>">
                    <p class="mb-1 small text-secondary fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid <?= htmlspecialchars($obs['icon'] ?? 'fa-circle-info') ?> <?= $iconColor ?>"></i>
                        <?= htmlspecialchars($obs['label'] ?? 'Insight') ?>
                    </p>
                    <p class="mb-0 fw-bold small"><?= ($obs['text'] ?? '') ?></p>
                </div>
                <?php endforeach; ?>
                <?php if (empty($ai_observations)): ?>
                    <p class="text-muted small text-center py-4">No insights available yet. Process more data to generate observations.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const primaryColor = '#5D5FEF';
    const successColor = '#2ED573';
    const dangerColor = '#FF4757';
    const warningColor = '#FFA502';
    const infoColor = '#1e90ff';

    Chart.defaults.font.family = "'Outfit', sans-serif";
    Chart.defaults.color = '#666';

    // 1. Main Analysis Chart
    const ctxMain = document.getElementById('mainAnalysisChart').getContext('2d');
    const gradientIn = ctxMain.createLinearGradient(0, 0, 0, 350);
    gradientIn.addColorStop(0, 'rgba(46, 213, 115, 0.3)');
    gradientIn.addColorStop(1, 'rgba(46, 213, 115, 0)');

    const gradientOut = ctxMain.createLinearGradient(0, 0, 0, 350);
    gradientOut.addColorStop(0, 'rgba(93, 95, 239, 0.3)');
    gradientOut.addColorStop(1, 'rgba(93, 95, 239, 0)');

    const mainChart = new Chart(ctxMain, {
        type: 'line',
        data: {
            labels: <?= json_encode($analytics['labels'] ?? []) ?>,
            datasets: [
                {
                    label: 'Money Inflow',
                    data: <?= json_encode($analytics['receiving'] ?? []) ?>,
                    borderColor: successColor,
                    backgroundColor: gradientIn,
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#fff',
                    pointBorderWidth: 3
                },
                {
                    label: 'Money Outflow',
                    data: <?= json_encode($analytics['spending'] ?? []) ?>,
                    borderColor: primaryColor,
                    backgroundColor: gradientOut,
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#fff',
                    pointBorderWidth: 3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 6, padding: 20 } },
                tooltip: { 
                    backgroundColor: '#1e293b',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    padding: 12,
                    displayColors: true,
                    cornerRadius: 8,
                    intersect: false,
                    mode: 'index'
                }
            },
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: 'rgba(0,0,0,0.03)', borderDash: [5, 5] },
                    ticks: { callback: value => '<?= $cs ?> ' + value.toLocaleString() }
                },
                x: { 
                    grid: { display: false },
                    ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 10 }
                }
            }
        }
    });

    // 2. Donut Chart
    const labels = <?= json_encode(array_keys(array_filter($analytics['categories'] ?? []))) ?>;
    const values = <?= json_encode(array_values(array_filter($analytics['categories'] ?? []))) ?>;
    if (labels.length > 0) {
        const ctxDonut = document.getElementById('donutChart').getContext('2d');
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: [primaryColor, successColor, warningColor, dangerColor, infoColor, '#a29bfe', '#fd79a8'],
                    borderWidth: 0,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '80%',
                plugins: { legend: { display: false } },
                animation: { animateScale: true, animateRotate: true }
            }
        });
    } else {
        document.getElementById('donutChart').innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-muted small">No data</div>';
    }

    // 3. Export
    window.exportGraph = function() {
        const link = document.createElement('a');
        link.download = 'mpesa_analytics_graph.png';
        link.href = mainChart.toBase64Image();
        link.click();
        if (typeof showAlert === 'function') {
            showAlert('Export Success', 'Graph image has been downloaded.', 'success');
        }
    }
</script>
<?= $this->endSection() ?>
