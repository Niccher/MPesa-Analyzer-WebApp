<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> Cache & Sessions - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .section-head { display: flex; align-items: center; gap: 0.85rem; margin-bottom: 1.25rem; }
    .section-head .head-icon {
        width: 42px; height: 42px; border-radius: 4px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: rgba(93, 95, 239, 0.12); color: var(--primary); font-size: 1.1rem;
    }
    .metric {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.85rem 0.9rem; height: 100%;
        background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 4px;
    }
    .metric-icon {
        width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: rgba(93, 95, 239, 0.12); font-size: 0.95rem;
    }
    .metric-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted); }
    .metric-value { font-weight: 700; color: var(--text-main); font-size: 0.95rem; }
    .metric-value.small { font-size: 0.8rem; font-weight: 600; }
    .action-bar { display: flex; gap: 0.6rem; flex-wrap: wrap; align-items: center; }
    .btn-action { min-width: 170px; }
    .tip-tile { padding: 1rem; background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 4px; height: 100%; }
    .tip-tile h6 { font-weight: 700; display: flex; align-items: center; gap: 0.5rem; }
    .tip-tile ul { font-size: 0.85rem; color: var(--text-muted); padding-left: 1.1rem; margin-bottom: 0; }
    [data-bs-theme="dark"] .settings-card { box-shadow: 0 4px 20px rgba(0,0,0,0.25); }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-broom me-2"></i> Cache &amp; Sessions</h2>
        <p class="text-secondary mb-0">Manage application cache and user sessions.</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="card settings-card mb-4">
    <div class="card-body p-4 pb-0">
        <?= view('Admin/System/_nav', ['active' => 'maintenance']) ?>
    </div>
</div>

<div class="row g-4">
    <!-- Cache Section -->
    <div class="col-lg-6">
        <div class="card settings-card glass-card h-100">
            <div class="card-body p-4">
                <div class="section-head">
                    <div class="head-icon"><i class="fa-solid fa-memory"></i></div>
                    <div>
                        <h5 class="fw-bold mb-0">Application Cache</h5>
                        <small class="text-secondary">Stored compiled &amp; data cache</small>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="metric">
                            <span class="metric-icon text-primary"><i class="fa-solid fa-gears"></i></span>
                            <div class="min-w-0">
                                <div class="metric-label">Driver</div>
                                <div class="metric-value"><?= esc($cache_info['driver']) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="metric">
                            <span class="metric-icon text-primary"><i class="fa-solid fa-file-lines"></i></span>
                            <div class="min-w-0">
                                <div class="metric-label">Cache Files</div>
                                <div class="metric-value"><?= number_format($cache_info['file_count']) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="metric">
                            <span class="metric-icon text-primary"><i class="fa-solid fa-hard-drive"></i></span>
                            <div class="min-w-0">
                                <div class="metric-label">Total Size</div>
                                <div class="metric-value"><?= esc($cache_info['size_human']) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="metric">
                            <span class="metric-icon text-primary"><i class="fa-solid fa-folder-tree"></i></span>
                            <div class="min-w-0">
                                <div class="metric-label">Cache Path</div>
                                <div class="metric-value small text-truncate" style="max-width: 150px;" title="<?= esc($cache_info['path']) ?>"><?= esc($cache_info['path']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="action-bar">
                    <button type="button" class="btn btn-danger btn-action" id="clearCacheBtn">
                        <i class="fa-solid fa-trash-can me-1"></i> Clear All Cache
                    </button>
                    <span class="text-secondary small" id="cacheStatus"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Sessions Section -->
    <div class="col-lg-6">
        <div class="card settings-card glass-card h-100">
            <div class="card-body p-4">
                <div class="section-head">
                    <div class="head-icon"><i class="fa-solid fa-user-clock"></i></div>
                    <div>
                        <h5 class="fw-bold mb-0">Sessions</h5>
                        <small class="text-secondary">Active user login sessions</small>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="metric">
                            <span class="metric-icon text-primary"><i class="fa-solid fa-microchip"></i></span>
                            <div class="min-w-0">
                                <div class="metric-label">Handler</div>
                                <div class="metric-value"><?= esc($session_info['handler']) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="metric">
                            <span class="metric-icon text-primary"><i class="fa-solid fa-folder-open"></i></span>
                            <div class="min-w-0">
                                <div class="metric-label">Session Files</div>
                                <div class="metric-value"><?= number_format($session_info['file_count']) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="metric">
                            <span class="metric-icon text-primary"><i class="fa-solid fa-database"></i></span>
                            <div class="min-w-0">
                                <div class="metric-label">DB Sessions</div>
                                <div class="metric-value"><?= number_format($session_info['db_sessions']) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="metric">
                            <span class="metric-icon text-danger"><i class="fa-solid fa-clock-rotate-left"></i></span>
                            <div class="min-w-0">
                                <div class="metric-label">Expired (2h+)</div>
                                <div class="metric-value text-danger"><?= number_format($session_info['expired_sessions']) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="metric">
                            <span class="metric-icon text-primary"><i class="fa-solid fa-hard-drive"></i></span>
                            <div class="min-w-0">
                                <div class="metric-label">File Storage</div>
                                <div class="metric-value"><?= esc($session_info['size_human']) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="metric">
                            <span class="metric-icon text-primary"><i class="fa-solid fa-location-dot"></i></span>
                            <div class="min-w-0">
                                <div class="metric-label">Session Path</div>
                                <div class="metric-value small text-truncate" style="max-width: 150px;" title="<?= esc($session_info['path']) ?>"><?= esc($session_info['path']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="action-bar mb-2">
                    <button type="button" class="btn btn-outline-warning btn-action" id="cleanExpiredBtn">
                        <i class="fa-solid fa-broom me-1"></i> Clean Expired (2h+)
                    </button>
                    <button type="button" class="btn btn-danger btn-action" id="clearSessionsBtn">
                        <i class="fa-solid fa-user-slash me-1"></i> Clear ALL Sessions
                    </button>
                </div>
                <div class="alert alert-warning py-2 px-3 mt-2 mb-0 d-flex align-items-center gap-2" style="font-size: 0.78rem;">
                    <i class="fa-solid fa-triangle-exclamation text-warning fs-6"></i>
                    <span><strong>Warning:</strong> Clearing all sessions immediately logs out everyone, including you!</span>
                </div>
                <span class="text-secondary small mt-2 d-block" id="sessionStatus"></span>
            </div>
        </div>
    </div>

    <!-- Data Retention Section -->
    <div class="col-lg-12">
        <div class="card settings-card glass-card">
            <div class="card-body p-4">
                <div class="section-head">
                    <div class="head-icon"><i class="fa-solid fa-hourglass-half"></i></div>
                    <div>
                        <h5 class="fw-bold mb-0">Data Retention</h5>
                        <small class="text-secondary">Automate purging of old uploads &amp; records</small>
                    </div>
                </div>
                <p class="text-secondary small mb-3">
                    Automatically purge uploaded SMS files and database rows older than the configured number of days.
                    This is enforced by the <code>data:retention</code> cron job — create one under
                    <a href="<?= base_url('admin/crons') ?>">Cron Jobs</a> (type "Enforce Data Retention") to schedule it, or run it manually.
                </p>
                <form id="retentionForm" class="row g-3 align-items-end">
                    <?= csrf_field() ?>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Retention Period (days)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-calendar-days text-primary"></i></span>
                            <input type="number" class="form-control" name="retention_days" id="retentionDays" min="0" max="3650" value="<?= esc($retention_days) ?>" required>
                        </div>
                        <small class="text-secondary">0 disables automatic purge.</small>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold"><i class="fa-solid fa-floppy-disk me-1"></i> Save Retention</button>
                    </div>
                    <div class="col-md-4">
                        <span class="text-secondary small" id="retentionStatus"></span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="col-lg-12">
        <div class="card settings-card glass-card">
            <div class="card-body p-4">
                <div class="section-head">
                    <div class="head-icon"><i class="fa-solid fa-circle-info"></i></div>
                    <div>
                        <h5 class="fw-bold mb-0">What these actions do</h5>
                        <small class="text-secondary">A quick guide to each maintenance action</small>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="tip-tile">
                            <h6><i class="fa-solid fa-memory text-primary"></i> Clear Cache</h6>
                            <ul>
                                <li>Removes all cached views, config, and data</li>
                                <li>Forces fresh compilation on next request</li>
                                <li>Use after config changes or deployments</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="tip-tile">
                            <h6><i class="fa-solid fa-broom text-warning"></i> Clean Expired Sessions</h6>
                            <ul>
                                <li>Removes only sessions older than 2 hours</li>
                                <li>Keeps active user sessions intact</li>
                                <li>Safe to run periodically via cron</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="tip-tile">
                            <h6><i class="fa-solid fa-user-slash text-danger"></i> Clear ALL Sessions</h6>
                            <ul>
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
    el.className = 'text-' + (type === 'success' ? 'success' : type === 'error' ? 'danger' : 'secondary') + ' small';
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
        const res = await fetch('<?= base_url('admin/system/maintenance/clear-cache') ?>', { method: 'POST' });
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
        const res = await fetch('<?= base_url('admin/system/maintenance/clean-expired-sessions') ?>', { method: 'POST' });
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
        const res = await fetch('<?= base_url('admin/system/maintenance/clear-sessions') ?>', { method: 'POST' });
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
        const res = await fetch('<?= base_url('admin/system/maintenance/save-retention') ?>', { method: 'POST', body: data });
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