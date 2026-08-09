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
        <p class="text-secondary mb-0">Configure maintenance mode and scheduled maintenance windows.</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="row g-4">
    <div class="col-lg-12">
        <div class="card settings-card">
            <div class="card-body p-4">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#maintenanceTab">Maintenance Mode</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#scheduleTab">Scheduled Maintenance</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#historyTab">History</button></li>
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
                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold"><i class="fa-solid fa-calendar-check me-1"></i> Save Schedule</button>
                            </form>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="historyTab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0" style="color: var(--primary);"><i class="fa-solid fa-clock-rotate-left me-2"></i> Maintenance History</h6>
                            <span class="text-muted small">Last <?= count($maintenance_history) ?> events</span>
                        </div>
                        <?php if (empty($maintenance_history)): ?>
                            <div class="text-center text-muted p-5">
                                <i class="fa-solid fa-inbox fs-1 d-block mb-2"></i>
                                No maintenance events recorded yet.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 90px;">Action</th>
                                            <th style="width: 170px;">Time</th>
                                            <th style="width: 130px;">Initiated By</th>
                                            <th>Message</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($maintenance_history as $ev): ?>
                                            <?php
                                                $isStart = $ev['action'] === 'start';
                                                $isCron = $ev['source'] === 'cron';
                                                $ts = $ev['created_at'];
                                                $local = '';
                                                if ($ts !== null && $ts !== '') {
                                                    try {
                                                        $dt = new \DateTimeImmutable($ts, new \DateTimeZone('UTC'));
                                                        $local = $dt->setTimezone(new \DateTimeZone('Africa/Nairobi'))->format('d M Y, g:i A');
                                                    } catch (\Throwable $e) {
                                                        $local = $ts;
                                                    }
                                                }
                                            ?>
                                            <tr>
                                                <td>
                                                    <span class="badge <?= $isStart ? 'bg-success' : 'bg-danger' ?>">
                                                        <i class="fa-solid <?= $isStart ? 'fa-play' : 'fa-stop' ?> me-1"></i><?= $isStart ? 'Started' : 'Stopped' ?>
                                                    </span>
                                                </td>
                                                <td><small><?= esc($local) ?></small></td>
                                                <td>
                                                    <span class="badge <?= $isCron ? 'bg-info text-dark' : 'bg-primary' ?>">
                                                        <i class="fa-solid <?= $isCron ? 'fa-clock' : 'fa-user' ?> me-1"></i><?= $isCron ? 'Cron Job' : 'Manual' ?>
                                                    </span>
                                                </td>
                                                <td><small class="text-muted"><?= esc($ev['message'] ?? '') ?: '<em>No message</em>' ?></small></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
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