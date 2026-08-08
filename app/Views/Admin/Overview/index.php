<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> Admin Overview - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .stat-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .stat-card .stat-icon { width: 44px; height: 44px; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    .status-pill { padding: 0.35rem 0.9rem; border-radius: 50rem; font-weight: 600; font-size: 0.8rem; }
    .system-table td, .system-table th { font-size: 0.85rem; }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-gauge-high me-2"></i> Admin Overview</h2>
        <p class="text-secondary mb-0">System health, ML backend status and platform usage at a glance.</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<!-- Health Alerts -->
<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="card stat-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon <?= $ml['reachable'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
                        <i class="fa-solid fa-microchip"></i>
                    </div>
                    <div>
                        <div class="text-muted small">ML Backend</div>
                        <div class="fw-bold fs-5">
                            <?= $ml['reachable'] ? '<span class="text-success">Online</span>' : '<span class="text-danger">Offline</span>' ?>
                        </div>
                        <?php if ($ml['reachable']): ?>
                            <small class="text-muted"><?= $ml['latency_ms'] ?>ms | <?= esc($ml['llama']) ?> | DB <?= $ml['db_configured'] ? 'OK' : 'FAIL' ?></small>
                        <?php else: ?>
                            <small class="text-danger"><?= esc($ml['error'] ?? 'Unreachable') ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card stat-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon <?= $maintenance_mode ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success' ?>">
                        <i class="fa-solid fa-tools"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Maintenance Mode</div>
                        <div class="fw-bold fs-5">
                            <?= $maintenance_mode ? '<span class="text-warning">ACTIVE</span>' : '<span class="text-success">Normal</span>' ?>
                        </div>
                        <small class="text-muted"><?= $maintenance_scheduled ? 'Scheduled window set' : 'No scheduled window' ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card stat-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary-subtle text-primary"><i class="fa-solid fa-database"></i></div>
                    <div>
                        <div class="text-muted small">Database</div>
                        <div class="fw-bold fs-5"><?= $stats['db_size_mb'] ?> MB</div>
                        <small class="text-muted"><?= $stats['sms'] ?> SMS records</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Platform Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card">
            <div class="card-body p-4">
                <div class="fw-bold fs-3" style="color: var(--primary);"><?= number_format($stats['users']) ?></div>
                <div class="text-muted small">Users</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card">
            <div class="card-body p-4">
                <div class="fw-bold fs-3 text-success"><?= number_format($stats['uploads']) ?></div>
                <div class="text-muted small">Uploads</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card">
            <div class="card-body p-4">
                <div class="fw-bold fs-3 text-info"><?= number_format($stats['transactions']) ?></div>
                <div class="text-muted small">Transactions</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card">
            <div class="card-body p-4">
                <div class="fw-bold fs-3 <?= $stats['queued_jobs'] > 0 ? 'text-warning' : 'text-secondary' ?>"><?= number_format($stats['processing_jobs']) ?></div>
                <div class="text-muted small">Processing Jobs (<?= $stats['queued_jobs'] ?> queued)</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Processing Jobs -->
    <div class="col-lg-6">
        <div class="card stat-card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-list-check me-2"></i> Recent Processing Jobs</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-striped system-table">
                        <thead>
                            <tr><th>ID</th><th>User</th><th>Status</th><th>Msgs</th><th>Errors</th><th>When</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_jobs)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-3">No processing jobs yet.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($recent_jobs as $j): ?>
                            <tr>
                                <td>#<?= $j['id'] ?></td>
                                <td><small><?= esc($j['user_id']) ?></small></td>
                                <td>
                                    <?php
                                    $badge = match ($j['status']) {
                                        'completed', 'done' => 'success',
                                        'failed', 'error' => 'danger',
                                        'processing', 'starting', 'queued' => 'warning',
                                        default => 'secondary',
                                    };
                                    ?>
                                    <span class="badge bg-<?= $badge ?>"><?= esc($j['status']) ?></span>
                                </td>
                                <td><?= $j['messages_processed'] ?></td>
                                <td><?= $j['errors'] ?></td>
                                <td><small><?= esc($j['created_at'] ?? $j['started_at'] ?? '-') ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="col-lg-6">
        <div class="card stat-card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-users me-2"></i> Recent Users</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-striped system-table">
                        <thead>
                            <tr><th>ID</th><th>Username</th><th>Group</th><th>Active</th><th>Registered</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_users)): ?>
                                <tr><td colspan="5" class="text-center text-muted py-3">No users yet.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($recent_users as $u): ?>
                            <tr>
                                <td><?= $u['id'] ?></td>
                                <td><strong><?= esc($u['username']) ?></strong></td>
                                <td><span class="badge bg-<?= $u['group'] === 'superadmin' ? 'danger' : 'primary' ?>"><?= esc($u['group'] ?? 'none') ?></span></td>
                                <td>
                                    <?php if ($u['active']): ?>
                                        <span class="text-success"><i class="fa-solid fa-circle-check"></i></span>
                                    <?php else: ?>
                                        <span class="text-danger"><i class="fa-solid fa-circle-xmark"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td><small><?= esc($u['created_at']) ?></small></td>
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
