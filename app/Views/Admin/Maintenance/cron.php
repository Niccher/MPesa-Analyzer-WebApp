<div class="card settings-card">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-clock me-2"></i> Scheduled Cron Jobs</h5>
        <p class="text-muted small mb-4">Manage background tasks that run on a schedule.</p>
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Job</th>
                        <th>Schedule</th>
                        <th>Command</th>
                        <th>Status</th>
                        <th>Last Run</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cron_jobs as $key => $job): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($job['description'] ?? $key) ?></strong>
                            <br><small class="text-muted"><?= $key ?></small>
                        </td>
                        <td><code><?= htmlspecialchars($job['schedule'] ?? 'N/A') ?></code></td>
                        <td><code><?= htmlspecialchars($job['command'] ?? 'N/A') ?></code></td>
                        <td>
                            <?php if (!empty($job['enabled'])): ?>
                                <span class="badge bg-success">Enabled</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Disabled</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small><?= $job['last_run'] ?? 'Never' ?></small>
                            <?php if (!empty($job['last_status'])): ?>
                                <br><span class="badge bg-<?= $job['last_status'] === 'success' ? 'success' : 'danger' ?>"><?= $job['last_status'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary run-cron" data-job="<?= $key ?>" <?= empty($job['enabled']) ? 'disabled' : '' ?>>
                                <i class="fa-solid fa-play me-1"></i> Run Now
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.run-cron').forEach(btn => {
    btn.addEventListener('click', function() {
        const job = this.dataset.job;
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Running...';
        const data = new FormData();
        data.append('job_key', job);
        fetch('<?= base_url('admin/settings/maintenance/cron/run') ?>', { method: 'POST', body: data })
            .then(r => r.json()).then(res => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-play me-1"></i> Run Now';
                showAlert('Cron Job', res.message, res.status === 'success' ? 'success' : 'danger');
                setTimeout(() => window.location.reload(), 2000);
            });
    });
});
</script>