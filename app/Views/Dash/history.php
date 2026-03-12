<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Sync History - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .history-card {
        background: white;
        border-radius: var(--radius);
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        margin-top: 20px;
    }

    .timeline {
        position: relative;
        max-width: 800px;
        margin: 0 auto;
    }

    .timeline::after {
        content: '';
        position: absolute;
        width: 4px;
        background-color: #f0f0f0;
        top: 0;
        bottom: 0;
        left: 31px;
        margin-left: -2px;
        border-radius: 4px;
    }

    .timeline-item {
        padding: 15px 40px;
        position: relative;
        background-color: inherit;
        width: 100%;
        margin-bottom: 20px;
    }

    .timeline-item::after {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        left: 21px;
        background-color: white;
        border: 4px solid var(--primary);
        top: 25px;
        border-radius: 50%;
        z-index: 1;
        box-shadow: 0 0 0 4px rgba(93, 95, 239, 0.1);
    }

    .timeline-content {
        padding: 20px 25px;
        background-color: #f8f9fa;
        position: relative;
        border-radius: 12px;
        margin-left: 20px;
        border: 1px solid #eee;
        transition: transform 0.2s;
    }

    .timeline-content:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.05);
    }

    .time-label {
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 10px;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .sync-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }

    .stat-box {
        background: white;
        padding: 12px;
        border-radius: 8px;
        text-align: center;
        border: 1px solid #f0f0f0;
    }

    .stat-val {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 5px;
    }

    .stat-lbl {
        font-size: 0.75rem;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="header-section">
    <h2 style="font-weight: 700; color: var(--primary);">Data Sync History</h2>
    <p style="color: var(--text-light);">Timeline of data uploads from your Android application.</p>
</div>

<div class="history-card">
    <div class="timeline">
        <?php if (!empty($history)) : ?>
            <?php foreach ($history as $log) : ?>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="time-label">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <?= date('M d, Y - h:i A', strtotime($log->loot_Created)) ?>
                        </div>
                        <p style="color: var(--text-light); font-size: 0.9rem; margin: 0;">Device paired via UUID: <?= substr($log->loot_Uuid, 0, 8) ?>...</p>
                        
                        <div class="sync-stats">
                            <div class="stat-box" style="border-bottom: 3px solid var(--primary);">
                                <div class="stat-val"><?= $log->info_All ?></div>
                                <div class="stat-lbl">Total Processed</div>
                            </div>
                            <div class="stat-box" style="border-bottom: 3px solid #2ED573;">
                                <div class="stat-val"><?= $log->info_Get_from_MPESA ?></div>
                                <div class="stat-lbl">Received</div>
                            </div>
                            <div class="stat-box" style="border-bottom: 3px solid #3498DB;">
                                <div class="stat-val"><?= $log->info_Sent_to_MPESA ?></div>
                                <div class="stat-lbl">Sent</div>
                            </div>
                            <div class="stat-box" style="border-bottom: 3px solid #FF4757;">
                                <div class="stat-val"><?= $log->info_Unknown ?></div>
                                <div class="stat-lbl">Unknown / Error</div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div style="text-align: center; padding: 40px; color: #888;">
                <i class="fa-solid fa-timeline" style="font-size: 3rem; color: #ddd; margin-bottom: 15px;"></i>
                <p>No sync history recorded yet. Connect your Android app to start logging data.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
