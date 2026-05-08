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
        border-radius: 20px;
        backdrop-filter: blur(10px);
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
        border-radius: 12px;
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

    .icon-box {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
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
                                <span class="badge bg-success-subtle text-success rounded-pill px-3">
                                    <i class="fa-solid fa-check-circle me-1"></i> Success
                                </span>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6 col-md-3">
                                <div class="stat-pill">
                                    <div class="icon-box bg-dark-subtle text-dark">
                                        <i class="fa-solid fa-list-ul"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold mb-0" style="font-size: 1.1rem;"><?= number_format($log->info_All) ?></div>
                                        <div class="text-muted" style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase;">Total</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="stat-pill">
                                    <div class="icon-box bg-success-subtle text-success">
                                        <i class="fa-solid fa-arrow-down"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold mb-0" style="font-size: 1.1rem;"><?= number_format($log->info_Get_from_MPESA) ?></div>
                                        <div class="text-muted" style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase;">Inflow</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="stat-pill">
                                    <div class="icon-box bg-primary-subtle text-primary">
                                        <i class="fa-solid fa-arrow-up"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold mb-0" style="font-size: 1.1rem;"><?= number_format($log->info_Sent_to_MPESA) ?></div>
                                        <div class="text-muted" style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase;">Outflow</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="stat-pill">
                                    <div class="icon-box bg-warning-subtle text-warning">
                                        <i class="fa-solid fa-circle-question"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold mb-0" style="font-size: 1.1rem;"><?= number_format($log->info_Unknown) ?></div>
                                        <div class="text-muted" style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase;">Other</div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
