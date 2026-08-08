<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> Cron Jobs - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-clock me-2"></i> Cron Jobs</h2>
        <p class="text-secondary mb-0">Configure the background tasks that keep the platform running.</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card settings-card">
            <div class="card-body p-4">
                <form id="cronForm">
                    <?= csrf_field() ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr><th>Job</th><th>Command</th><th>Schedule</th><th>Enabled</th><th>Save</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cron_jobs as $key => $job): ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($job['description'] ?? $key) ?></strong>
                                        <br><small class="text-muted"><?= $key ?></small>
                                        <input type="hidden" name="job_key[]" value="<?= esc($key) ?>">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="command[]" value="<?= esc($job['command'] ?? '') ?>">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="schedule[]" value="<?= esc($job['schedule'] ?? '') ?>" placeholder="*/5 * * * *">
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="enabled[]" value="1" role="switch" <?= !empty($job['enabled']) ? 'checked' : '' ?> data-key="<?= esc($key) ?>">
                                        </div>
                                    </td>
                                    <td>
                                        <button type="submit" class="btn btn-sm btn-outline-primary" data-save="<?= esc($key) ?>"><i class="fa-solid fa-floppy-disk"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold d-none" id="saveAllBtn"><i class="fa-solid fa-floppy-disk me-1"></i> Save All</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card settings-card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);">Scheduler</h5>
                <p class="text-muted small mb-3">
                    These settings define what runs. For the jobs to run automatically, add them to the
                    host crontab (recommended), e.g.:
                </p>
                <pre class="bg-light border rounded p-3 small" style="overflow-x:auto;">* * * * * docker exec mpesa-analyzer-webapp php spark llm:process >> /var/log/mpesa-cron.log 2>&1</pre>
                <div class="d-grid gap-2">
                    <a href="<?= base_url('admin/settings/maintenance') ?>#cronTab" class="btn btn-outline-primary">
                        <i class="fa-solid fa-play me-2"></i> Run jobs now
                    </a>
                </div>
                <hr>
                <h6 class="fw-bold">Available spark commands</h6>
                <ul class="small text-muted mb-0">
                    <li><code>llm:process</code> — pending LLM jobs</li>
                    <li><code>session:gc</code> — expired sessions</li>
                    <li><code>uploads:cleanup</code> — orphaned uploads</li>
                    <li><code>reports:send</code> — scheduled reports</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('cronForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const data = new FormData(this);

    const keys = data.getAll('job_key[]');
    const commands = data.getAll('command[]');
    const schedules = data.getAll('schedule[]');
    const enabledVals = data.getAll('enabled[]');

    // Build a map of which job_keys are enabled
    const enabledSet = new Set();
    document.querySelectorAll('input[name="enabled[]"]').forEach(chk => {
        if (chk.checked) enabledSet.add(chk.dataset.key);
    });

    let promises = [];
    keys.forEach((key, i) => {
        const body = new FormData();
        body.append('job_key', key);
        body.append('command', commands[i] || '');
        body.append('schedule', schedules[i] || '');
        body.append('description', document.querySelector('[data-save="' + key + '"]').closest('tr').querySelector('strong').textContent);
        body.append('enabled', enabledSet.has(key) ? '1' : '0');
        promises.push(
            fetch('<?= base_url('admin/crons/save') ?>', { method: 'POST', body })
                .then(r => r.json())
        );
    });

    Promise.all(promises).then(results => {
        const failed = results.filter(r => r.status !== 'success');
        if (failed.length === 0) {
            showAlert('Cron Jobs', 'All cron jobs saved.', 'success');
        } else {
            showAlert('Cron Jobs', failed[0].message || 'Some jobs failed to save.', 'danger');
        }
    });
});
</script>
<?= $this->endSection() ?>
