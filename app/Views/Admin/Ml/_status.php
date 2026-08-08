<?php if (!isset($status)) return; ?>
<div class="card settings-card mb-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="stat-icon rounded d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.4rem; <?= $status['reachable'] ? 'background:#e7f6ec;color:#1e7e34;' : 'background:#fdecec;color:#c0392b;' ?>">
                <i class="fa-solid fa-microchip"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold fs-5"><?= $status['reachable'] ? 'ML Backend Online' : 'ML Backend Offline' ?></div>
                <small class="text-muted">
                    <?php if ($status['reachable']): ?>
                        <?= esc($status['app']['llm_model'] ?? 'unknown model') ?> &middot;
                        <?= esc($status['app']['llm_provider'] ?? 'provider') ?> &middot;
                        <?= $status['latency_ms'] ?>ms latency &middot;
                        llama: <strong class="text-<?= $status['llama'] === 'ok' ? 'success' : 'danger' ?>"><?= esc($status['llama']) ?></strong> &middot;
                        DB: <strong class="text-<?= $status['db_configured'] ? 'success' : 'danger' ?>"><?= $status['db_configured'] ? 'OK' : 'FAIL' ?></strong>
                    <?php else: ?>
                        <?= esc($status['error'] ?? 'Unreachable') ?>
                    <?php endif; ?>
                </small>
            </div>
            <?php if ($status['reachable']): ?>
                <a href="<?= base_url('admin/ml') ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3"><i class="fa-solid fa-rotate me-1"></i> Refresh</a>
            <?php endif; ?>
        </div>
    </div>
</div>
