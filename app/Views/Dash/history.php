<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Upload History - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .cat-badge {
        font-size: 0.65rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 4px;
        white-space: nowrap;
        display: inline-block;
    }
    .classify-bar {
        height: 6px;
        border-radius: 3px;
        background: #e9ecef;
        overflow: hidden;
        min-width: 80px;
    }
    .classify-bar-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.5s ease;
    }
    .summary-stat {
        padding: 16px 20px;
        border-radius: 4px;
        background: var(--card-bg);
        border: 1px solid var(--card-border);
    }
    .history-table th {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        font-weight: 600;
        border-bottom: 2px solid var(--card-border);
        padding: 12px 8px;
        white-space: nowrap;
    }
    .history-table td {
        padding: 14px 8px;
        vertical-align: middle;
        border-bottom: 1px solid var(--card-border);
        font-size: 0.9rem;
    }
    .history-table tr:hover td {
        background: rgba(93, 95, 239, 0.02);
    }
    .batch-uuid {
        font-family: 'JetBrains Mono', 'Courier New', monospace;
        font-size: 0.65rem;
        color: var(--text-muted);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);">Upload History</h2>
        <p class="text-secondary mb-0">All data imports from your linked devices, with LLM classification status.</p>
    </div>
    <div>
        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
            <i class="fa-solid fa-layer-group me-1"></i>
            <?= count($batches) ?> Batches
        </span>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php $cs = currency_symbol(); ?>

<?php if (!empty($batches)) : ?>

<!-- Summary Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="summary-stat text-center">
            <div class="fw-bold fs-4"><?= number_format($total_sms_all) ?></div>
            <div class="text-muted small text-uppercase" style="font-weight: 600; letter-spacing: 0.5px;">Total SMS</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="summary-stat text-center">
            <div class="fw-bold fs-4 text-success"><?= number_format($total_classified_all) ?></div>
            <div class="text-muted small text-uppercase" style="font-weight: 600; letter-spacing: 0.5px;">Classified</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="summary-stat text-center">
            <div class="fw-bold fs-4 text-primary"><?= $cs ?> <?= number_format($total_amount_all, 0) ?></div>
            <div class="text-muted small text-uppercase" style="font-weight: 600; letter-spacing: 0.5px;">Total Value</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="summary-stat text-center">
            <div class="fw-bold fs-4"><?= $total_sms_all > 0 ? round(($total_classified_all / $total_sms_all) * 100) : 0 ?>%</div>
            <div class="text-muted small text-uppercase" style="font-weight: 600; letter-spacing: 0.5px;">LLM Coverage</div>
        </div>
    </div>
</div>

<!-- Batches Table -->
<div class="card glass-card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table history-table mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th style="min-width: 100px;">SMS</th>
                        <th style="min-width: 120px;">Classification</th>
                        <th>Categories</th>
                        <th>Amount</th>
                        <th>Parties</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $catPalette = [
                        'Unclassified'    => ['bg' => '#e9ecef', 'text' => '#6c757d'],
                        'Mobile Money'    => ['bg' => '#d1e7dd', 'text' => '#0f5132'],
                        'Payments/Govt'   => ['bg' => '#cfe2ff', 'text' => '#084298'],
                        'Bank Transfer'   => ['bg' => '#cff4fc', 'text' => '#055160'],
                        'Fintech'         => ['bg' => '#fff3cd', 'text' => '#664d03'],
                        'Airtime'         => ['bg' => '#f8d7da', 'text' => '#842029'],
                        'Notification'    => ['bg' => '#e2e3e5', 'text' => '#41464b'],
                        'Salary'          => ['bg' => '#d1e7dd', 'text' => '#0f5132'],
                        'Shopping'        => ['bg' => '#f8d7da', 'text' => '#842029'],
                        'Food & Drink'    => ['bg' => '#fff3cd', 'text' => '#664d03'],
                        'Transport'         => ['bg' => '#cff4fc', 'text' => '#055160'],
                        'Utilities'       => ['bg' => '#e2e3e5', 'text' => '#41464b'],
                        'Entertainment'   => ['bg' => '#f0d6ff', 'text' => '#5a189a'],
                    ];
                    ?>
                    <?php foreach ($batches as $b) : 
                        $ts = strtotime($b->created_at);
                        $dateStr = $ts ? date('M j, Y', $ts) : 'N/A';
                        $timeStr = $ts ? date('h:i A', $ts) : '';
                        $classifyPct = $b->total > 0 ? round(($b->classified / $b->total) * 100) : 0;
                        $barColor = $classifyPct >= 100 ? '#198754' : ($classifyPct > 0 ? '#ffc107' : '#e9ecef');
                    ?>
                    <tr>
                        <td class="text-nowrap">
                            <div class="fw-semibold"><?= $dateStr ?></div>
                            <div class="text-muted small"><?= $timeStr ?></div>
                        </td>
                        <td>
                            <div class="fw-semibold"><?= number_format($b->total) ?></div>
                            <div class="batch-uuid"><?= htmlspecialchars(substr($b->uuid, 0, 12)) ?>…</div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="classify-bar flex-grow-1">
                                    <div class="classify-bar-fill" style="width: <?= $classifyPct ?>%; background: <?= $barColor ?>;"></div>
                                </div>
                                <span class="small fw-semibold text-nowrap" style="font-size: 0.75rem;">
                                    <?= $b->classified ?>/<?= $b->total ?>
                                </span>
                            </div>
                            <?php if ($classifyPct > 0 && $classifyPct < 100) : ?>
                                <div class="small text-warning mt-1" style="font-size: 0.65rem;">
                                    <i class="fa-solid fa-microchip me-1"></i>LLM running…
                                </div>
                            <?php elseif ($classifyPct >= 100) : ?>
                                <div class="small text-success mt-1" style="font-size: 0.65rem;">
                                    <i class="fa-solid fa-check-circle me-1"></i>Complete
                                </div>
                            <?php else : ?>
                                <div class="small text-muted mt-1" style="font-size: 0.65rem;">
                                    <i class="fa-solid fa-clock me-1"></i>Pending
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $cats = $b->categories;
                            arsort($cats);
                            $shown = 0;
                            foreach ($cats as $name => $count) : 
                                if ($shown >= 3) break;
                                $pal = $catPalette[$name] ?? ['bg' => '#f8f9fa', 'text' => '#212529'];
                                $shown++;
                            ?>
                                <span class="cat-badge me-1 mb-1" style="background: <?= $pal['bg'] ?>; color: <?= $pal['text'] ?>;">
                                    <?= $name ?> <?= $count ?>
                                </span>
                            <?php endforeach; ?>
                            <?php if (count($cats) > 3) : ?>
                                <span class="cat-badge" style="background: #e9ecef; color: #6c757d;">+<?= count($cats) - 3 ?> more</span>
                            <?php endif; ?>
                        </td>
                        <td class="fw-semibold text-nowrap">
                            <?= $cs ?> <?= number_format($b->total_amount, 2) ?>
                        </td>
                        <td class="text-nowrap">
                            <?= number_format($b->counterparties) ?>
                        </td>
                        <td>
                            <a href="<?= base_url('dashboard/transactions') ?>"
                               class="btn btn-sm btn-outline-primary rounded-pill px-3"
                               title="View all transactions">
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php else : ?>
<div class="card glass-card text-center p-5 border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-center mx-auto mb-4"
             style="width: 80px; height: 80px; border-radius: 4px; background: var(--bg-color);">
            <i class="fa-solid fa-timeline fs-1 text-muted"></i>
        </div>
        <h5 class="fw-bold">No upload history yet</h5>
        <p class="text-secondary">Your device hasn't uploaded any data. Make sure your Android app is configured and linked.</p>
        <a href="<?= url_to('Info::index') ?>" class="btn btn-primary rounded-pill px-4 mt-2">
            <i class="fa-solid fa-gear me-2"></i> Configure App
        </a>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
