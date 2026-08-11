<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> ML Config - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); }
    .cfg-label { font-weight:600; font-size:.82rem; color:#33415c; margin-bottom:.3rem; }
    .cfg-desc { font-size:.76rem; color:#6c757d; }
    .cfg-default { font-size:.74rem; color:#dc3545; }
    .cfg-default.unset { font-weight:600; }
    .cfg-default i { margin-right:.25rem; }
    .input-group-text.cfg-ico { background:#f4f6fb; color:var(--primary); border:1px solid #dfe4ef; font-size:.85rem; }
    .form-control { border:1px solid #dfe4ef; }

</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-sliders me-2"></i> ML Config</h2>
        <p class="text-secondary mb-0">Tune how the ML backend classifies and extracts SMS financial data.</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<?= view('Admin/Ml/_status', ['status' => $status]) ?>

<div class="card settings-card">
    <div class="card-body p-4">
        <?= view('Admin/Ml/_nav', ['active' => 'config']) ?>

        <?php if (!$status['reachable']): ?>
            <div class="alert alert-warning mb-0">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                The ML backend is not reachable, so current values cannot be loaded.
            </div>
        <?php else: ?>
            <?php $cfg = $status['app'] ?? []; ?>

            <div class="alert alert-light border mb-4 small">
                <i class="fa-solid fa-envelope me-1"></i>
                The LLM processes short SMS messages, so the defaults below are tuned for fast, low-cost,
                deterministic extraction. Values in <span class="text-danger">red</span> show what the backend
                falls back to when a field is left empty — a llama.cpp restart applies context, batch and GPU changes.
            </div>

            <form id="configForm">
                <?= csrf_field() ?>

                <?php
                // label, icon, description, default, type, min, max, step
                $fields = [
                    'llm_model'       => ['LLM Model', 'fa-microchip', 'Model name sent to the LLM provider. Should match the active .gguf file.', 'qwen2.5-1.5b-instruct', 'text', null, null, null],
                    'llm_max_tokens'  => ['Max Tokens', 'fa-note-sticky', 'Maximum output length per LLM call. SMS extractions are short, so 512 is usually enough.', 512, 'number', 256, 8192, null],
                    'llm_temperature' => ['Temperature', 'fa-thermometer-half', 'Sampling randomness. Kept low for consistent financial JSON output.', 0.1, 'number', 0, 2, 0.05],
                    'llm_ctx_size'    => ['Context Size', 'fa-arrows-left-right', 'llama.cpp context window (tokens). Enables larger prompt batches of SMS.', 8192, 'number', 256, 131072, null],
                    'llm_batch_size'  => ['Prompt Batch', 'fa-layer-group', 'llama.cpp prompt-processing batch size. Raise with GPU memory.', 512, 'number', 1, 8192, null],
                    'n_gpu_layers'    => ['GPU Layers', 'fa-memory', 'Layers offloaded to the GPU; 0 keeps everything on CPU.', 0, 'number', 0, 512, null],
                    'batch_size'      => ['SMS Batch Size', 'fa-bolt', 'How many unprocessed SMS are pulled and analyzed per cycle.', 50, 'number', 1, 500, null],
                    'max_retries'     => ['Max Retries', 'fa-rotate', 'Retries for failed LLM calls before a message is marked as an error.', 3, 'number', 0, 10, null],
                    'poll_interval'   => ['Poll Interval (s)', 'fa-clock', 'Seconds between background polls for new unprocessed SMS.', 30, 'number', 5, 3600, null],
                ];
                ?>
                <div class="row g-4">
                    <?php foreach ($fields as $name => [$label, $icon, $desc, $dflt, $type, $min, $max, $step]): ?>
                        <?php
                        $val = $cfg[$name] ?? null;
                        $isSet = $val !== null && $val !== '';
                        ?>
                        <div class="col-md-6 col-xl-4">
                            <label class="cfg-label" for="<?= esc($name) ?>"><?= esc($label) ?></label>
                            <div class="input-group">
                                <span class="input-group-text cfg-ico"><i class="fa-solid <?= esc($icon) ?>"></i></span>
                                <input type="<?= esc($type) ?>"
                                       class="form-control"
                                       id="<?= esc($name) ?>"
                                       name="<?= esc($name) ?>"
                                       value="<?= esc($val ?? '') ?>"
                                       <?= $min !== null ? 'min="' . $min . '"' : '' ?>
                                       <?= $max !== null ? 'max="' . $max . '"' : '' ?>
                                       <?= $step !== null ? 'step="' . $step . '"' : '' ?>>
                            </div>
                            <div class="cfg-desc mt-1"><?= esc($desc) ?></div>
                            <div class="cfg-default <?= $isSet ? '' : 'unset' ?>">
                                <i class="fa-solid fa-circle-info"></i>
                                <?php if ($isSet): ?>
                                    Default if unset: <code><?= esc($dflt) ?></code>
                                <?php else: ?>
                                    No value set — using default <code><?= esc($dflt) ?></code>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save Config
                        </button>
                    </div>
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