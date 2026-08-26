<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> Database Backup - Mpesa Analyzer <?= $this->endSection() ?>
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
        padding: 0.9rem 1rem; height: 100%;
        background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 4px;
    }
    .metric-icon {
        width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: rgba(93, 95, 239, 0.12); font-size: 0.95rem;
    }
    .metric-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted); }
    .metric-value { font-weight: 700; color: var(--text-main); }
    .metric-value.trunc { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .stat-big { text-align: center; padding: 1rem 1rem; height: 100%; background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 4px; }
    .stat-big .stat-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted); font-weight: 600; }
    .stat-big .stat-value { font-weight: 700; font-size: 1.5rem; color: var(--text-main); line-height: 1.3; }
    .backup-row { cursor: pointer; }
    .backup-row:hover { background: rgba(93, 95, 239, 0.06); }
    .table-row:hover { background: rgba(93, 95, 239, 0.06); }
    .path-box { background: var(--card-bg); border: 1px dashed var(--card-border); border-radius: 4px; }
    [data-bs-theme="dark"] .settings-card { box-shadow: 0 4px 20px rgba(0,0,0,0.25); }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-database me-2"></i> Database Backup</h2>
        <p class="text-secondary mb-0">Download SQL dumps, view table statistics, and manage backup history.</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="card settings-card mb-4">
    <div class="card-body p-4 pb-0">
        <?= view('Admin/System/_nav', ['active' => 'backup']) ?>
    </div>
</div>

<div class="row g-4">
    <!-- Database Info Card -->
    <div class="col-lg-4">
        <div class="card settings-card glass-card h-100">
            <div class="card-body p-4">
                <div class="section-head">
                    <div class="head-icon"><i class="fa-solid fa-server"></i></div>
                    <div>
                        <h5 class="fw-bold mb-0">Database Info</h5>
                        <small class="text-secondary">Connection details</small>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="metric">
                            <span class="metric-icon text-primary"><i class="fa-solid fa-gears"></i></span>
                            <div class="min-w-0">
                                <div class="metric-label">Driver</div>
                                <div class="metric-value small"><?= esc($db_info['driver']) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="metric">
                            <span class="metric-icon text-primary"><i class="fa-solid fa-network-wired"></i></span>
                            <div class="min-w-0">
                                <div class="metric-label">Host</div>
                                <div class="metric-value small trunc"><?= esc($db_info['host']) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="metric">
                            <span class="metric-icon text-primary"><i class="fa-solid fa-database"></i></span>
                            <div class="min-w-0">
                                <div class="metric-label">Database</div>
                                <div class="metric-value small trunc"><?= esc($db_info['database']) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="metric">
                            <span class="metric-icon text-primary"><i class="fa-solid fa-tag"></i></span>
                            <div class="min-w-0">
                                <div class="metric-label">Version</div>
                                <div class="metric-value small trunc"><?= esc($db_info['version']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="path-box p-3 mb-3">
                    <div class="text-secondary small fw-semibold mb-1"><i class="fa-solid fa-folder-open text-primary me-1"></i> Backup directory</div>
                    <code><?= WRITEPATH ?>backups/</code>
                </div>

                <?php
                $freeBytes = @disk_free_space(WRITEPATH) ?: 0;
                $totalBytes = @disk_total_space(WRITEPATH) ?: 1;
                $usedBytes = $totalBytes - $freeBytes;
                $pct = round(($usedBytes / $totalBytes) * 100, 1);
                $freeGB = round($freeBytes / 1024 / 1024 / 1024, 1);
                $totalGB = round($totalBytes / 1024 / 1024 / 1024, 1);
                ?>
                <div class="p-3 bg-light rounded border">
                    <div class="d-flex justify-content-between mb-1 small text-dark fw-semibold">
                        <span><i class="fa-solid fa-hard-drive text-secondary me-1"></i> Disk Safeguard</span>
                        <span><?= $freeGB ?> GB free of <?= $totalGB ?> GB</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar <?= $pct > 85 ? 'bg-danger' : 'bg-success' ?>" role="progressbar" style="width: <?= $pct ?>%" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Statistics Card -->
    <div class="col-lg-8">
        <div class="card settings-card glass-card h-100">
            <div class="card-body p-4">
                <div class="section-head">
                    <div class="head-icon"><i class="fa-solid fa-chart-simple"></i></div>
                    <div>
                        <h5 class="fw-bold mb-0">Table Statistics</h5>
                        <small class="text-secondary">Overview of stored data</small>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="stat-big">
                            <span class="stat-label"><i class="fa-solid fa-table-list me-1"></i> Total Tables</span>
                            <div class="stat-value"><?= $table_stats['total_tables'] ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-big">
                            <span class="stat-label"><i class="fa-solid fa-layer-group me-1"></i> Total Rows</span>
                            <div class="stat-value"><?= number_format($table_stats['total_rows']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-big">
                            <span class="stat-label"><i class="fa-solid fa-hard-drive me-1"></i> Total Size</span>
                            <div class="stat-value"><?= esc($table_stats['total_size_human']) ?></div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width: 50px;"><i class="fa-solid fa-hashtag text-secondary"></i></th>
                                <th>Table Name</th>
                                <th class="text-end" style="width: 120px;"><i class="fa-solid fa-list-ol text-secondary"></i> Rows</th>
                                <th class="text-end" style="width: 120px;"><i class="fa-solid fa-hard-drive text-secondary"></i> Size</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($table_stats['tables'] as $idx => $table): ?>
                                <tr class="table-row">
                                    <td class="text-secondary small"><?= $idx + 1 ?></td>
                                    <td>
                                        <code class="small"><?= esc($table['name']) ?></code>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-semibold"><?= number_format($table['rows']) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-secondary small"><?= esc($table['size_human']) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="2" class="text-end"><i class="fa-solid fa-calculator text-primary me-1"></i> Totals</td>
                                <td class="text-end"><?= number_format($table_stats['total_rows']) ?></td>
                                <td class="text-end"><?= esc($table_stats['total_size_human']) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Backup -->
    <div class="col-lg-12">
        <div class="card settings-card glass-card">
            <div class="card-body p-4">
                <div class="section-head">
                    <div class="head-icon"><i class="fa-solid fa-file-export"></i></div>
                    <div>
                        <h5 class="fw-bold mb-0">Create New Backup</h5>
                        <small class="text-secondary">Generate and download a fresh SQL dump</small>
                    </div>
                </div>
                <form id="backupForm" class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-body rounded border h-100">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="structure_only" id="structureOnly" value="1">
                                <label class="form-check-label fw-semibold" for="structureOnly"><i class="fa-solid fa-table-columns text-primary me-1"></i> Structure only (no data)</label>
                            </div>
                            <small class="text-secondary">Creates schema-only dump (tables, indexes, views)</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-body rounded border h-100">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="compress" id="compress" value="1" checked>
                                <label class="form-check-label fw-semibold" for="compress"><i class="fa-solid fa-file-zipper text-primary me-1"></i> Compress (gzip)</label>
                            </div>
                            <small class="text-secondary">Reduces file size significantly</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 fw-semibold">
                            <i class="fa-solid fa-download me-1"></i> Download Backup
                        </button>
                        <span id="backupStatus" class="ms-3 text-secondary small"></span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Backup History -->
    <div class="col-lg-12">
        <div class="card settings-card glass-card">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div class="section-head mb-0">
                        <div class="head-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                        <div>
                            <h5 class="fw-bold mb-0">Backup History <span class="badge bg-secondary ms-1"><?= count($backup_history) ?></span></h5>
                        </div>
                    </div>
                    <a href="<?= base_url('admin/audit') . '?category=system&action=db_backup_download' ?>" class="btn btn-outline-secondary btn-sm" target="_blank">
                        <i class="fa-solid fa-clipboard-list me-1"></i> Audit Log
                    </a>
                </div>

                <?php if (empty($backup_history)): ?>
                    <div class="text-center text-secondary p-5">
                        <i class="fa-solid fa-folder-open fs-1 d-block mb-2 opacity-50"></i>
                        No backups yet. Create one above to get started.
                    </div>
                <?php else: 
                    $topHistory = array_slice($backup_history, 0, 5);
                    $restHistory = array_slice($backup_history, 5);
                ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;"><i class="fa-solid fa-hashtag text-secondary"></i></th>
                                    <th>Filename</th>
                                    <th class="text-center" style="width: 100px;"><i class="fa-solid fa-hard-drive text-secondary"></i> Size</th>
                                    <th style="width: 180px;"><i class="fa-solid fa-clock text-secondary"></i> Created</th>
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topHistory as $idx => $backup): ?>
                                    <tr class="backup-row">
                                        <td class="text-secondary small"><?= $idx + 1 ?></td>
                                        <td>
                                            <i class="fa-solid fa-file-zipper text-secondary me-1"></i><code><?= esc($backup['name']) ?></code>
                                        </td>
                                        <td class="text-center"><?= esc($backup['human_size']) ?></td>
                                        <td><?= esc($backup['modified']) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('admin/system/backup/download-file/' . urlencode($backup['name'])) ?>" class="btn btn-outline-primary" title="Download">
                                                    <i class="fa-solid fa-download"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger btn-delete-backup" data-file="<?= esc($backup['name']) ?>" title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <?php if (!empty($restHistory)): ?>
                                <tbody class="collapse" id="olderBackups">
                                    <?php foreach ($restHistory as $idx => $backup): ?>
                                        <tr class="backup-row">
                                            <td class="text-secondary small"><?= $idx + 6 ?></td>
                                            <td>
                                                <i class="fa-solid fa-file-zipper text-secondary me-1"></i><code><?= esc($backup['name']) ?></code>
                                            </td>
                                            <td class="text-center"><?= esc($backup['human_size']) ?></td>
                                            <td><?= esc($backup['modified']) ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= base_url('admin/system/backup/download-file/' . urlencode($backup['name'])) ?>" class="btn btn-outline-primary" title="Download">
                                                        <i class="fa-solid fa-download"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger btn-delete-backup" data-file="<?= esc($backup['name']) ?>" title="Delete">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            <?php endif; ?>
                        </table>
                    </div>
                    <?php if (!empty($restHistory)): ?>
                        <div class="text-center mt-3">
                            <button class="btn btn-outline-primary btn-sm px-4 rounded-pill" type="button" data-bs-toggle="collapse" data-bs-target="#olderBackups" aria-expanded="false" id="toggleOlderBtn">
                                <i class="fa-solid fa-chevron-down me-1"></i> Show Older Backups (<?= count($restHistory) ?>)
                            </button>
                        </div>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const btn = document.getElementById('toggleOlderBtn');
                            const collapseEl = document.getElementById('olderBackups');
                            collapseEl.addEventListener('show.bs.collapse', function() {
                                btn.innerHTML = '<i class="fa-solid fa-chevron-up me-1"></i> Hide Older Backups';
                            });
                            collapseEl.addEventListener('hide.bs.collapse', function() {
                                btn.innerHTML = '<i class="fa-solid fa-chevron-down me-1"></i> Show Older Backups (<?= count($restHistory) ?>)';
                            });
                        });
                        </script>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
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

document.getElementById('backupForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    const status = document.getElementById('backupStatus');
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generating...';
    status.textContent = 'Creating backup...';

    const formData = new FormData(this);
    const params = new URLSearchParams();
    for (const [key, value] of formData) {
        params.append(key, value);
    }

    try {
        const response = await fetch('<?= base_url('admin/system/backup/download') ?>?' + params);
        if (!response.ok) {
            const err = await response.json();
            throw new Error(err.message || 'Backup failed');
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = response.headers.get('Content-Disposition')?.match(/filename="(.+)"/)?.[1] || 'backup.sql';
        a.click();
        window.URL.revokeObjectURL(url);

        status.textContent = 'Backup downloaded!';
        status.className = 'ms-3 text-success small';
    } catch (err) {
        status.textContent = 'Error: ' + err.message;
        status.className = 'ms-3 text-danger small';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-download me-1"></i> Download Backup';
    }
});

// Delete backup
document.querySelectorAll('.btn-delete-backup').forEach(btn => {
    btn.addEventListener('click', async function() {
        const file = this.dataset.file;
        const result = await confirmAction(
            'Delete Backup?',
            `This will permanently delete "${file}".\n\nThis action CANNOT be undone.`,
            'warning',
            'Yes, delete'
        );
        if (!result.isConfirmed) return;
        
        const row = this.closest('tr');
        row.style.opacity = '0.5';
        
        try {
            const res = await fetch('<?= base_url('admin/system/backup/delete') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'file=' + encodeURIComponent(file)
            });
            const data = await res.json();
            if (data.status === 'success') {
                row.remove();
                Swal.fire('Deleted!', data.message, 'success');
            } else {
                Swal.fire('Error', data.message, 'error');
                row.style.opacity = '1';
            }
        } catch (e) {
            Swal.fire('Error', 'Error: ' + e.message, 'error');
            row.style.opacity = '1';
        }
    });
});
</script>
<?= $this->endSection() ?>