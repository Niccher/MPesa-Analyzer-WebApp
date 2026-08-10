<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> ML Allowed Senders - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-star me-2"></i> ML Allowed Senders</h2>
        <p class="text-secondary mb-0">Senders always treated as finance-related ("allowed by default").</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<?= view('Admin/Ml/_status', ['status' => $status]) ?>

<div class="card settings-card">
    <div class="card-body p-4">
        <?= view('Admin/Ml/_nav', ['active' => 'allowed']) ?>

        <?php if ($fallback_active): ?>
            <div class="alert alert-warning d-flex align-items-start gap-2">
                <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                <div>
                    <strong>Fallback list in use.</strong> The allowed table is empty, so the ML backend is using its
                    hardcoded defaults. Add senders below to override them, or leave empty to keep the built-in list.
                </div>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <form id="allowedForm" class="card settings-card">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-plus me-1"></i> Add Sender</h6>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Sender</label>
                            <input type="text" class="form-control" name="sender" placeholder="e.g. EQUITY" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category</label>
                            <select class="form-select" name="category">
                                <option value="">-- Category --</option>
                                <option value="Mobile Money">Mobile Money</option>
                                <option value="Bank">Bank</option>
                                <option value="Fintech">Fintech</option>
                                <option value="SACCO">SACCO</option>
                                <option value="Insurance">Insurance</option>
                                <option value="Payments/Govt">Payments/Govt</option>
                                <option value="Other Finance">Other Finance</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-semibold"><i class="fa-solid fa-star me-1"></i> Add to Allowed</button>
                    </div>
                </form>

                <div class="card settings-card mt-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-2" style="color: var(--primary);"><i class="fa-solid fa-rotate-left me-1"></i> Reset</h6>
                        <p class="small text-muted mb-3">Clear the table so the ML backend returns to its hardcoded defaults.</p>
                        <button type="button" class="btn btn-outline-danger w-100 rounded-pill" id="resetBtn">
                            <i class="fa-solid fa-broom me-1"></i> Reset to Hardcoded
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Sender</th>
                                <th>Category</th>
                                <th>Added</th>
                                <th style="width: 90px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($allowed)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        No custom allowed senders. The hardcoded fallback list is active.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($allowed as $a): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= esc($a['sender']) ?></td>
                                        <td><?= esc($a['category']) ?: '<span class="text-muted">—</span>' ?></td>
                                        <td><small><?= esc($a['created_at']) ?></small></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger remove-btn" data-sender="<?= esc($a['sender'], 'attr') ?>">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info mt-3 mb-0 small">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Allowed senders skip LLM classification (they are always finance) but their SMS is still parsed for
                    transaction details. The ML backend checks this table on every processing cycle and falls back to
                    its hardcoded list when it is empty.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('allowedForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = new FormData(this);
    const btn = this.querySelector('button[type=submit]');
    btn.disabled = true;
    fetch('<?= base_url('admin/ml/allowed/add') ?>', { method: 'POST', body: data })
        .then(r => r.json()).then(res => {
            showAlert('Allowed Senders', res.message, res.status === 'success' ? 'success' : 'danger');
            if (res.status === 'success') setTimeout(() => window.location.reload(), 1200);
        })
        .catch(err => showAlert('Error', err.message, 'danger'))
        .finally(() => { btn.disabled = false; });
});

document.querySelectorAll('.remove-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const sender = this.dataset.sender;
        if (!confirm('Remove "' + sender + '" from the allowed list?')) return;
        const data = new FormData();
        data.append('sender', sender);
        fetch('<?= base_url('admin/ml/allowed/remove') ?>', { method: 'POST', body: data })
            .then(r => r.json()).then(res => {
                showAlert('Allowed Senders', res.message, res.status === 'success' ? 'success' : 'danger');
                if (res.status === 'success') setTimeout(() => window.location.reload(), 1200);
            })
            .catch(err => showAlert('Error', err.message, 'danger'));
    });
});

document.getElementById('resetBtn').addEventListener('click', function() {
    if (!confirm('Clear the entire allowed list? The ML backend will fall back to its hardcoded defaults.')) return;
    fetch('<?= base_url('admin/ml/allowed/reset') ?>', { method: 'POST' })
        .then(r => r.json()).then(res => {
            showAlert('Allowed Senders', res.message, res.status === 'success' ? 'success' : 'danger');
            if (res.status === 'success') setTimeout(() => window.location.reload(), 1200);
        })
        .catch(err => showAlert('Error', err.message, 'danger'));
});
</script>
<?= $this->endSection() ?>
