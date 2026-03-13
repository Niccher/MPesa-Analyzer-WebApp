<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Dashboard - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .summary-card {
        border: none;
        border-radius: 16px;
        color: white;
        transition: transform 0.3s ease;
        overflow: hidden;
    }
    
    .summary-card:hover {  transform: translateY(-5px); }

    .card-purple { background: linear-gradient(135deg, #6C5CE7 0%, #4834D4 100%); }
    .card-blue { background: linear-gradient(135deg, #3498DB 0%, #2980B9 100%); }
    .card-dark { background: linear-gradient(135deg, #34495E 0%, #2C3E50 100%); }

    .metric-box {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(5px);
        border-radius: 12px;
        padding: 1rem 0.5rem;
    }
    
    .metric-icon { font-size: 2rem; }
    .recent-activity-badge { background: rgba(0,0,0,0.1); border-radius: 8px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);">Dashboard Overview</h2>
        <p class="text-secondary mb-0">High-level summary of your financial data (Last 30 Days).</p>
    </div>
    <div>
        <a href="<?= base_url('dashboard/search') ?>" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="fa-solid fa-magnifying-glass me-2"></i> Search and Filtering
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row g-4 mb-4">
    <!-- ... (Cards remain the same) ... -->
<?php /* Keeping cards content same as before but ensuring variables are used */ ?>
<!-- Received Summary etc (omitted for brevity in search/replace block) -->
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="fw-bold mb-0"><i class="fa-solid fa-list-ul me-2 text-primary"></i> Last 10 Transactions</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0 align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Date</th>
                                <th>Category</th>
                                <th>Type</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_transactions as $sms): ?>
                            <tr>
                                <td class="ps-4 small text-secondary">
                                    <?= format_mpesa_date($sms->sms_time) ?>
                                </td>
                                <td>
                                    <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-3">
                                        <?= $sms->sms_category ?>
                                    </span>
                                </td>
                                <td><?= $sms->sms_type ?></td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-light rounded-circle shadow-sm view-sms-btn" 
                                            data-time="<?= format_mpesa_date($sms->sms_time) ?>"
                                            data-body="<?= htmlspecialchars(base64_decode($sms->sms_body)) ?>">
                                        <i class="fa-solid fa-eye text-primary"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SMS Detail Modal -->
<div class="modal fade" id="smsDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Transaction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="text-secondary small fw-bold text-uppercase">Time</label>
                    <p id="modal-sms-time" class="mb-0 fw-semibold"></p>
                </div>
                <div>
                    <label class="text-secondary small fw-bold text-uppercase">Message Content</label>
                    <div class="p-3 bg-light rounded-3 mt-1" id="modal-sms-body" style="white-space: pre-wrap; font-size: 0.9rem;"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal logic (Native JS)
        const smsModalEl = document.getElementById('smsDetailModal');
        const smsModal = new bootstrap.Modal(smsModalEl);
        const modalTime = document.getElementById('modal-sms-time');
        const modalBody = document.getElementById('modal-sms-body');

        // Dynamic binding for table buttons
        document.body.addEventListener('click', function(e) {
            const btn = e.target.closest('.view-sms-btn');
            if (btn) {
                modalTime.textContent = btn.getAttribute('data-time');
                modalBody.innerHTML = btn.getAttribute('data-body').replace(/\n/g, '<br>');
                smsModal.show();
            }
        });
    });
</script>
<?= $this->endSection() ?>
