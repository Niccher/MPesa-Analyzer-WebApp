<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> ML Models - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-box-open me-2"></i> ML Models</h2>
        <p class="text-secondary mb-0">View and activate LLM models served by the backend.</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<?= view('Admin/Ml/_status', ['status' => $status]) ?>

<div class="card settings-card">
    <div class="card-body p-4">
        <?= view('Admin/Ml/_nav', ['active' => 'models']) ?>

        <?php if (!$status['reachable']): ?>
            <div class="alert alert-warning mb-0">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                The ML backend is not reachable, so models cannot be listed.
            </div>
        <?php elseif (empty($status['models'])): ?>
            <div class="alert alert-info mb-0">No model files reported by the backend.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr><th>Model File</th><th>Size</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($status['models'] as $m): ?>
                        <tr>
                            <td class="fw-semibold"><?= esc($m['filename']) ?></td>
                            <td><?= $m['size_mb'] ?> MB</td>
                            <td>
                                <?php if (!empty($m['active'])): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (empty($m['active'])): ?>
                                    <button class="btn btn-sm btn-outline-primary activate-model" data-filename="<?= esc($m['filename']) ?>" data-llm-model="<?= esc(pathinfo($m['filename'], PATHINFO_FILENAME)) ?>">
                                        <i class="fa-solid fa-play me-1"></i> Activate
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted small">In use</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="alert alert-info mt-3 mb-0 small">
                <i class="fa-solid fa-circle-info me-1"></i>
                Activating a model updates the backend configuration. A llama.cpp restart is required for the new model to actually be served — the next container restart will load it.
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.activate-model').forEach(btn => {
    btn.addEventListener('click', function() {
        const filename = this.dataset.filename;
        const llmModel = this.dataset.llmModel;
        if (!confirm('Activate model "' + filename + '"? The change takes effect after the ML backend restarts.')) return;
        const data = new FormData();
        data.append('filename', filename);
        data.append('llm_model', llmModel);
        fetch('<?= base_url('admin/ml/models/activate') ?>', { method: 'POST', body: data })
            .then(r => r.json()).then(res => {
                showAlert('Model', res.message, res.status === 'ok' ? 'success' : 'danger');
                if (res.status === 'ok') setTimeout(() => window.location.reload(), 1500);
            })
            .catch(err => showAlert('Error', err.message, 'danger'));
    });
});
</script>
<?= $this->endSection() ?>
