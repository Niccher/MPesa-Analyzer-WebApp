<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> API Tokens & Auth - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .info-card {
        border: none;
        border-radius: 4px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
    .card-accent {
        height: 4px;
        width: 100%;
        background: linear-gradient(90deg, var(--primary), rgba(93,95,239,0.25));
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
    }
    .setting-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px 0;
        border-bottom: 1px solid #f5f5f5;
    }
    .setting-item:last-child {
        border-bottom: none;
    }
    .item-icon {
        width: 36px;
        height: 36px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-key me-2"></i>API Tokens &amp; Sync</h2>
        <p class="text-secondary mb-0">Generate, view, and revoke mobile access tokens to sync your SMS data safely.</p>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primary btn-sm rounded-pill px-3" href="<?= base_url('dashboard/devices/tokens') ?>">
            <i class="fa-solid fa-key me-1"></i> Show all Tokens
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?= $this->include('Layouts/_control_center_nav', ['activeTab' => 'tokens']) ?>

<?php if (session()->has('new_token')) : ?>
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <h5 class="alert-heading fw-bold"><i class="fa-solid fa-circle-check"></i> New API Token Generated!</h5>
        <p>Please copy this token now. For your security, <strong>it will not be shown again</strong>.</p>
        <hr>
        <div class="d-flex justify-content-center mb-3">
            <div id="qrcode" class="bg-white p-2 rounded"></div>
        </div>
        <div class="p-3 bg-light border text-center font-monospace rounded mb-2 user-select-all" id="rawTokenValue" style="word-break:break-all; font-size:1.1rem; letter-spacing:1px;"><?= session('new_token') ?></div>
        <p class="mb-0 small text-muted text-center">Scan this QR code with the Android App or paste the token directly inside the device linkage preferences page.</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var token = document.getElementById("rawTokenValue").innerText;
            new QRCode(document.getElementById("qrcode"), {
                text: token,
                width: 200,
                height: 200
            });
        });
    </script>
<?php endif; ?>

<?php if (session()->has('message')) : ?>
    <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
        <?= session('message') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Left Column: API Tokens management & linkage guide -->
    <div class="col-lg-6">
        <!-- API Security Management -->
        <div class="card info-card mb-4">
            <div class="card-accent"></div>
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="fs-4 text-primary"><i class="fa-solid fa-shield-halved"></i></span>
                    <h5 class="card-title fw-bold mb-0" style="color: var(--primary);">Mobile App Authentication</h5>
                </div>
                
                <div class="setting-item border-0 pb-0">
                    <span class="item-icon bg-primary-subtle text-primary"><i class="fa-solid fa-key"></i></span>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark">Mobile Application Token</span>
                            <?php if (!empty($tokens)): ?>
                                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i>Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1"><i class="fa-solid fa-circle-xmark me-1"></i>No Token</span>
                            <?php endif; ?>
                        </div>
                        <p class="small text-muted mb-0">
                            Use a system-generated cryptographic token to authenticate your Android app instead of exposing your login password.
                        </p>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4 pt-3 border-top" style="border-color: rgba(0,0,0,0.05) !important;">
                    <form action="<?= url_to('Info::generateToken') ?>" method="POST" class="d-inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold"><i class="fa-solid fa-arrows-rotate me-1"></i> Generate Token</button>
                    </form>
                    
                    <?php if (!empty($tokens)): ?>
                        <form action="<?= url_to('Info::revokeToken') ?>" method="POST" id="revokeTokenForm" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="button" id="revokeTokenBtn" class="btn btn-outline-danger btn-sm rounded-pill px-4 fw-bold"><i class="fa-solid fa-ban me-1"></i> Revoke Token</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Android Sync Helper Guide -->
        <div class="card info-card">
            <div class="card-accent"></div>
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="fs-4 text-primary"><i class="fa-solid fa-circle-info"></i></span>
                    <h5 class="card-title fw-bold mb-0" style="color: var(--primary);">How to link your device</h5>
                </div>
                <ol class="small text-secondary ps-3 mb-0">
                    <li class="mb-2">Download the companion Android SMS Parser application.</li>
                    <li class="mb-2">Click the <strong>Generate Token</strong> button above.</li>
                    <li class="mb-2">Open the app on your phone, click <strong>Link Device</strong>, and scan the QR code (or paste the raw key).</li>
                    <li>Verify the connection status on the <strong>Linked Devices</strong> tab.</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Right Column: Stats & Data Definitions -->
    <div class="col-lg-6">
        <!-- Application Data Insights -->
        <div class="card info-card mb-4">
            <div class="card-accent"></div>
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="fs-4 text-primary"><i class="fa-solid fa-server"></i></span>
                    <h5 class="card-title fw-bold mb-0" style="color: var(--primary);">Database &amp; Sync Insights</h5>
                </div>
                
                <div class="setting-item">
                    <span class="item-icon bg-primary-subtle text-primary"><i class="fa-solid fa-database"></i></span>
                    <div class="flex-grow-1">
                        <div class="fw-bold text-dark">Total Tracked Messages</div>
                        <small class="text-muted">Raw SMS messages parsed</small>
                    </div>
                    <span class="badge bg-primary rounded-pill px-3 py-1 fs-6"><?= number_format($total_processed) ?></span>
                </div>
                
                <div class="setting-item">
                    <span class="item-icon bg-info-subtle text-info"><i class="fa-solid fa-mobile-screen-button"></i></span>
                    <div class="flex-grow-1">
                        <div class="fw-bold text-dark">Active Devices</div>
                        <small class="text-muted">Companion phones logging data</small>
                    </div>
                    <span class="badge bg-info rounded-pill px-3 py-1 fs-6 text-white"><?= $active_devices_count ?></span>
                </div>
                
                <div class="setting-item">
                    <span class="item-icon bg-success-subtle text-success"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                    <div class="flex-grow-1">
                        <div class="fw-bold text-dark">Latest Sync Activity</div>
                        <small class="text-secondary d-block mt-1">
                            <?php if ($last_upload): ?>
                                UUID: <span class="font-monospace text-primary fw-semibold"><?= htmlspecialchars($last_upload->loot_Uuid) ?></span>
                            <?php else: ?>
                                Awaiting connection
                            <?php endif; ?>
                        </small>
                    </div>
                    <span class="text-dark fw-bold text-end">
                        <?php if ($last_upload): ?>
                            <span class="badge bg-success text-white rounded-pill px-2 py-1"><i class="fa-regular fa-clock me-1"></i><?= date('M d, Y', strtotime($last_upload->loot_Created)) ?></span>
                            <span class="text-muted d-block text-center mt-1" style="font-size:0.75rem;"><?= date('h:i A', strtotime($last_upload->loot_Created)) ?></span>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    function submitForm(formId) {
        document.getElementById(formId).submit();
    }

    // Revoke API token
    const revokeTokenBtn = document.getElementById('revokeTokenBtn');
    if (revokeTokenBtn) {
        revokeTokenBtn.addEventListener('click', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Revoke API token?',
                text: 'Your mobile app will lose connection immediately. You will need to link it again.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, revoke it',
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (result.isConfirmed) submitForm('revokeTokenForm');
            });
        });
    }
});
</script>
<?= $this->endSection() ?>
