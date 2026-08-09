<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> Database Info - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .stat-tile { border-radius: 4px; }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-database me-2"></i> Database Information</h2>
        <p class="text-secondary mb-0">All details about the application database</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<!-- Overview stat tiles -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card settings-card">
            <div class="card-body p-3">
                <div class="text-secondary small fw-semibold text-uppercase mb-1"><i class="fa-solid fa-server me-1"></i> Database</div>
                <div class="fw-bold fs-5 text-truncate" title="<?= esc($server['database']) ?>"><?= esc($server['database']) ?></div>
                <small class="text-muted"><?= esc($server['driver']) ?> driver</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card settings-card">
            <div class="card-body p-3">
                <div class="text-secondary small fw-semibold text-uppercase mb-1"><i class="fa-solid fa-hard-drive me-1"></i> Total Size</div>
                <div class="fw-bold fs-5"><?= esc($stats['size_mb'] ?? '0') ?> MB</div>
                <small class="text-muted"><?= esc($total_size_human) ?></small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card settings-card">
            <div class="card-body p-3">
                <div class="text-secondary small fw-semibold text-uppercase mb-1"><i class="fa-solid fa-table-list me-1"></i> Tables</div>
                <div class="fw-bold fs-5"><?= (int)($stats['table_count'] ?? 0) ?></div>
                <small class="text-muted">Data: <?= esc($stats['data_mb'] ?? '0') ?> MB · Index: <?= esc($stats['index_mb'] ?? '0') ?> MB</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card settings-card">
            <div class="card-body p-3">
                <div class="text-secondary small fw-semibold text-uppercase mb-1"><i class="fa-solid fa-layer-group me-1"></i> Approx Rows</div>
                <div class="fw-bold fs-5"><?= esc($total_rows) ?></div>
                <small class="text-muted">Estimated (InnoDB)</small>
            </div>
        </div>
    </div>
</div>

<!-- Server info + Storage engines -->
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card settings-card h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-circle-info me-2"></i> Server Information</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted text-uppercase fw-semibold">Version</small>
                            <div class="fw-semibold"><?= esc($server['version']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted text-uppercase fw-semibold">Host</small>
                            <div class="fw-semibold"><?= esc($server['host']) ?>:<?= esc($server['port']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted text-uppercase fw-semibold">Driver</small>
                            <div class="fw-semibold"><?= esc($server['driver']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted text-uppercase fw-semibold">Charset</small>
                            <div class="fw-semibold"><?= esc($server['charset'] ?: '—') ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted text-uppercase fw-semibold">Collation</small>
                            <div class="fw-semibold"><?= esc($server['collation'] ?: '—') ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted text-uppercase fw-semibold">Server Comment</small>
                            <div class="fw-semibold text-truncate" title="<?= esc($server['comment']) ?>"><?= esc($server['comment'] ?: '—') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card settings-card h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-gears me-2"></i> Storage Engines</h5>
                <?php if (empty($engines)): ?>
                    <div class="text-muted">No engine data.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Engine</th>
                                    <th class="text-end">Tables</th>
                                    <th class="text-end">Size</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($engines as $e): ?>
                                    <tr>
                                        <td class="fw-semibold"><i class="fa-solid fa-cubes me-1 text-secondary"></i><?= esc($e['engine_name']) ?></td>
                                        <td class="text-end"><?= (int) $e['table_count'] ?></td>
                                        <td class="text-end"><?= esc($e['size_mb']) ?> MB</td>
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

<!-- Tables -->
<div class="card settings-card mt-4">
    <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="fw-bold mb-0" style="color: var(--primary);"><i class="fa-solid fa-table-list me-2"></i> Tables <span class="text-secondary fs-6 fw-normal">(<?= count($tables) ?>)</span></h5>
            <input type="text" id="tableSearch" class="form-control form-control-sm" placeholder="Filter tables..." style="max-width: 240px;">
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" id="tablesTable">
                <thead class="table-light">
                    <tr>
                        <th>Table</th>
                        <th class="text-end">Rows</th>
                        <th class="text-end">Data</th>
                        <th class="text-end">Index</th>
                        <th class="text-end">Total</th>
                        <th>Engine</th>
                        <th>Collation</th>
                        <th class="text-end">Fields</th>
                        <th>Columns</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tables as $t): ?>
                        <tr>
                            <td class="fw-semibold"><i class="fa-solid fa-table me-1 text-secondary"></i><?= esc($t['name']) ?></td>
                            <td class="text-end"><?= number_format((int) $t['row_count']) ?></td>
                            <td class="text-end"><?= esc($t['data_human']) ?></td>
                            <td class="text-end"><?= esc($t['index_human']) ?></td>
                            <td class="text-end fw-semibold"><?= esc($t['size_human']) ?></td>
                            <td><small><?= esc($t['engine_name']) ?></small></td>
                            <td><small class="text-muted"><?= esc($t['collation']) ?></small></td>
                            <td class="text-end"><?= $t['field_count'] ?></td>
                            <td><small class="text-muted"><?= htmlspecialchars($t['field_list']) ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('tableSearch');
    const rows = document.querySelectorAll('#tablesTable tbody tr');
    input.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        rows.forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
});
</script>
<?= $this->endSection() ?>
