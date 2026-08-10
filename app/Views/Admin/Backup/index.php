<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> Database Backup - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .backup-row { cursor: pointer; }
    .backup-row:hover { background: rgba(177,184,237,0.1); }
    .stat-card { border-left: 4px solid var(--primary); }
    .table-row:hover { background: rgba(177,184,237,0.05); }
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

<div class="row g-4">
    <!-- Database Info Card -->
    <div class="col-lg-4">
        <div class="card settings-card h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-server me-2"></i> Database Info</h5>
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="stat-card p-3 bg-light rounded">
                            <div class="text-muted small"><i class="fa-solid fa-database text-primary me-1"></i> Driver</div>
                            <div class="fw-bold"><?= esc($db_info['driver']) ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card p-3 bg-light rounded">
                            <div class="text-muted small"><i class="fa-solid fa-server text-primary me-1"></i> Host</div>
                            <div class="fw-bold small"><?= esc($db_info['host']) ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card p-3 bg-light rounded">
                            <div class="text-muted small"><i class="fa-solid fa-cylinder text-primary me-1"></i> Database</div>
                            <div class="fw-bold small"><?= esc($db_info['database']) ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card p-3 bg-light rounded">
                            <div class="text-muted small"><i class="fa-solid fa-database text-primary me-1"></i> Version</div>
                            <div class="fw-bold small"><?= esc($db_info['version']) ?></div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="text-muted small">
                    <strong><i class="fa-solid fa-folder text-primary me-1"></i> Backup directory:</strong> 
                    <code><?= WRITEPATH ?>backups/</code>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Statistics Card -->
    <div class="col-lg-8">
        <div class="card settings-card h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-table me-2"></i> Table Statistics</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="stat-card p-3 bg-light rounded text-center">
                            <div class="text-muted small"><i class="fa-solid fa-list text-primary me-1"></i> Total Tables</div>
                            <div class="fw-bold fs-4"><?= $table_stats['total_tables'] ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card p-3 bg-light rounded text-center">
                            <div class="text-muted small"><i class="fa-solid fa-list-ol text-primary me-1"></i> Total Rows</div>
                            <div class="fw-bold fs-4"><?= number_format($table_stats['total_rows']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card p-3 bg-light rounded text-center">
                            <div class="text-muted small"><i class="fa-solid fa-hdd text-primary me-1"></i> Total Size</div>
                            <div class="fw-bold fs-4"><?= esc($table_stats['total_size_human']) ?></div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width: 50px;"><i class="fa-solid fa-hashtag text-muted"></i></th>
                                <th>Table Name</th>
                                <th class="text-end" style="width: 120px;"><i class="fa-solid fa-list-ol text-muted"></i> Rows</th>
                                <th class="text-end" style="width: 120px;"><i class="fa-solid fa-hdd text-muted"></i> Size</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($table_stats['tables'] as $idx => $table): ?>
                                <tr class="table-row">
                                    <td class="text-muted small"><?= $idx + 1 ?></td>
                                    <td>
                                        <code class="small"><?= esc($table['name']) ?></code>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-semibold"><?= number_format($table['rows']) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-muted small"><?= esc($table['size_human']) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="2" class="text-end"><i class="fa-solid fa-sigma text-primary me-1"></i> Totals</td>
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
        <div class="card settings-card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-download me-2"></i> Create New Backup</h5>
                <form id="backupForm" class="row g-3">
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="structure_only" id="structureOnly" value="1">
                            <label class="form-check-label fw-semibold" for="structureOnly">Structure only (no data)</label>
                        </div>
                        <small class="text-muted">Creates schema-only dump (tables, indexes, views)</small>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="compress" id="compress" value="1" checked>
                            <label class="form-check-label fw-semibold" for="compress">Compress (gzip)</label>
                        </div>
                        <small class="text-muted">Reduces file size significantly</small>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 fw-semibold">
                            <i class="fa-solid fa-download me-1"></i> Download Backup
                        </button>
                        <span id="backupStatus" class="ms-3 text-muted small"></span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Backup History -->
    <div class="col-lg-12">
        <div class="card settings-card">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0" style="color: var(--primary);"><i class="fa-solid fa-history me-2"></i> Backup History <span class="badge bg-secondary ms-1"><?= count($backup_history) ?></span></h5>
                    <a href="<?= base_url('admin/audit') . '?category=system&action=db_backup_download' ?>" class="btn btn-outline-secondary btn-sm" target="_blank">
                        <i class="fa-solid fa-clipboard-list me-1"></i> Audit Log
                    </a>
                </div>

                <?php if (empty($backup_history)): ?>
                    <div class="text-center text-muted p-5">
                        <i class="fa-solid fa-inbox fs-1 d-block mb-2"></i>
                        No backups yet. Create one above to get started.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;"><i class="fa-solid fa-hashtag text-muted"></i></th>
                                    <th>Filename</th>
                                    <th class="text-center" style="width: 100px;"><i class="fa-solid fa-hdd text-muted"></i> Size</th>
                                    <th style="width: 180px;"><i class="fa-solid fa-clock text-muted"></i> Created</th>
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($backup_history as $idx => $backup): ?>
                                    <tr class="backup-row">
                                        <td class="text-muted small"><?= $idx + 1 ?></td>
                                        <td>
                                            <code><?= esc($backup['name']) ?></code>
                                        </td>
                                        <td class="text-center"><?= esc($backup['human_size']) ?></td>
                                        <td><?= esc($backup['modified']) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('admin/backup/download-file/' . urlencode($backup['name'])) ?>" class="btn btn-outline-primary" title="Download">
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
                        </table>
                    </div>
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
        const response = await fetch('<?= base_url('admin/backup/download') ?>?' + params);
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
            const res = await fetch('<?= base_url('admin/backup/delete') ?>', {
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