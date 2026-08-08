<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> Maintenance Mode - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .maintenance-status { padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
    .maintenance-on { background: #fff0f0; border: 1px solid #ffcccc; color: #cc0000; }
    .maintenance-off { background: #f0fff0; border: 1px solid #ccffcc; color: #006600; }
    .schedule-form { background: #f8f9fa; padding: 1.5rem; border-radius: 4px; border: 1px solid #e0e0e0; }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-tools me-2"></i> Maintenance Mode</h2>
        <p class="text-secondary mb-0">Configure maintenance windows and database maintenance.</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card settings-card">
            <div class="card-body p-4">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#maintenanceTab">Maintenance Mode</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#scheduleTab">Scheduled Maintenance</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#dbInfoTab">DB Info</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#cronTab">Cron Jobs</button></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="maintenanceTab">
                        <div class="maintenance-status <?= $is_maintenance ? 'maintenance-on' : 'maintenance-off' ?>">
                            <strong>Status:</strong> <?= $is_maintenance ? '<span class="badge bg-danger">MAINTENANCE MODE ACTIVE</span>' : '<span class="badge bg-success">Normal Operation</span>' ?>
                        </div>
                        <form id="maintenanceForm">
                            <?= csrf_field() ?>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenance_mode" role="switch" <?= $is_maintenance ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="maintenance_mode">Enable Maintenance Mode</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Maintenance Message</label>
                                <textarea class="form-control" name="maintenance_message" rows="3"><?= htmlspecialchars($maintenance_message) ?></textarea>
                                <small class="text-muted">Shown to users when maintenance mode is active.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Allowed IPs (comma-separated)</label>
                                <input type="text" class="form-control" name="allowed_ips" value="<?= htmlspecialchars($allowed_ips) ?>" placeholder="192.168.1.100, 10.0.0.1">
                                <small class="text-muted">These IPs can access the site during maintenance.</small>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold"><i class="fa-solid fa-floppy-disk me-1"></i> Save</button>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="scheduleTab">
                        <div class="schedule-form">
                            <form id="scheduleForm">
                                <?= csrf_field() ?>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="scheduled" id="scheduled" role="switch" <?= $scheduled ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="scheduled">Enable Scheduled Maintenance</label>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Start Time (UTC)</label>
                                        <input type="datetime-local" class="form-control" name="schedule_start" value="<?= htmlspecialchars($schedule_start) ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">End Time (UTC)</label>
                                        <input type="datetime-local" class="form-control" name="schedule_end" value="<?= htmlspecialchars($schedule_end) ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Maintenance Message</label>
                                    <textarea class="form-control" name="maintenance_message" rows="2"><?= htmlspecialchars($maintenance_message) ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Allowed IPs During Window</label>
                                    <input type="text" class="form-control" name="allowed_ips" value="<?= htmlspecialchars($allowed_ips) ?>" placeholder="192.168.1.100, 10.0.0.1">
                                </div>
                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold"><i class="fa-solid fa-calendar-check me-1"></i> Save Schedule</button>
                            </form>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="dbInfoTab">
                        <?= view('Admin/Maintenance/db_info', [
                            'tables' => $tables,
                            'total_tables' => $total_tables,
                            'db_size_mb' => $db_size_mb,
                            'db_name' => $db_name,
                        ]) ?>
                    </div>

                    <div class="tab-pane fade" id="cronTab">
                        <?= view('Admin/Maintenance/cron', ['cron_jobs' => $cron_jobs]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card settings-card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);">Quick Actions</h5>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="tab" data-bs-target="#maintenanceTab" onclick="document.querySelector('[data-bs-target=\"#maintenanceTab\"]').click()">
                        <i class="fa-solid fa-toggle-on me-2"></i> Toggle Maintenance
                    </button>
                    <a href="<?= base_url('admin/settings/maintenance/db-info') ?>" class="btn btn-outline-info">
                        <i class="fa-solid fa-database me-2"></i> View DB Info
                    </a>
                    <a href="<?= base_url('admin/settings/maintenance/cron') ?>" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-clock me-2"></i> Manage Cron Jobs
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('maintenanceForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const data = new FormData(form);
        fetch('<?= base_url('admin/settings/maintenance/toggle') ?>', { method: 'POST', body: data })
            .then(r => r.json()).then(res => {
                showAlert('Maintenance', res.message, res.status === 'success' ? 'success' : 'danger');
                setTimeout(() => window.location.reload(), 1000);
            });
    });

    document.getElementById('scheduleForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const data = new FormData(form);
        fetch('<?= base_url('admin/settings/maintenance/schedule') ?>', { method: 'POST', body: data })
            .then(r => r.json()).then(res => {
                showAlert('Schedule', res.message, res.status === 'success' ? 'success' : 'danger');
            });
    });
});
</script>
<?= $this->endSection() ?>