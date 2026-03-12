<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Graph Analytics - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .analytics-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
        margin-top: 20px;
    }

    .chart-card {
        background: white;
        border-radius: var(--radius);
        padding: 24px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
    
    .chart-card.full-width {
        grid-column: span 2;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .chart-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }

    @media (max-width: 900px) {
        .analytics-grid {
            grid-template-columns: 1fr;
        }
        .chart-card.full-width {
            grid-column: span 1;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="header-section" style="margin-bottom: 20px;">
    <h2 style="font-weight: 700; color: var(--primary);">Data Visualizations</h2>
    <p style="color: var(--text-light);">A visual breakdown of tracked M-Pesa activity</p>
</div>

<div class="analytics-grid">
    <!-- Cash Flow Line Chart -->
    <div class="chart-card full-width">
        <div class="chart-header">
            <h3 class="chart-title"><i class="fa-solid fa-money-bill-trend-up" style="color: #2ED573"></i> Cash Flow (Money In vs. Money Out)</h3>
        </div>
        <div class="chart-container">
            <canvas id="cashFlowChart"></canvas>
        </div>
    </div>

    <!-- Spending Breakdown Doughnut Chart -->
    <div class="chart-card">
        <div class="chart-header">
            <h3 class="chart-title"><i class="fa-solid fa-chart-pie" style="color: var(--primary)"></i> Category Breakdown</h3>
        </div>
        <div class="chart-container">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>

    <!-- Fuliza Dependency Bar Chart -->
    <div class="chart-card">
        <div class="chart-header">
            <h3 class="chart-title"><i class="fa-solid fa-boxes-stacked" style="color: #FF4757"></i> Fuliza Usage</h3>
        </div>
        <div class="chart-container">
            <canvas id="fulizaChart"></canvas>
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
