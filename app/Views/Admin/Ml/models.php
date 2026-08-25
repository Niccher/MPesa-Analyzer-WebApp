<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> ML Models - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .meta-chip { background:#f4f6fb; border:1px solid #e4e8f0; border-radius:6px; padding:4px 8px; font-size:.78rem; }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-box-open me-2"></i> ML Models</h2>
        <p class="text-secondary mb-0">Upload, inspect and activate LLM models served by the backend.</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<?= view('Admin/Ml/_status', ['status' => $status]) ?>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card settings-card h-100">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="fa-solid fa-cloud-arrow-up me-2" style="color: var(--primary);"></i> Upload Model</h6>
                <p class="text-muted small mb-3">Add a <code>.gguf</code> or <code>.bin</code> model file. Large files stream to the backend.</p>
                <?php if (!$status['reachable']): ?>
                    <div class="alert alert-warning small mb-0">Backend offline — cannot upload.</div>
                <?php else: ?>
                    <div class="mb-3">
                        <input type="file" id="modelFile" class="form-control" accept=".gguf,.bin">
                    </div>
                    <div id="uploadProgress" class="progress mb-3 d-none" style="height:6px;">
                        <div class="progress-bar" role="progressbar" style="width:0%"></div>
                    </div>
                    <button type="button" id="uploadBtn" class="btn btn-primary rounded-pill px-4 fw-semibold w-100">
                        <i class="fa-solid fa-upload me-1"></i> Upload
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card settings-card h-100">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="fa-solid fa-box-open me-2" style="color: var(--primary);"></i> Active Model</h6>
                <?php
                    $active = null;
                    foreach (($status['models'] ?? []) as $m) { if (!empty($m['active'])) { $active = $m; break; } }
                    if ($active): $md = $active['metadata'] ?? [];
                ?>
                    <div class="d-flex align-items-center gap-3 flex-wrap mb-2">
                        <span class="badge bg-success">Active</span>
                        <span class="fw-semibold"><?= esc($active['filename']) ?></span>
                        <span class="text-muted small"><?= $active['size_mb'] ?> MB</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="meta-chip"><?= esc($md['n_params_label'] ?? '—') ?> params</span>
                        <span class="meta-chip"><?= esc($md['quantization'] ?? '—') ?></span>
                        <span class="meta-chip">ctx <?= esc($md['context_length'] ?? '—') ?></span>
                        <span class="meta-chip"><?= esc($md['architecture'] ?? '—') ?></span>
                        <span class="meta-chip"><?= esc($md['name'] ?? $active['filename']) ?></span>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info small mb-0">No model is currently active.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card settings-card">
    <div class="card-body p-4">
        <?= view('Admin/Ml/_nav', ['active' => 'models', 'status' => $status]) ?>

        <?php if (!$status['reachable']): ?>
            <div class="alert alert-warning mb-0">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                The ML backend is not reachable, so models cannot be listed.
            </div>
        <?php elseif (empty($status['models'])): ?>
            <div class="alert alert-info mb-0">No model files reported by the backend. Upload one above.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table id="modelTable" class="table table-sm table-striped align-middle">
                    <thead>
                        <tr><th style="width:26%">Model</th><th>Params</th><th>Quant</th><th>Context</th><th>Size</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($status['models'] as $m): $md = $m['metadata'] ?? []; ?>
                        <tr>
                            <td class="fw-semibold">
                                <?= esc($m['filename']) ?>
                                <?php if (!empty($md['name']) && $md['name'] !== $m['filename']): ?>
                                    <div class="text-muted small"><?= esc($md['name']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($md['n_params_label'] ?? '—') ?></td>
                            <td><?= esc($md['quantization'] ?? '—') ?></td>
                            <td><?= esc($md['context_length'] ?? '—') ?></td>
                            <td><?= $m['size_mb'] ?> MB</td>
                            <td>
                                <?php if (!empty($m['active'])): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#meta-<?= md5($m['filename']) ?>">
                                    <i class="fa-solid fa-circle-info"></i> Details
                                </button>
                                <?php if (empty($m['active'])): ?>
                                    <button class="btn btn-sm btn-outline-primary activate-model" data-filename="<?= esc($m['filename']) ?>" data-llm-model="<?= esc(pathinfo($m['filename'], PATHINFO_FILENAME)) ?>">
                                        <i class="fa-solid fa-play me-1"></i> Activate
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-model" data-filename="<?= esc($m['filename']) ?>">
                                        <i class="fa-solid fa-trash me-1"></i>
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted small">In use</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr class="collapse" id="meta-<?= md5($m['filename']) ?>" data-bs-parent="#modelTable">
                            <td colspan="7" class="bg-light">
                                <div class="d-flex flex-wrap gap-2 py-2">
                                    <?php foreach (['architecture','block_count','embedding_length','file_type','params_raw'] as $k): ?>
                                        <?php if (!empty($md[$k])): ?>
                                            <span class="meta-chip"><strong><?= esc($k) ?>:</strong> <?= esc(is_array($md[$k]) ? json_encode($md[$k]) : $md[$k]) ?></span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <?php if (empty($md)): ?><span class="text-muted small">No metadata available.</span><?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="alert alert-info mt-3 mb-0 small">
                <i class="fa-solid fa-circle-info me-1"></i>
                Activating a model updates the backend configuration. A llama.cpp restart is required for the new model (and context/batch/gpu changes) to actually be served.
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
const uploadBtn = document.getElementById('uploadBtn');
if (uploadBtn) {
    uploadBtn.addEventListener('click', function() {
        const input = document.getElementById('modelFile');
        const file = input.files && input.files[0];
        if (!file) { showAlert('Upload', 'Please choose a model file.', 'warning'); return; }
        if (!/\.(gguf|bin)$/i.test(file.name)) { showAlert('Upload', 'Only .gguf and .bin files are allowed.', 'danger'); return; }

        const data = new FormData();
        data.append('model', file);

        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Uploading...';
        document.getElementById('uploadProgress').classList.remove('d-none');
        const bar = document.querySelector('#uploadProgress .progress-bar');

        fetch('<?= base_url('admin/ml/models/upload') ?>', { method: 'POST', body: data })
            .then(r => { bar.style.width = '100%'; return r.json(); })
            .then(res => {
                showAlert('Upload', res.message, res.status === 'ok' ? 'success' : 'danger');
                if (res.status === 'ok') setTimeout(() => window.location.reload(), 1500);
            })
            .catch(err => showAlert('Error', err.message, 'danger'))
            .finally(() => {
                setTimeout(() => {
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = '<i class="fa-solid fa-upload me-1"></i> Upload';
                    document.getElementById('uploadProgress').classList.add('d-none');
                    bar.style.width = '0%';
                }, 1500);
            });
    });
}

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

document.querySelectorAll('.delete-model').forEach(btn => {
    btn.addEventListener('click', function() {
        const filename = this.dataset.filename;
        if (!confirm('Delete model "' + filename + '"? This permanently removes the file from the backend.')) return;
        const data = new FormData();
        data.append('filename', filename);
        fetch('<?= base_url('admin/ml/models/delete') ?>', { method: 'POST', body: data })
            .then(r => r.json()).then(res => {
                showAlert('Delete', res.message, res.status === 'ok' ? 'success' : 'danger');
                if (res.status === 'ok') setTimeout(() => window.location.reload(), 1500);
            })
            .catch(err => showAlert('Error', err.message, 'danger'));
    });
});
</script>
<?= $this->endSection() ?>