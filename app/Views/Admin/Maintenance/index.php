<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> Cache & Sessions - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .stat-card { border-left: 4px solid var(--primary); }
    .btn-action { min-width: 160px; }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-broom me-2"></i> Cache & Sessions</h2>
        <p class="text-secondary mb-0">Manage application cache and user sessions.</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="row g-4">
    <!-- Cache Section -->
    <div class="col-lg-6">
        <div class="card settings-card h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-memory me-2"></i> Application Cache</h5>
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="stat-card p-3 bg-light rounded">
                            <div class="text-muted small">Driver</div>
                            <div class="fw-bold"><?= esc($cache_info['driver']) ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card p-3 bg-light rounded">
                            <div class="text-muted small">Cache Files</div>
                            <div class="fw-bold"><?= number_format($cache_info['file_count']) ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card p-3 bg-light rounded">
                            <div class="text-muted small">Total Size</div>
                            <div class="fw-bold"><?= esc($cache_info['size_human']) ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card p-3 bg-light rounded">
                            <div class="text-muted small">Cache Path</div>
                            <div class="fw-bold small text-truncate" style="max-width: 180px;" title="<?= esc($cache_info['path']) ?>"><?= esc($cache_info['path']) ?></div>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-danger btn-action" id="clearCacheBtn">
                        <i class="fa-solid fa-trash me-1"></i> Clear All Cache
                    </button>
                    <span class="text-muted small align-self-center" id="cacheStatus"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Sessions Section -->
    <div class="col-lg-6">
        <div class="card settings-card h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-user-clock me-2"></i> Sessions</h5>
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="stat-card p-3 bg-light rounded">
                            <div class="text-muted small">Handler</div>
                            <div class="fw-bold"><?= esc($session_info['handler']) ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card p-3 bg-light rounded">
                            <div class="text-muted small">Session Files</div>
                            <div class="fw-bold"><?= number_format($session_info['file_count']) ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card p-3 bg-light rounded">
                            <div class="text-muted small">DB Sessions</div>
                            <div class="fw-bold"><?= number_format($session_info['db_sessions']) ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card p-3 bg-light rounded">
                            <div class="text-muted small">Expired (2h+)</div>
                            <div class="fw-bold text-danger"><?= number_format($session_info['expired_sessions']) ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card p-3 bg-light rounded">
                            <div class="text-muted small">File Storage</div>
                            <div class="fw-bold"><?= esc($session_info['size_human']) ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card p-3 bg-light rounded">
                            <div class="text-muted small">Session Path</div>
                            <div class="fw-bold small text-truncate" style="max-width: 180px;" title="<?= esc($session_info['path']) ?>"><?= esc($session_info['path']) ?></div>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap mb-2">
                    <button type="button" class="btn btn-warning btn-action" id="cleanExpiredBtn">
                        <i class="fa-solid fa-clock me-1"></i> Clean Expired (2h+)
                    </button>
                    <button type="button" class="btn btn-danger btn-action" id="clearSessionsBtn">
                        <i class="fa-solid fa-trash me-1"></i> Clear ALL Sessions
                    </button>
                </div>
                <span class="text-muted small" id="sessionStatus"></span>
            </div>
        </div>
    </div>

    <!-- Data Retention Section -->
    <div class="col-lg-12">
        <div class="card settings-card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-calendar-minus me-2"></i> Data Retention</h5>
                <p class="text-muted small mb-3">
                    Automatically purge uploaded SMS files and database rows older than the configured number of days.
                    This is enforced by the <code>data:retention</code> cron job — create one under
                    <a href="<?= base_url('admin/crons') ?>">Cron Jobs</a> (type "Enforce Data Retention") to schedule it, or run it manually.
                </p>
                <form id="retentionForm" class="row g-3 align-items-end">
                    <?= csrf_field() ?>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Retention Period (days)</label>
                        <input type="number" class="form-control" name="retention_days" id="retentionDays" min="0" max="3650" value="<?= esc($retention_days) ?>" required>
                        <small class="text-muted">0 disables automatic purge.</small>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold"><i class="fa-solid fa-floppy-disk me-1"></i> Save Retention</button>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted small" id="retentionStatus"></span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="col-lg-12">
        <div class="card settings-card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-circle-info me-2"></i> What these actions do</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded h-100">
                            <h6 class="fw-bold"><i class="fa-solid fa-memory text-primary me-1"></i> Clear Cache</h6>
                            <ul class="small text-muted mb-0">
                                <li>Removes all cached views, config, and data</li>
                                <li>Forces fresh compilation on next request</li>
                                <li>Use after config changes or deployments</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded h-100">
                            <h6 class="fw-bold"><i class="fa-solid fa-clock text-warning me-1"></i> Clean Expired Sessions</h6>
                            <ul class="small text-muted mb-0">
                                <li>Removes only sessions older than 2 hours</li>
                                <li>Keeps active user sessions intact</li>
                                <li>Safe to run periodically via cron</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded h-100">
                            <h6 class="fw-bold"><i class="fa-solid fa-trash text-danger me-1"></i> Clear ALL Sessions</h6>
                            <ul class="small text-muted mb-0">
                                <li>Logs out ALL users immediately</li>
                                <li>Includes your current session</li>
                                <li>Use only for emergencies/security</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showStatus(el, message, type = 'info') {
    el.textContent = message;
    el.className = 'text-' + (type === 'success' ? 'success' : type === 'error' ? 'danger' : 'muted') + ' small align-self-center';
}

function setLoading(btn, loading) {
    btn.disabled = loading;
    if (loading) {
        btn.dataset.originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Working...';
    } else {
        btn.innerHTML = btn.dataset.originalHtml || btn.innerHTML;
    }
}

function confirmAction(title, text, icon = 'warning', confirmText = 'Yes, proceed') {
    return Swal.fire({
        title: title,
        text: text,
        icon: icon,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        reverseButtons: true,
    });
}

document.getElementById('clearCacheBtn').addEventListener('click', async function() {
    const result = await confirmAction(
        'Clear All Application Cache?',
        'This will:\n• Remove ALL cached views, config, and data\n• Force fresh compilation on next request\n• May cause temporary slowdown on first page loads\n\nThis action CANNOT be undone.',
        'warning',
        'Yes, clear cache'
    );
    if (!result.isConfirmed) return;

    const btn = this;
    const status = document.getElementById('cacheStatus');
    setLoading(btn, true);
    showStatus(status, 'Clearing cache...', 'info');

    try {
        const res = await fetch('<?= base_url('admin/maintenance/clear-cache') ?>', { method: 'POST' });
        const data = await res.json();
        showStatus(status, data.message, data.status === 'success' ? 'success' : 'error');
        if (data.status === 'success') {
            Swal.fire('Cleared!', data.message, 'success');
        }
    } catch (e) {
        showStatus(status, 'Request failed: ' + e.message, 'error');
        Swal.fire('Error', 'Request failed: ' + e.message, 'error');
    }
    setLoading(btn, false);
});

document.getElementById('cleanExpiredBtn').addEventListener('click', async function() {
    const result = await confirmAction(
        'Clean Expired Sessions?',
        'This will:\n• Remove ONLY sessions older than 2 hours\n• Keep active user sessions intact\n• Free up storage space\n\nThis action CANNOT be undone.',
        'info',
        'Yes, clean expired'
    );
    if (!result.isConfirmed) return;

    const btn = this;
    const status = document.getElementById('sessionStatus');
    setLoading(btn, true);
    showStatus(status, 'Cleaning expired sessions...', 'info');

    try {
        const res = await fetch('<?= base_url('admin/maintenance/clean-expired-sessions') ?>', { method: 'POST' });
        const data = await res.json();
        showStatus(status, data.message, data.status === 'success' ? 'success' : 'error');
        if (data.status === 'success') {
            Swal.fire('Cleaned!', data.message, 'success');
        }
    } catch (e) {
        showStatus(status, 'Request failed: ' + e.message, 'error');
        Swal.fire('Error', 'Request failed: ' + e.message, 'error');
    }
    setLoading(btn, false);
});

document.getElementById('clearSessionsBtn').addEventListener('click', async function() {
    const result = await confirmAction(
        'Clear ALL Sessions?',
        '⚠️ THIS WILL LOG OUT EVERYONE INCLUDING YOU\n\nThis will:\n• Immediately terminate ALL active user sessions\n• Log out ALL users (including yourself)\n• Clear both file and database sessions\n• Users will need to log in again\n\nThis action CANNOT be undone.',
        'error',
        'Yes, clear ALL sessions'
    );
    if (!result.isConfirmed) return;

    const btn = this;
    const status = document.getElementById('sessionStatus');
    setLoading(btn, true);
    showStatus(status, 'Clearing all sessions...', 'info');

    try {
        const res = await fetch('<?= base_url('admin/maintenance/clear-sessions') ?>', { method: 'POST' });
        const data = await res.json();
        showStatus(status, data.message, data.status === 'success' ? 'success' : 'error');
        if (data.status === 'success') {
            Swal.fire('Cleared!', data.message + ' Reloading...', 'success');
            setTimeout(() => location.reload(), 1500);
        }
    } catch (e) {
        showStatus(status, 'Request failed: ' + e.message, 'error');
        Swal.fire('Error', 'Request failed: ' + e.message, 'error');
    }
    setLoading(btn, false);
});

document.getElementById('retentionForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const status = document.getElementById('retentionStatus');
    const data = new FormData(this);
    const btn = this.querySelector('button[type=submit]');
    setLoading(btn, true);
    showStatus(status, 'Saving retention period...', 'info');
    try {
        const res = await fetch('<?= base_url('admin/maintenance/save-retention') ?>', { method: 'POST', body: data });
        const r = await res.json();
        showStatus(status, r.message, r.status === 'success' ? 'success' : 'error');
        Swal.fire(r.status === 'success' ? 'Saved!' : 'Error', r.message, r.status === 'success' ? 'success' : 'error');
    } catch (err) {
        showStatus(status, 'Request failed: ' + err.message, 'error');
    }
    setLoading(btn, false);
});
</script>
<?= $this->endSection() ?>