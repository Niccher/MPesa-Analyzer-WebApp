<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Data Management - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-database me-2"></i>Data Management</h2>
        <p class="text-secondary mb-0">Export, purge, and manage your financial data.</p>
    </div>
    <a href="<?= base_url('dashboard/settings') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Settings
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (session()->has('message')) : ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= session('message') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (session()->has('error')) : ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= session('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card settings-card h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4" style="color: var(--primary);"><i class="fa-solid fa-download me-2"></i>Export Transactions</h5>
                <p class="text-muted small mb-3">Download all your analyzed transactions for backup or external analysis.</p>
                <div class="d-flex gap-3">
                    <a href="<?= base_url('dashboard/settings/export/csv') ?>" class="btn btn-success rounded-pill px-4 fw-semibold">
                        <i class="fa-solid fa-file-csv me-1"></i> Export CSV
                    </a>
                    <a href="<?= base_url('dashboard/settings/export/json') ?>" class="btn btn-info rounded-pill px-4 fw-semibold text-white">
                        <i class="fa-solid fa-file-code me-1"></i> Export JSON
                    </a>
                </div>
            </div>
        </div>

        <div class="card settings-card mt-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4" style="color: var(--primary);"><i class="fa-solid fa-clock-rotate-left me-2"></i>Delete Upload Batches</h5>
                <p class="text-muted small mb-3">Remove specific upload batches and their associated SMS data.</p>
                <?php
                $db = \Config\Database::connect();
                $userId = auth()->user()->id;
                $tokenType = \CodeIgniter\Shield\Authentication\Authenticators\AccessTokens::ID_TYPE_ACCESS_TOKEN;
                $uploads = $db->query("
                    SELECT l.* FROM tbl_Loot l
                    INNER JOIN auth_identities i ON i.secret = SHA2(l.loot_Owner, 256)
                    WHERE i.user_id = ? AND i.type = ?
                    ORDER BY l.loot_Created DESC LIMIT 20
                ", [$userId, $tokenType])->getResult();
                ?>
                <?php if (!empty($uploads)): ?>
                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>File</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($uploads as $upload): ?>
                            <tr>
                                <td><small><?= $upload->loot_Created ?? 'N/A' ?></small></td>
                                <td><small><?= htmlspecialchars($upload->loot_Name ?? 'N/A') ?></small></td>
                                <td>
                                    <form action="<?= base_url('dashboard/settings/data/delete-upload') ?>" method="POST"
                                          onsubmit="return confirm('Delete this upload batch and all its SMS data? This cannot be undone.');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="uuid" value="<?= $upload->loot_Uuid ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">
                                            <i class="fa-solid fa-trash-can"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info mb-0">
                    <i class="fa-solid fa-circle-info me-1"></i> No uploads found.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card settings-card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4" style="color: var(--primary);"><i class="fa-solid fa-eraser me-2"></i>Purge Old Data</h5>
                <p class="text-muted small mb-3">
                    Remove SMS records and analyzed transactions older than a specified number of months.
                    Your account and settings will remain intact.
                </p>
                <div class="mb-3">
                    <span class="fw-semibold text-dark">Total Uploads:</span>
                    <span class="badge bg-primary rounded-pill ms-2"><?= $total_uploads ?? 0 ?></span>
                </div>
                <div class="mb-3">
                    <span class="fw-semibold text-dark">Oldest Record:</span>
                    <span class="text-muted ms-2"><?= $oldest_upload ?></span>
                </div>
                <form action="<?= base_url('dashboard/settings/data/purge') ?>" method="POST"
                      onsubmit="return confirm('This will permanently delete data older than the selected period. This cannot be undone. Continue?');">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Delete data older than:</label>
                        <select class="form-select" name="months">
                            <option value="3">3 months</option>
                            <option value="6">6 months</option>
                            <option value="12" selected>12 months</option>
                            <option value="24">24 months</option>
                            <option value="36">36 months</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold">
                        <i class="fa-solid fa-trash-can me-1"></i> Purge Old Data
                    </button>
                </form>
            </div>
        </div>

        <div class="card settings-card mt-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4" style="color: var(--primary);"><i class="fa-solid fa-floppy-disk me-2"></i>Settings Backup</h5>
                <p class="text-muted small mb-3">Export or restore your preferences, budgets, and category rules.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="<?= base_url('dashboard/settings/data/export-settings') ?>" class="btn btn-outline-primary rounded-pill px-4 fw-semibold">
                        <i class="fa-solid fa-download me-1"></i> Export Settings
                    </a>
                    <form action="<?= base_url('dashboard/settings/data/import-settings') ?>" method="POST" enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
                        <?= csrf_field() ?>
                        <input type="file" name="settings_file" accept=".json" class="form-control form-control-sm" style="max-width:200px;" required>
                        <button type="submit" class="btn btn-outline-success rounded-pill px-4 fw-semibold btn-sm">
                            <i class="fa-solid fa-upload me-1"></i> Import
                        </button>
                    </form>
                </div>
                <hr class="my-3">
                <h6 class="fw-bold mb-2">Category Rules</h6>
                <p class="text-muted small mb-2">Export or bulk-import keyword-to-category mappings as CSV.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="<?= base_url('dashboard/settings/data/export-rules') ?>" class="btn btn-outline-info rounded-pill px-4 fw-semibold btn-sm">
                        <i class="fa-solid fa-download me-1"></i> Export Rules
                    </a>
                    <form action="<?= base_url('dashboard/settings/data/import-rules') ?>" method="POST" enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
                        <?= csrf_field() ?>
                        <input type="file" name="rules_file" accept=".csv" class="form-control form-control-sm" style="max-width:200px;" required>
                        <button type="submit" class="btn btn-outline-success rounded-pill px-4 fw-semibold btn-sm">
                            <i class="fa-solid fa-upload me-1"></i> Import CSV
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card settings-card mt-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4" style="color: var(--primary);"><i class="fa-solid fa-circle-info me-2"></i>Data Summary</h5>
                <p class="text-muted small mb-0">Your account currently has <strong><?= $total_uploads ?? 0 ?></strong> upload batch(es) 
                with the earliest record from <strong><?= $oldest_upload ?></strong>. Use the export feature above to download a backup before purging old data.</p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
