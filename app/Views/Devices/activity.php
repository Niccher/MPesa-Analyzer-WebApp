<?= $this->extend('Layouts/admin') ?>
<?= $this->section('title') ?> API Activity - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .stat-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .feed-table td, .feed-table th { font-size: 0.82rem; }
    .feed-meta { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 0.72rem; color: #6c757d; word-break: break-all; }
    .action-badge { min-width: 150px; }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-bolt me-2"></i> API Activity</h2>
        <p class="text-secondary mb-0">Audit trail of every endpoint hit from your Android devices.</p>
    </div>
    <a class="btn btn-outline-secondary btn-sm" href="<?= base_url('dashboard/devices') ?>">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Devices
    </a>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card"><div class="card-body p-4">
            <div class="fw-bold fs-3" style="color: var(--primary);"><?= number_format((int) ($activity['totals']->total_calls ?? 0)) ?></div>
            <div class="text-muted small">Total Calls</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card"><div class="card-body p-4">
            <div class="fw-bold fs-3 text-success"><?= number_format((int) ($activity['totals']->last_24h ?? 0)) ?></div>
            <div class="text-muted small">Last 24 Hours</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card"><div class="card-body p-4">
            <div class="fw-bold fs-3 text-info"><?= number_format((int) ($activity['totals']->last_7d ?? 0)) ?></div>
            <div class="text-muted small">Last 7 Days</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card"><div class="card-body p-4">
            <div class="fw-bold fs-3 text-warning"><?= number_format((int) ($activity['totals']->unique_ips ?? 0)) ?></div>
            <div class="text-muted small">Unique IPs</div>
        </div></div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card stat-card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-chart-simple me-2"></i> Endpoint Breakdown</h5>
                <?php if (empty($activity['actions'])): ?>
                    <p class="text-muted small mb-0">No API activity recorded yet.</p>
                <?php else: ?>
                <?php foreach ($activity['actions'] as $a): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-light text-dark border action-badge text-start"><code><?= esc($a['action']) ?></code></span>
                        <span class="fw-bold"><?= number_format((int) $a['cnt']) ?></span>
                    </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card stat-card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-list me-2"></i> Recent Calls</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-striped feed-table">
                        <thead>
                            <tr><th>When</th><th>Action</th><th>IP</th><th>Details</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($activity['feed'])): ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">No calls recorded.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($activity['feed'] as $f): ?>
                            <tr>
                                <td><small><?= esc($f['created_at']) ?></small></td>
                                <td><code><?= esc($f['action']) ?></code></td>
                                <td><small><?= esc($f['ip'] ?? '-') ?></small></td>
                                <td>
                                    <small><?= esc($f['description'] ?? '') ?></small>
                                    <?php if (!empty($f['metadata'])): ?>
                                        <div class="feed-meta"><?= esc(json_encode(json_decode($f['metadata'], true), JSON_UNESCAPED_SLASHES)) ?></div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
