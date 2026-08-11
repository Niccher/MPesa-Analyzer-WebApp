<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> ML Backend - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); }
    .cfg-icon { width: 30px; height: 30px; border-radius: 7px; display:inline-flex; align-items:center; justify-content:center; font-size:.78rem; background:#f4f6fb; color:var(--primary); flex-shrink:0; }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-microchip me-2"></i> ML Backend</h2>
        <p class="text-secondary mb-0">Manage the ML analysis service: health, models, configuration and testing.</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<?= view('Admin/Ml/_status', ['status' => $status]) ?>

<div class="card settings-card">
    <div class="card-body p-4">
        <?= view('Admin/Ml/_nav', ['active' => 'status']) ?>

        <?php if ($status['reachable']): ?>
        <div class="row g-4">
            <div class="col-lg-6">
                <h6 class="fw-bold text-uppercase text-secondary mb-3" style="font-size:0.75rem; letter-spacing:1px;">Current Configuration</h6>
                <?php
                $cfgIcons = [
                    'llm_provider'   => 'fa-plug',
                    'llm_model'      => 'fa-microchip',
                    'llm_max_tokens' => 'fa-note-sticky',
                    'llm_temperature'=> 'fa-thermometer-half',
                    'llm_ctx_size'   => 'fa-arrows-left-right',
                    'llm_batch_size' => 'fa-layer-group',
                    'n_gpu_layers'   => 'fa-memory',
                    'llm_base_url'   => 'fa-link',
                    'batch_size'     => 'fa-bolt',
                    'max_retries'    => 'fa-rotate',
                    'poll_interval'  => 'fa-clock',
                    'model_path'     => 'fa-folder-open',
                    'uptime'         => 'fa-server',
                ];
                ?>
                <table class="table table-sm table-striped system-table">
                    <tbody>
                        <?php foreach ($status['app'] as $key => $value): ?>
                        <tr>
                            <td class="fw-semibold w-50">
                                <span class="cfg-icon me-2"><i class="fa-solid <?= esc($cfgIcons[$key] ?? 'fa-gear') ?>"></i></span>
                                <?= esc(str_replace('_', ' ', $key)) ?>
                            </td>
                            <td><code><?= esc((string)$value) ?></code></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td class="fw-semibold">
                                <span class="cfg-icon me-2"><i class="fa-solid fa-server"></i></span>
                                Uptime
                            </td>
                            <td><code><?= $status['uptime'] !== null ? round($status['uptime'] / 3600, 1) . ' hours' : 'unknown' ?></code></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-lg-6">
                <h6 class="fw-bold text-uppercase text-secondary mb-3" style="font-size:0.75rem; letter-spacing:1px;">Available Models</h6>
                <?php if (empty($status['models'])): ?>
                    <p class="text-muted">No models reported by backend.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($status['models'] as $m): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <span class="fw-semibold"><?= esc($m['filename']) ?></span>
                                <br><small class="text-muted"><?= $m['size_mb'] ?> MB</small>
                            </div>
                            <?php if (!empty($m['active'])): ?>
                                <span class="badge bg-success">ACTIVE</span>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
            <div class="alert alert-warning mb-0">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                The ML backend is not reachable. Check that the <code>ml-mpesa-analyzer</code> container is running.
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
