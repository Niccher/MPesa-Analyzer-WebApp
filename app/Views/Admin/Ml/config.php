<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> ML Config - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-sliders me-2"></i> ML Config</h2>
        <p class="text-secondary mb-0">Tune how the ML backend processes SMS batches.</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<?= view('Admin/Ml/_status', ['status' => $status]) ?>

<div class="card settings-card">
    <div class="card-body p-4">
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/ml') ?>">Status</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/ml/models') ?>">Models</a></li>
            <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/ml/config') ?>">Config</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/ml/test') ?>">Test Prompt</a></li>
        </ul>

        <?php if (!$status['reachable']): ?>
            <div class="alert alert-warning mb-0">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                The ML backend is not reachable, so current values cannot be loaded.
            </div>
        <?php else: ?>
            <?php $cfg = $status['app'] ?? []; ?>
            <form id="configForm" class="row g-4">
                <?= csrf_field() ?>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">LLM Model</label>
                    <input type="text" class="form-control" name="llm_model" value="<?= esc($cfg['llm_model'] ?? '') ?>">
                    <small class="text-muted">Model name sent to the LLM provider.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Max Tokens</label>
                    <input type="number" class="form-control" name="llm_max_tokens" value="<?= esc($cfg['llm_max_tokens'] ?? 2048) ?>" min="256" max="8192">
                    <small class="text-muted">Maximum output length per LLM call.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Batch Size</label>
                    <input type="number" class="form-control" name="batch_size" value="<?= esc($cfg['batch_size'] ?? 20) ?>" min="1" max="500">
                    <small class="text-muted">SMS processed per batch cycle.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Max Retries</label>
                    <input type="number" class="form-control" name="max_retries" value="<?= esc($cfg['max_retries'] ?? 3) ?>" min="0" max="10">
                    <small class="text-muted">Retries for failed LLM calls.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Poll Interval (seconds)</label>
                    <input type="number" class="form-control" name="poll_interval" value="<?= esc($cfg['poll_interval'] ?? 30) ?>" min="5" max="3600">
                    <small class="text-muted">How often the backend polls for new SMS.</small>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold"><i class="fa-solid fa-floppy-disk me-1"></i> Save Config</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('configForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const data = new FormData(form);
    const btn = form.querySelector('button[type=submit]');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
    fetch('<?= base_url('admin/ml/config/save') ?>', { method: 'POST', body: data })
        .then(r => r.json()).then(res => {
            showAlert('ML Config', res.message, res.status === 'success' ? 'success' : 'danger');
        })
        .catch(err => showAlert('Error', err.message, 'danger'))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Save Config';
        });
});
</script>
<?= $this->endSection() ?>
