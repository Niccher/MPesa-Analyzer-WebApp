<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> ML Jobs - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); }
    .stat-card { border: none; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); border-top:3px solid var(--stat-color, var(--primary)); }
    .stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink:0; }
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

<div class="card settings-card mb-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="stat-icon bg-<?= !empty($auto['auto_enabled']) ? 'success' : 'danger' ?>-subtle text-<?= !empty($auto['auto_enabled']) ? 'success' : 'danger' ?>">
                <i class="fa-solid fa-<?= !empty($auto['auto_enabled']) ? 'play' : 'pause' ?>"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold fs-6">Auto Processing Jobs</div>
                <div class="text-muted small">
                    <?php if ($auto['reachable'] === false): ?>
                        Backend offline — toggle unavailable.
                    <?php elseif (!empty($auto['auto_enabled'])): ?>
                        Running — the backend polls for new SMS and processes them automatically.
                    <?php else: ?>
                        <strong class="text-danger">Stopped</strong> — no ML jobs will run until re-enabled.
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($auto['reachable'] !== false): ?>
                <button type="button" id="autoToggleBtn"
                        class="btn btn-<?= !empty($auto['auto_enabled']) ? 'danger' : 'success' ?> rounded-pill px-4 fw-semibold">
                    <i class="fa-solid fa-<?= !empty($auto['auto_enabled']) ? 'stop' : 'play' ?> me-1"></i>
                    <?= !empty($auto['auto_enabled']) ? 'Stop Auto Jobs' : 'Start Auto Jobs' ?>
                </button>
            <?php endif; ?>
        </div>
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
            <div class="card stat-card h-100" style="--stat-color:#0d6efd;">
                <div class="card-body p-3 d-flex align-items-start gap-3">
                    <div class="stat-icon bg-primary-subtle text-primary"><i class="fa-solid fa-list-check"></i></div>
                    <div>
                        <div class="fw-bold fs-3 lh-1"><?= number_format($totals['jobs']) ?></div>
                        <div class="fw-semibold small">Total Jobs</div>
                        <div class="text-muted small">Classified SMS-runs recorded</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card h-100" style="--stat-color:#198754;">
                <div class="card-body p-3 d-flex align-items-start gap-3">
                    <div class="stat-icon bg-success-subtle text-success"><i class="fa-solid fa-envelope"></i></div>
                    <div>
                        <div class="fw-bold fs-3 lh-1"><?= number_format($totals['msgs']) ?></div>
                        <div class="fw-semibold small">SMS Classified</div>
                        <div class="text-muted small">Messages analyzed by the LLM</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card h-100" style="--stat-color:#dc3545;">
                <div class="card-body p-3 d-flex align-items-start gap-3">
                    <div class="stat-icon bg-danger-subtle text-danger"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div>
                        <div class="fw-bold fs-3 lh-1"><?= number_format($totals['errors']) ?></div>
                        <div class="fw-semibold small">Errors</div>
                        <div class="text-muted small">Failed classifications</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card h-100" style="--stat-color:#fd7e14;">
                <div class="card-body p-3 d-flex align-items-start gap-3">
                    <div class="stat-icon bg-warning-subtle text-warning"><i class="fa-solid fa-stopwatch"></i></div>
                    <div>
                        <div class="fw-bold fs-3 lh-1"><?= $totals['time'] > 0 ? gmdate('H:i:s', $totals['time']) : '0s' ?></div>
                        <div class="fw-semibold small">Total Time</div>
                        <div class="text-muted small">Cumulative processing time</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card stat-card h-100" style="--stat-color:#6f42c1;">
                <div class="card-body p-3 d-flex align-items-start gap-3">
                    <div class="stat-icon bg-secondary-subtle text-secondary"><i class="fa-solid fa-address-book"></i></div>
                    <div>
                        <div class="fw-bold fs-3 lh-1"><?= number_format($totals['senders']) ?></div>
                        <div class="fw-semibold small">Senders Seen</div>
                        <div class="text-muted small"><?= number_format($totals['senders_finance']) ?> finance / <?= number_format($totals['senders_unwanted']) ?> unwanted</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card h-100" style="--stat-color:#198754;">
                <div class="card-body p-3 d-flex align-items-start gap-3">
                    <div class="stat-icon bg-success-subtle text-success"><i class="fa-solid fa-envelope-open-text"></i></div>
                    <div>
                        <div class="fw-bold fs-3 lh-1"><?= number_format($totals['sms_total']) ?></div>
                        <div class="fw-semibold small">SMS Total</div>
                        <div class="text-muted small"><?= number_format($totals['sms_finance']) ?> finance / <?= number_format($totals['sms_unwanted']) ?> unwanted</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card h-100" style="--stat-color:#6c757d;">
                <div class="card-body p-3 d-flex align-items-start gap-3">
                    <div class="stat-icon bg-warning-subtle text-warning"><i class="fa-solid fa-ban"></i></div>
                    <div>
                        <div class="fw-bold fs-3 lh-1"><?= number_format($totals['sms_skipped']) ?></div>
                        <div class="fw-semibold small">Skipped SMS</div>
                        <div class="text-muted small">From unwanted / non-finance senders</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card h-100" style="--stat-color:#0dcaf0;">
                <div class="card-body p-3 d-flex align-items-start gap-3">
                    <div class="stat-icon bg-info-subtle text-info"><i class="fa-solid fa-money-bill-transfer"></i></div>
                    <div>
                        <div class="fw-bold fs-3 lh-1"><?= number_format($totals['transactional']) ?></div>
                        <div class="fw-semibold small">Transactions Found</div>
                        <div class="text-muted small">Inserted across all jobs</div>
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
                            <th>SMS Processed</th>
                            <th>Errors</th>
                            <th>Model</th>
                            <th>Queued</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $j): $md = $j['metadata'] ?? []; ?>
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
                                    'disabled' => 'secondary',
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
                            <td><?= number_format((int) ($md['messages_processed'] ?? $j['messages_processed'])) ?></td>
                            <td class="text-<?= $j['errors'] > 0 ? 'danger fw-semibold' : 'muted' ?>"><?= (int) ($md['errors'] ?? $j['errors']) ?></td>
                            <td><small class="text-muted"><?= esc($md['model'] ?? '—') ?></small></td>
                            <td><small><?= esc($j['created_at'] ?? '—') ?></small></td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#jobmeta-<?= (int) $j['id'] ?>">
                                    <i class="fa-solid fa-circle-info"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="collapse" id="jobmeta-<?= (int) $j['id'] ?>">
                            <td colspan="9" class="bg-light">
                                <div class="row g-3 py-2">
                                    <div class="col-md-6">
                                        <div class="fw-semibold small text-secondary mb-1">Senders &amp; SMS</div>
                                        <table class="table table-sm mb-0 system-table">
                                            <tr><td>All senders</td><td class="fw-semibold"><?= number_format((int) ($md['senders_total'] ?? 0)) ?></td></tr>
                                            <tr><td>Finance senders</td><td class="fw-semibold text-success"><?= number_format((int) ($md['senders_finance'] ?? 0)) ?></td></tr>
                                            <tr><td>Unwanted senders</td><td class="fw-semibold text-danger"><?= number_format((int) ($md['senders_unwanted'] ?? 0)) ?></td></tr>
                                            <tr><td>All SMS</td><td class="fw-semibold"><?= number_format((int) ($md['sms_total'] ?? 0)) ?></td></tr>
                                            <tr><td>Finance SMS</td><td class="fw-semibold text-success"><?= number_format((int) ($md['sms_finance'] ?? 0)) ?></td></tr>
                                            <tr><td>Unwanted SMS</td><td class="fw-semibold text-danger"><?= number_format((int) ($md['sms_unwanted'] ?? 0)) ?></td></tr>
                                            <tr><td>Skipped SMS (all − unwanted senders)</td><td class="fw-semibold text-warning"><?= number_format((int) ($md['sms_skipped'] ?? 0)) ?></td></tr>
                                            <tr><td>Transactions found</td><td class="fw-semibold text-info"><?= number_format((int) ($md['transactional_inserted'] ?? 0)) ?></td></tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="fw-semibold small text-secondary mb-1">Model &amp; Backend</div>
                                        <table class="table table-sm mb-0 system-table">
                                            <tr><td>Model</td><td class="fw-semibold"><?= esc($md['model'] ?? '—') ?></td></tr>
                                            <tr><td>Provider</td><td><?= esc($md['model_provider'] ?? '—') ?></td></tr>
                                            <tr><td>Model path</td><td><small><?= esc($md['model_path'] ?? '—') ?></small></td></tr>
                                            <tr><td>Max tokens</td><td><?= esc($md['llm_max_tokens'] ?? '—') ?></td></tr>
                                            <tr><td>Temperature</td><td><?= esc($md['llm_temperature'] ?? '—') ?></td></tr>
                                            <tr><td>Context size</td><td><?= esc($md['llm_ctx_size'] ?? '—') ?></td></tr>
                                            <tr><td>Prompt batch</td><td><?= esc($md['llm_batch_size'] ?? '—') ?></td></tr>
                                            <tr><td>GPU layers</td><td><?= esc($md['n_gpu_layers'] ?? '—') ?></td></tr>
                                            <tr><td>SMS batch size</td><td><?= esc($md['sms_batch_size'] ?? '—') ?></td></tr>
                                            <tr><td>Started / Finished</td><td><small><?= esc($j['started_at'] ?? '—') ?><br><?= esc($j['completed_at'] ?? '—') ?></small></td></tr>
                                        </table>
                                    </div>
                                    <?php if (!empty($md['error'])): ?>
                                        <div class="col-12">
                                            <div class="alert alert-danger small mb-0"><?= esc($md['error']) ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
document.getElementById('autoToggleBtn')?.addEventListener('click', function() {
    const currentlyEnabled = !this.classList.contains('btn-danger');
    const enable = !currentlyEnabled;
    const label = enable ? 'Start auto jobs' : 'Stop auto jobs';
    if (!confirm(label + '? ' + (enable ? 'The backend will resume processing on its next poll cycle.' : 'No ML jobs will run until re-enabled.'))) return;
    this.disabled = true;
    const data = new FormData();
    data.append('enabled', enable ? '1' : '0');
    fetch('<?= base_url('admin/ml/jobs/auto') ?>', { method: 'POST', body: data })
        .then(r => r.json()).then(res => {
            showAlert('Auto Jobs', res.message, res.status === 'ok' ? 'success' : 'danger');
            if (res.status === 'ok') setTimeout(() => window.location.reload(), 1200);
        })
        .catch(err => showAlert('Error', err.message, 'danger'))
        .finally(() => { this.disabled = false; });
});
</script>
<?= $this->endSection() ?>
