<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Monthly Reports & Insights - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .report-hero { background: linear-gradient(135deg, #5D5FEF 0%, #a29bfe 100%); border-radius: 20px; color: white; padding: 32px 40px; margin-bottom: 28px; position: relative; overflow: hidden; }
    .report-hero::after { content: ''; position: absolute; top: -60%; right: -5%; width: 280px; height: 280px; background: rgba(255,255,255,0.08); border-radius: 50%; }
    .glass-card { background: var(--card-bg); backdrop-filter: blur(10px); border: 1px solid var(--card-border); border-radius: 20px; box-shadow: 0 6px 24px rgba(31,38,135,0.06); transition: transform .2s, box-shadow .2s; }
    .glass-card:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(31,38,135,0.1); }
    .metric-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; margin-bottom: 12px; }
    select.period-select { border-radius: 12px; border: 1px solid var(--card-border); padding: 8px 16px; font-weight: 600; color: var(--primary); background: var(--bg-color); cursor: pointer; }
    .trend-badge { padding: 4px 10px; border-radius: 20px; font-weight: bold; font-size: 0.8rem; }
    .trend-up { background: rgba(255,71,87,0.15); color: #FF4757; }
    .trend-down { background: rgba(46,213,115,0.15); color: #2ED573; }
    .trend-flat { background: rgba(160,160,160,0.15); color: #888; }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="report-hero shadow-sm">
    <div class="row align-items-center">
        <div class="col-md-7">
            <p class="mb-1 opacity-75 small fw-bold text-uppercase">Financial Report & Insights</p>
            <h1 class="fw-bold mb-1 fs-3"><?= $report['month_name'] ?> <?= $report['year'] ?></h1>
            <p class="opacity-75 mb-0"><?= number_format($report['tx_count']) ?> transactions analysed this month</p>
        </div>
        <div class="col-md-5 d-flex justify-content-md-end align-items-center gap-3 mt-3 mt-md-0 position-relative" style="z-index: 2;">
            <form method="GET" action="<?= base_url('dashboard/reports') ?>" class="d-flex align-items-center gap-2">
                <select name="month_key" id="monthPicker" class="period-select" onchange="applyMonth(this.value)">
                    <?php foreach ($months as $m): ?>
                        <option value="<?= $m['y'] ?>-<?= $m['m'] ?>" <?= $selectedKey === $m['value'] ? 'selected' : '' ?>>
                            <?= $m['label'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="year" id="hidYear" value="<?= $report['year'] ?>">
                <input type="hidden" name="month" id="hidMonth" value="<?= $report['month'] ?>">
            </form>
            <a href="<?= base_url('dashboard/reports/print?year='.$report['year'].'&month='.$report['month']) ?>" 
               target="_blank" class="btn btn-light fw-bold rounded-pill px-4">
                <i class="fa-solid fa-print me-2"></i>Export PDF
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Summary Cards & Overall Trends -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="glass-card p-4 h-100">
            <div class="d-flex justify-content-between">
                <div class="metric-icon bg-danger-subtle text-danger"><i class="fa-solid fa-arrow-up-long"></i></div>
                <?php 
                $trendClass = $trends['trend'] === 'down' ? 'trend-down' : ($trends['trend'] === 'up' ? 'trend-up' : 'trend-flat');
                $trendIcon  = $trends['trend'] === 'down' ? '↓' : ($trends['trend'] === 'up' ? '↑' : '-');
                ?>
                <span class="trend-badge <?= $trendClass ?>"><?= $trendIcon ?> <?= $trends['percentage'] ?>% vs Last Mo.</span>
            </div>
            <p class="text-secondary fw-bold small mb-1">Total Outflow</p>
            <h3 class="fw-bold mb-0 text-danger">Ksh <?= number_format($report['total_out'], 2) ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="glass-card p-4 h-100">
            <div class="metric-icon bg-success-subtle text-success"><i class="fa-solid fa-arrow-down-long"></i></div>
            <p class="text-secondary fw-bold small mb-1">Total Inflow</p>
            <h3 class="fw-bold mb-0 text-success">Ksh <?= number_format($report['total_in'], 2) ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="glass-card p-4 h-100">
            <div class="metric-icon <?= $report['net'] >= 0 ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning' ?>">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
            <p class="text-secondary fw-bold small mb-1">Net Balance</p>
            <h3 class="fw-bold mb-0 <?= $report['net'] >= 0 ? 'text-primary' : 'text-warning' ?>">
                <?= $report['net'] >= 0 ? '+' : '' ?>Ksh <?= number_format($report['net'], 2) ?>
            </h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="glass-card p-4 h-100">
            <div class="metric-icon bg-info-subtle text-info"><i class="fa-solid fa-receipt"></i></div>
            <p class="text-secondary fw-bold small mb-1">Transactions</p>
            <h3 class="fw-bold mb-0 text-info"><?= number_format($report['tx_count']) ?></h3>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Daily Cash Flow -->
    <div class="col-lg-8">
        <div class="glass-card p-4 h-100">
            <h5 class="fw-bold mb-4">Daily Cash Flow</h5>
            <div style="position: relative; height: 320px;">
                <canvas id="dailyFlowChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Category Split -->
    <div class="col-lg-4">
        <div class="glass-card p-4 h-100">
            <h5 class="fw-bold mb-4">Category Split</h5>
            <div style="position: relative; height:240px;">
                <canvas id="catDonut"></canvas>
            </div>
            <div class="mt-4">
                <?php
                $catColors = ['#2ED573','#5D5FEF','#FFA502','#FF4757','#a0a0a0'];
                $idx = 0;
                foreach ($report['categories'] as $name => $val):
                    if ($val == 0) continue;
                ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <span style="width:10px;height:10px;background:<?= $catColors[$idx%5] ?>;border-radius:3px;"></span>
                        <small class="text-muted"><?= $name ?></small>
                    </div>
                    <small class="fw-bold">Ksh <?= number_format($val, 0) ?></small>
                </div>
                <?php $idx++; endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- AI Subscriptions/Recurring Detector -->
<?php if (!empty($recurring)): ?>
<div class="glass-card p-4 mb-4 border-start border-4 border-primary">
    <div class="d-flex align-items-center gap-3 mb-4">
        <i class="fa-solid fa-clock-rotate-left fa-2x text-primary"></i>
        <div>
            <h5 class="fw-bold mb-0">Detected Recurring Payments</h5>
            <small class="text-muted">AI identified identical transactions occurring regularly over the last 6 months.</small>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-borderless align-middle mb-0">
            <thead class="text-muted small text-uppercase">
                <tr><th>Counterparty</th><th>Consistent Amount</th><th>Occurrences</th><th>Last Paid</th></tr>
            </thead>
            <tbody>
                <?php foreach ($recurring as $rec): ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($rec->counterparty) ?></td>
                    <td class="text-danger fw-bold">Ksh <?= number_format($rec->amount, 2) ?></td>
                    <td><span class="badge bg-light text-dark"><?= $rec->occurs ?> times</span></td>
                    <td class="text-muted timestamp" data-timestamp="<?= strtotime($rec->last_paid) ?>">
                        <?= date('d M Y', strtotime($rec->last_paid)) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Top Counterparties -->
<?php if (!empty($report['top_counterparties'])): ?>
<div class="glass-card p-4">
    <h5 class="fw-bold mb-4">Top Entities You Paid This Month</h5>
    <div class="row g-3">
        <?php
        $maxAmt = $report['top_counterparties'][0]->total_amount ?? 1;
        $colors2 = ['#5D5FEF','#2ED573','#FFA502','#FF4757','#1E90FF'];
        foreach ($report['top_counterparties'] as $i => $cp):
            $perc = round(($cp->total_amount / $maxAmt) * 100);
            $c = $colors2[$i % 5];
        ?>
        <div class="col-md-6">
            <div class="d-flex align-items-center gap-3 mb-1">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:<?= $c ?>20;">
                    <i class="fa-solid fa-user" style="color:<?= $c ?>;"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold small"><?= htmlspecialchars($cp->counterparty) ?></span>
                        <span class="small" style="color:<?= $c ?>">Ksh <?= number_format($cp->total_amount, 0) ?></span>
                    </div>
                    <div class="progress rounded-pill mt-1" style="height:5px;background:#f0f0f0;">
                        <div class="progress-bar" style="width:<?= $perc ?>%;background:<?= $c ?>;"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function applyMonth(val) {
    const [y, m] = val.split('-');
    document.getElementById('hidYear').value = y;
    document.getElementById('hidMonth').value = m;
    document.querySelector('form').submit();
}

Chart.defaults.font.family = "'Outfit', sans-serif";

new Chart(document.getElementById('dailyFlowChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($report['labels']) ?>,
        datasets: [{ label: 'Inflow', data: <?= json_encode($report['inflow']) ?>, backgroundColor: 'rgba(46,213,115,0.7)', borderRadius: 6, stack: '0' },
                   { label: 'Outflow', data: <?= json_encode($report['outflow']) ?>, backgroundColor: 'rgba(255,71,87,0.7)', borderRadius: 6, stack: '1' }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { tooltip: { intersect: false, mode: 'index' } }, scales: { x: { grid: { display: false } }, y: { beginAtZero: true, grid: { borderDash: [5,5] } } } }
});

const catData = <?= json_encode(array_values(array_filter($report['categories']))) ?>;
const catLabels = <?= json_encode(array_keys(array_filter($report['categories']))) ?>;
new Chart(document.getElementById('catDonut'), {
    type: 'doughnut',
    data: {
        labels: catLabels,
        datasets: [{ data: catData, backgroundColor: ['#2ED573','#5D5FEF','#FFA502','#FF4757','#a0a0a0'], borderWidth: 0, hoverOffset: 8 }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { display: false } } }
});

// Relative timestamps
document.querySelectorAll('.timestamp').forEach(el => {
    const ts = parseInt(el.dataset.timestamp) * 1000;
    const diffDays = Math.floor((Date.now() - ts) / (1000 * 60 * 60 * 24));
    if(diffDays === 0) el.innerHTML += ' (Today)';
    else if(diffDays === 1) el.innerHTML += ' (Yesterday)';
    else el.innerHTML += ` (${diffDays} days ago)`;
});
</script>
<?= $this->endSection() ?>
