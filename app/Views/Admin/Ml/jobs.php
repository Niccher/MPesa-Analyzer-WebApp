<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> ML Jobs - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .stat-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .stat-card .stat-icon { width: 44px; height: 44px; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    .system-table td, .system-table th { font-size: 0.85rem; }
    .job-row.failed { background-color: rgba(220,53,69,0.04); }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-list-check me-2"></i> ML Jobs</h2>
        <p class="text-secondary mb-0">History and statistics of ML SMS-classification runs.</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<?= view('Admin/Ml/_status', ['status' => $status]) ?>

<div class="card settings-card mb-4">
    <div class="card-body p-4">
        <?= view('Admin/Ml/_nav', ['active' => 'jobs']) ?>
    </div>
</div>

<?php if (empty($jobs)): ?>
    <div class="card settings-card">
        <div class="card-body p-5 text-center text-muted">
            <i class="fa-solid fa-clock-rotate-left fs-1 d-block mb-2"></i>
            No processing jobs recorded yet. Trigger a Rescan (top bar) or run a job to see statistics here.
        </div>
    </div>
<?php else: ?>
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card stat-card h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary-subtle text-primary"><i class="fa-solid fa-list-check"></i></div>
                    <div>
                        <div class="fw-bold fs-4"><?= number_format($totals['jobs']) ?></div>
                        <div class="text-muted small">Total Jobs</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success-subtle text-success"><i class="fa-solid fa-envelope"></i></div>
                    <div>
                        <div class="fw-bold fs-4"><?= number_format($totals['msgs']) ?></div>
                        <div class="text-muted small">SMS Classified</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="stat-icon bg-danger-subtle text-danger"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div>
                        <div class="fw-bold fs-4"><?= number_format($totals['errors']) ?></div>
                        <div class="text-muted small">Errors</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning-subtle text-warning"><i class="fa-solid fa-stopwatch"></i></div>
                    <div>
                        <div class="fw-bold fs-4"><?= $totals['time'] > 0 ? gmdate('H:i:s', $totals['time']) : '0s' ?></div>
                        <div class="text-muted small">Total Time Taken</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card settings-card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="fw-bold mb-0" style="color: var(--primary);"><i class="fa-solid fa-clock-rotate-left me-2"></i> Job History</h5>
                <?php foreach ($status_counts as $st => $cnt): ?>
                    <span class="badge bg-secondary"><?= esc($st) ?>: <?= $cnt ?></span>
                <?php endforeach; ?>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-striped system-table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Status</th>
                            <th>Time Taken</th>
                            <th>SMS Classified</th>
                            <th>Errors</th>
                            <th>Queued</th>
                            <th>Started</th>
                            <th>Finished</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $j): ?>
                        <tr class="job-row">
                            <td>#<?= $j['id'] ?></td>
                            <td><small><?= esc($j['username']) ?></small></td>
                            <td>
                                <?php
                                $badge = match ($j['status']) {
                                    'completed', 'done' => 'success',
                                    'failed', 'error' => 'danger',
                                    'processing', 'starting' => 'warning',
                                    'queued' => 'info',
                                    default => 'secondary',
                                };
                                ?>
                                <span class="badge bg-<?= $badge ?>"><?= esc($j['status']) ?></span>
                            </td>
                            <td>
                                <?php if (!empty($j['duration_seconds'])): ?>
                                    <?= gmdate('i:s', (int) $j['duration_seconds']) ?>
                                <?php elseif ($j['started_at'] && $j['completed_at']): ?>
                                    <?= gmdate('i:s', max(0, strtotime($j['completed_at']) - strtotime($j['started_at']))) ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= number_format((int) $j['messages_processed']) ?></td>
                            <td class="text-<?= $j['errors'] > 0 ? 'danger fw-semibold' : 'muted' ?>"><?= (int) $j['errors'] ?></td>
                            <td><small><?= esc($j['created_at'] ?? '—') ?></small></td>
                            <td><small><?= esc($j['started_at'] ?? '—') ?></small></td>
                            <td><small><?= esc($j['completed_at'] ?? '—') ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
