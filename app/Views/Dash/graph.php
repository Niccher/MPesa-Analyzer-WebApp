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
        <p class="text-secondary mb-0">A visual breakdown of tracked M-Pesa activity</p>
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
                <div class="chart-container">
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
                <h5 class="card-title fw-bold mb-4"><i class="fa-solid fa-boxes-stacked me-2" style="color: #FF4757"></i> Fuliza Usage</h5>
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
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [
                {
                    label: 'Money In',
                    data: [12000, 19000, 3000, 5000, 20000, 3000, 4000],
                    borderColor: successColor,
                    backgroundColor: 'rgba(46, 213, 115, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Money Out',
                    data: [5000, 25000, 2000, 8000, 15000, 10000, 6000],
                    borderColor: dangerColor,
                    backgroundColor: 'rgba(255, 71, 87, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true
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
            labels: ['Sent to Mobile', 'Till/Paybill', 'Withdrawal', 'Airtime/Data'],
            datasets: [{
                data: [45, 25, 20, 10],
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
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [{
                label: 'Loans Taken',
                data: [1500, 3000, 500, 4000],
                backgroundColor: dangerColor,
                borderRadius: 6
            },
            {
                label: 'Loans Repaid',
                data: [0, 4500, 0, 2000],
                backgroundColor: successColor,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top' } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                x: { grid: { display: false } }
            }
        }
    });
</script>
<?= $this->endSection() ?>
