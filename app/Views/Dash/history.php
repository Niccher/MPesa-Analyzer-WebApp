<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Sync History - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .timeline {
        position: relative;
        padding-left: 3rem;
        margin-top: 2rem;
    }
    .timeline::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 14px;
        width: 4px;
        background: #e9ecef;
        border-radius: 4px;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
    }
    .timeline-marker {
        position: absolute;
        left: -3rem;
        top: 0.25rem;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: white;
        border: 4px solid var(--primary);
        box-shadow: 0 0 0 4px rgba(93, 95, 239, 0.1);
        z-index: 1;
    }
    .history-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
    .stat-box {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);">Data Sync History</h2>
        <p class="text-secondary mb-0">Timeline of data uploads from your Android application.</p>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="card history-card mb-4">
    <div class="card-body p-4 p-md-5">
        
        <?php if (!empty($history)) : ?>
            <div class="timeline">
                <?php foreach ($history as $log) : ?>
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="card bg-light border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h5 class="fw-bold text-primary mb-1">
                                    <i class="fa-solid fa-cloud-arrow-up me-2"></i>
                                    <?= date('M d, Y - h:i A', strtotime($log->loot_Created)) ?>
                                </h5>
                                <p class="text-muted small mb-3">Device Upload UUID: <?= substr($log->loot_Uuid, 0, 8) ?>...</p>
                                
                                <div class="row g-3 text-center">
                                    <div class="col-6 col-md-3">
                                        <div class="stat-box border-primary border-bottom border-3">
                                            <h4 class="fw-bold mb-1"><?= $log->info_All ?></h4>
                                            <small class="text-uppercase text-muted fw-bold" style="font-size:0.7rem;">Processed</small>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="stat-box border-success border-bottom border-3">
                                            <h4 class="fw-bold mb-1"><?= $log->info_Get_from_MPESA ?></h4>
                                            <small class="text-uppercase text-muted fw-bold" style="font-size:0.7rem;">Received</small>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="stat-box border-info border-bottom border-3">
                                            <h4 class="fw-bold mb-1"><?= $log->info_Sent_to_MPESA ?></h4>
                                            <small class="text-uppercase text-muted fw-bold" style="font-size:0.7rem;">Sent</small>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="stat-box border-danger border-bottom border-3">
                                            <h4 class="fw-bold mb-1"><?= $log->info_Unknown ?></h4>
                                            <small class="text-uppercase text-muted fw-bold" style="font-size:0.7rem;">Errors / Unknown</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="text-center p-5 text-muted">
                <i class="fa-solid fa-timeline mb-3" style="font-size: 3rem; color: #ddd;"></i>
                <h5>No sync history recorded yet.</h5>
                <p>Connect your Android app to start logging data.</p>
            </div>
        <?php endif; ?>
        
    </div>
</div>
<?= $this->endSection() ?>
