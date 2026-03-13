<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Graph Analytics - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .chart-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: none;
    }

    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);">Data Visualizations</h2>
        <p class="text-secondary mb-0">A visual breakdown of tracked activity (Last 30 Days)</p>
    </div>
    <div>
        <a href="<?= base_url('dashboard/search') ?>" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="fa-solid fa-magnifying-glass me-2"></i> Search and Filtering
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row g-4 mb-4">
    <!-- Cash Flow Line Chart -->
    <div class="col-12">
        <div class="card chart-card h-100">
            <div class="card-body p-4">
                <h5 class="card-title fw-bold mb-4"><i class="fa-solid fa-money-bill-trend-up me-2" style="color: #2ED573"></i> Cash Flow (Money In vs. Money Out)</h5>
                <div class="chart-container" style="height: 350px;">
                    <canvas id="cashFlowChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Spending Breakdown Doughnut Chart -->
    <div class="col-lg-6">
        <div class="card chart-card h-100">
            <div class="card-body p-4">
                <h5 class="card-title fw-bold mb-4"><i class="fa-solid fa-chart-pie me-2" style="color: var(--primary)"></i> Category Breakdown</h5>
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Fuliza Dependency Bar Chart -->
    <div class="col-lg-6">
        <div class="card chart-card h-100">
            <div class="card-body p-4">
                <h5 class="card-title fw-bold mb-4"><i class="fa-solid fa-boxes-stacked me-2" style="color: #FF4757"></i> Fuliza Usage (Taken vs Paid)</h5>
                <div class="chart-container">
                    <canvas id="fulizaChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Styling constants
    const primaryColor = '#5D5FEF';
    const successColor = '#2ED573';
    const dangerColor = '#FF4757';
    const warningColor = '#FFA502';
    const infoColor = '#1e90ff';

    Chart.defaults.font.family = "'Outfit', sans-serif";
    Chart.defaults.color = '#666';

    // 1. Cash Flow Chart (Line)
    const ctxCashFlow = document.getElementById('cashFlowChart').getContext('2d');
    new Chart(ctxCashFlow, {
        type: 'line',
        data: {
            labels: <?= json_encode($analytics['labels']) ?>,
            datasets: [
                {
                    label: 'Money In',
                    data: <?= json_encode($analytics['receiving']) ?>,
                    borderColor: successColor,
                    backgroundColor: 'rgba(46, 213, 115, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 2
                },
                {
                    label: 'Money Out',
                    data: <?= json_encode($analytics['spending']) ?>,
                    borderColor: dangerColor,
                    backgroundColor: 'rgba(255, 71, 87, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top' } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                x: { grid: { display: false } }
            }
        }
    });

    // 2. Category Doughnut
    const ctxCategory = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctxCategory, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($analytics['categories'])) ?>,
            datasets: [{
                data: <?= json_encode(array_values($analytics['categories'])) ?>,
                backgroundColor: [primaryColor, successColor, warningColor, infoColor],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'right' } },
            cutout: '70%'
        }
    });

    // 3. Fuliza Bar Chart
    const ctxFuliza = document.getElementById('fulizaChart').getContext('2d');
    new Chart(ctxFuliza, {
        type: 'bar',
        data: {
            labels: <?= json_encode($analytics['labels']) ?>,
            datasets: [{
                label: 'Loans Taken',
                data: <?= json_encode($analytics['fuliza_taken']) ?>,
                backgroundColor: dangerColor,
                borderRadius: 4
            },
            {
                label: 'Loans Repaid',
                data: <?= json_encode($analytics['fuliza_paid']) ?>,
                backgroundColor: successColor,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { position: 'top' },
                tooltip: { intersect: false, mode: 'index' }
            },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                x: { grid: { display: false }, ticks: { display: false } }
            }
        }
    });
</script>
<?= $this->endSection() ?>
