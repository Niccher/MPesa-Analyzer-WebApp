<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Sync Activity - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .activity-feed {
        position: relative;
        padding: 1rem 0;
    }

    .activity-item {
        position: relative;
        padding-left: 100px;
        margin-bottom: 2.5rem;
    }

    .activity-date {
        position: absolute;
        left: 0;
        top: 0;
        width: 80px;
        text-align: right;
    }

    .activity-date .day {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
        color: var(--primary);
    }

    .activity-date .month {
        font-size: 0.75rem;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 1px;
        color: var(--text-muted);
    }

    .activity-line {
        position: absolute;
        left: 88px;
        top: 0;
        bottom: -2.5rem;
        width: 2px;
        background: var(--card-border);
    }

    .activity-item:last-child .activity-line {
        display: none;
    }

    .activity-marker {
        position: absolute;
        left: 82px;
        top: 8px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: var(--primary);
        border: 3px solid var(--bg-color);
        box-shadow: 0 0 0 4px rgba(93, 95, 239, 0.15);
        z-index: 2;
    }

    .sync-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 4px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
    }

    .sync-card:hover {
        transform: translateX(8px);
        box-shadow: 0 10px 30px rgba(93, 95, 239, 0.08);
        border-color: var(--primary);
    }

    .stat-pill {
        padding: 8px 16px;
        border-radius: 4px;
        background: var(--bg-color);
        border: 1px solid var(--card-border);
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.2s;
    }

    .sync-card:hover .stat-pill {
        border-color: var(--primary-subtle);
    }

    .stat-pill-lg {
        padding: 16px 24px;
        border: 2px solid var(--primary);
        background: rgba(93, 95, 239, 0.04);
    }

    .icon-box {
        width: 32px;
        height: 32px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    .icon-box-lg {
        width: 48px;
        height: 48px;
        font-size: 1.4rem;
    }

    .uuid-tag {
        font-family: 'JetBrains Mono', 'Courier New', monospace;
        font-size: 0.7rem;
        background: rgba(93, 95, 239, 0.05);
        color: var(--primary);
        padding: 2px 8px;
        border-radius: 4px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);">Sync Activity</h2>
        <p class="text-secondary mb-0">Detailed log of data imports from linked devices.</p>
    </div>
    <div>
        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
            <i class="fa-solid fa-clock-rotate-left me-1"></i>
            <?= count($history) ?> Sync Events
        </span>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (!empty($history)) : ?>
    <div class="activity-feed">
        <?php foreach ($history as $log) :
            $timestamp = strtotime($log->loot_Created);
            $day = date('d', $timestamp);
            $month = date('M', $timestamp);
            $time = date('h:i A', $timestamp);

            $catColors = [
                'Unclassified'    => ['bg' => 'bg-secondary-subtle', 'text' => 'text-secondary', 'icon' => 'fa-circle-question'],
                'Mobile Money'    => ['bg' => 'bg-success-subtle', 'text' => 'text-success', 'icon' => 'fa-mobile-screen'],
                'Payments/Govt'   => ['bg' => 'bg-primary-subtle', 'text' => 'text-primary', 'icon' => 'fa-building-columns'],
                'Bank Transfer'   => ['bg' => 'bg-info-subtle', 'text' => 'text-info', 'icon' => 'fa-university'],
                'Fintech'         => ['bg' => 'bg-warning-subtle', 'text' => 'text-warning', 'icon' => 'fa-bolt'],
                'Airtime'         => ['bg' => 'bg-danger-subtle', 'text' => 'text-danger', 'icon' => 'fa-phone'],
                'Notification'    => ['bg' => 'bg-dark-subtle', 'text' => 'text-dark', 'icon' => 'fa-bell'],
            ];
        ?>
            <div class="activity-item">
                <div class="activity-date">
                    <div class="day"><?= $day ?></div>
                    <div class="month"><?= $month ?></div>
                    <div class="small text-muted mt-1"><?= $time ?></div>
                </div>

                <div class="activity-line"></div>
                <div class="activity-marker"></div>

                <div class="card sync-card">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-box bg-primary-subtle text-primary">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Data Import Completed</h6>
                                    <span class="uuid-tag">UUID: <?= htmlspecialchars($log->loot_Uuid) ?></span>
                                </div>
                            </div>
                            <div class="text-end d-none d-md-block">
                                <span class="small text-muted me-2"><i class="fa-regular fa-clock me-1"></i><?= time_elapsed_string($log->loot_Created) ?></span>
                                <span class="badge bg-success-subtle text-success rounded-pill px-3">
                                    <i class="fa-solid fa-check-circle me-1"></i> Success
                                </span>
                            </div>
                        </div>

                        <!-- Summary Stats -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="stat-pill">
                                    <div class="icon-box icon-box-lg bg-dark text-white">
                                        <i class="fa-solid fa-message"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold" style="font-size: 1.4rem;"><?= number_format($log->total) ?></div>
                                        <div class="text-muted" style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase;">Total SMS</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-pill">
                                    <div class="icon-box icon-box-lg bg-success-subtle text-success">
                                        <i class="fa-solid fa-coins"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold" style="font-size: 1.4rem;">Ksh <?= number_format($log->total_amount, 2) ?></div>
                                        <div class="text-muted" style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase;">Total Amount</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-pill">
                                    <div class="icon-box icon-box-lg bg-info-subtle text-info">
                                        <i class="fa-solid fa-users"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold" style="font-size: 1.4rem;"><?= number_format($log->counterparties) ?></div>
                                        <div class="text-muted" style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase;">Counterparties</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-2 text-muted small">
                                    <i class="fa-solid fa-microchip"></i>
                                    <span>LLM Classification: <strong><?= $log->classified ?>/<?= $log->total ?></strong> classified</span>
                                    <span class="badge bg-<?= $log->classified === $log->total ? 'success' : ($log->classified > 0 ? 'warning' : 'secondary') ?>-subtle text-<?= $log->classified === $log->total ? 'success' : ($log->classified > 0 ? 'warning' : 'secondary') ?> rounded-pill">
                                        <?= $log->classified === $log->total ? 'Complete' : ($log->classified > 0 ? 'Partial' : 'Pending') ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-2 text-muted small">
                                    <i class="fa-solid fa-chart-simple"></i>
                                    <span>Transactions Analyzed: <strong><?= $log->analyzed_count ?></strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- LLM Category Breakdown -->
                        <?php $catList = $log->categories; ?>
                        <?php if (!empty($catList)) : ?>
                        <div class="row g-3">
                            <?php 
                            $sorted = $catList;
                            arsort($sorted);
                            // Show Unclassified last
                            $unclassifiedCount = 0;
                            if (isset($sorted['Unclassified'])) {
                                $unclassifiedCount = $sorted['Unclassified'];
                                unset($sorted['Unclassified']);
                            }
                            ?>
                            <?php foreach ($sorted as $catName => $catCount): 
                                $palette = $catColors[$catName] ?? ['bg' => 'bg-light', 'text' => 'text-dark', 'icon' => 'fa-tag'];
                            ?>
                            <div class="col-sm-6 col-md-4 col-lg-3">
                                <div class="stat-pill">
                                    <div class="icon-box <?= $palette['bg'] ?> <?= $palette['text'] ?>">
                                        <i class="fa-solid <?= $palette['icon'] ?>"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold mb-0" style="font-size: 1.1rem;"><?= number_format($catCount) ?></div>
                                        <div class="text-muted" style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase;"><?= htmlspecialchars($catName) ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>

                            <?php if ($unclassifiedCount > 0): 
                                $palette = $catColors['Unclassified'];
                            ?>
                            <div class="col-sm-6 col-md-4 col-lg-3">
                                <div class="stat-pill">
                                    <div class="icon-box <?= $palette['bg'] ?> <?= $palette['text'] ?>">
                                        <i class="fa-solid <?= $palette['icon'] ?>"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold mb-0" style="font-size: 1.1rem;"><?= number_format($unclassifiedCount) ?></div>
                                        <div class="text-muted" style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase;">Unclassified</div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div class="text-center text-muted small py-3">
                            <i class="fa-solid fa-microchip me-1"></i> Pending LLM classification...
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else : ?>
    <div class="card sync-card text-center p-5 border-0 shadow-sm">
        <div class="card-body">
            <div class="icon-box bg-light text-muted mx-auto mb-4" style="width: 80px; height: 80px; font-size: 2.5rem;">
                <i class="fa-solid fa-timeline"></i>
            </div>
            <h5 class="fw-bold">No sync history found</h5>
            <p class="text-secondary">Your device hasn't uploaded any data yet. Make sure your Android app is configured correctly.</p>
            <a href="<?= url_to('Info::index') ?>" class="btn btn-primary rounded-pill px-4 mt-2">
                <i class="fa-solid fa-gear me-2"></i> Configure App
            </a>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
