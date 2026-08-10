<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Info & Settings - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .info-card {
        border: none;
        border-radius: 4px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
    
    .setting-item {
        padding: 1rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .setting-item:last-child {
        border-bottom: none;
    }
    
    .token-box {
        background: var(--bg-color);
        border: 1px solid var(--card-border);
        border-radius: 4px;
        padding: 15px;
        font-family: monospace;
        word-break: break-all;
        font-size: 1.1rem;
        color: var(--primary);
        opacity: 0.8;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);">System Information</h2>
        <p class="text-secondary mb-0">Manage your account, view device statuses, and configure API access.</p>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (session()->has('new_token')) : ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <h5 class="alert-heading"><i class="fa-solid fa-circle-check"></i> New API Token Generated!</h5>
        <p>Please copy this token now. For your security, <strong>it will not be shown again</strong>.</p>
        <hr>
        <div class="d-flex justify-content-center mb-3">
            <div id="qrcode" class="bg-white p-2 rounded"></div>
        </div>
        <div class="token-box mb-2 user-select-all" id="rawTokenValue"><?= session('new_token') ?></div>
        <p class="mb-0 small text-muted">Scan the QR code or paste this token into the Link Device page of your Android application.</p>
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
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <?= session('message') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- User Profile Card -->
    <div class="col-lg-6">
        <div class="card info-card h-100">
            <div class="card-body p-4">
                <h5 class="card-title fw-bold mb-4" style="color: var(--primary);"><i class="fa-solid fa-user me-2"></i> Account Profile</h5>
                
                <div class="setting-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-dark">Username</div>
                        <small class="text-muted"><?= auth()->user()->username ?? '—' ?></small>
                    </div>
                </div>
                
                <div class="setting-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-dark">Email Address</div>
                        <small class="text-muted"><?= auth()->user()->email ?? '—' ?></small>
                    </div>
                </div>
                
                <div class="setting-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-dark">Phone Sync Validation</div>
                        <small class="text-muted">Status of active connection to your mobile device</small>
                    </div>
                    <?php if ($last_upload): ?>
                        <span class="badge bg-success rounded-pill px-3 py-2">Connected Validate</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2">Awaiting Sync</span>
                    <?php endif; ?>
                </div>

                <div class="setting-item mt-3 pt-3 border-top border-warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold text-warning text-dark">Delete My Data</div>
                            <small class="text-muted">Delete all your uploaded SMS data and summaries, but keep your account.</small>
                        </div>
                        <form action="<?= base_url('process/delete_data') ?>" method="POST" id="deleteDataForm">
                            <?= csrf_field() ?>
                            <button type="button" id="deleteDataBtn" class="btn btn-outline-warning btn-sm px-3 fw-bold text-dark"><i class="fa-solid fa-eraser me-1"></i> Delete Data</button>
                        </form>
                    </div>
                </div>

                <div class="setting-item mt-3 pt-3 border-top border-danger">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold text-danger">Delete Account</div>
                            <small class="text-muted">Permanently remove your account and all data.</small>
                        </div>
                        <form action="<?= base_url('process/delete_account') ?>" method="POST" id="deleteAccountForm">
                            <?= csrf_field() ?>
                            <button type="button" id="deleteAccountBtn" class="btn btn-outline-danger btn-sm px-3 fw-bold"><i class="fa-solid fa-trash-can me-1"></i> Delete Account</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- API Security Management -->
    <div class="col-lg-6">
        <div class="card info-card h-100">
            <div class="card-body p-4">
                <h5 class="card-title fw-bold mb-4" style="color: var(--primary);"><i class="fa-solid fa-shield-halved me-2"></i> API Security</h5>
                
                <div class="setting-item">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-bold text-dark">Mobile Application Token</div>
                        <?php if (!empty($tokens)): ?>
                            <span class="badge bg-success">Valid & Active</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Null / No Token</span>
                        <?php endif; ?>
                    </div>
                    <p class="small text-muted mb-3">
                        Use a web-generated token to securely authenticate your Android app instead of a password.
                    </p>
                    
                    <div class="d-flex gap-2">
                        <form action="<?= url_to('Info::generateToken') ?>" method="POST" style="display:inline;">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold"><i class="fa-solid fa-arrows-rotate me-1"></i> Generate New Token</button>
                        </form>
                        
                        <?php if (!empty($tokens)): ?>
                            <form action="<?= url_to('Info::revokeToken') ?>" method="POST" id="revokeTokenForm" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="button" id="revokeTokenBtn" class="btn btn-outline-danger btn-sm px-3 fw-bold"><i class="fa-solid fa-ban me-1"></i> Nullify Existing</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Stats Card -->
    <div class="col-lg-6">
        <div class="card info-card h-100">
            <div class="card-body p-4">
                <h5 class="card-title fw-bold mb-4" style="color: var(--primary);"><i class="fa-solid fa-server me-2"></i> Application Data Insights</h5>
                
                <div class="setting-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-dark">Total Tracked Records</div>
                        <small class="text-muted">Raw SMS messages safely parsed</small>
                    </div>
                    <span class="badge bg-primary rounded-pill px-3 py-2 fs-6"><?= number_format($total_processed) ?></span>
                </div>
                
                <div class="setting-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-dark">Active User Devices</div>
                        <small class="text-muted">Android phones logging data</small>
                    </div>
                    <span class="badge bg-info rounded-pill px-3 py-2 fs-6"><?= $active_devices_count ?></span>
                </div>
                
                <div class="setting-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-dark">Latest Device Data Upload</div>
                        <small class="text-muted">
                            <?php if ($last_upload): ?>
                                Phone UUID: <?= substr($last_upload->loot_Uuid, 0, 8) ?>...
                            <?php else: ?>
                                No phone logged in yet
                            <?php endif; ?>
                        </small>
                    </div>
                    <span class="text-dark fw-bold text-end">
                        <?php if ($last_upload): ?>
                            <?= date('M d, Y', strtotime($last_upload->loot_Created)) ?><br>
                            <small class="text-muted"><?= date('h:i A', strtotime($last_upload->loot_Created)) ?></small>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Dictionary Card -->
    <div class="col-lg-6">
        <div class="card info-card h-100">
            <div class="card-body p-4">
                <h5 class="card-title fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-book me-2"></i> Category Legend</h5>
                <p class="text-muted small mb-4">A quick reference guide for how the system categorizes your transactions.</p>
                
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded-3 border-start border-4 border-success h-100">
                            <strong class="d-block mb-1 text-dark">Receive</strong>
                            <small class="text-muted">Money sent directly to your phone by another user.</small>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded-3 border-start border-4 border-primary h-100">
                            <strong class="d-block mb-1 text-dark">Sent To LNM</strong>
                            <small class="text-muted">Lipa Na M-Pesa (Buy Goods or Paybill).</small>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded-3 border-start border-4 border-warning h-100">
                            <strong class="d-block mb-1 text-dark">Withdraw</strong>
                            <small class="text-muted">Cash extracted from an M-Pesa Agent.</small>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded-3 border-start border-4 border-danger h-100">
                            <strong class="d-block mb-1 text-dark">Error / Failed</strong>
                            <small class="text-muted">Transactions that did not go through (wrong PIN, insufficient funds).</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    function submitForm(id) {
        const form = document.getElementById(id);
        if (form) form.submit();
    }

    // Delete My Data — warning, not undoable.
    const deleteDataBtn = document.getElementById('deleteDataBtn');
    if (deleteDataBtn) {
        deleteDataBtn.addEventListener('click', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Delete all your data?',
                html: 'This will permanently delete <strong>all</strong> your uploaded SMS data, summaries, tags and analysis results.<br><br>'
                    + '<span style="color: #dc3545; font-weight: 600;">This action cannot be undone.</span><br><br>'
                    + 'Your account will remain active.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete my data',
                cancelButtonText: 'Cancel',
                allowOutsideClick: false,
                focusCancel: true,
            }).then(function (result) {
                if (result.isConfirmed) submitForm('deleteDataForm');
            });
        });
    }

    // Delete Account — requires typing DELETE to proceed.
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    if (deleteAccountBtn) {
        deleteAccountBtn.addEventListener('click', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Delete your account permanently?',
                html: 'This will permanently remove your account, connected devices and <strong>all</strong> your data.<br><br>'
                    + '<span style="color: #dc3545; font-weight: 600;">This action cannot be undone.</span><br><br>'
                    + 'To proceed, type <strong>DELETE</strong> below.',
                icon: 'warning',
                input: 'text',
                inputPlaceholder: 'Type DELETE to confirm',
                inputAttributes: { autocapitalize: 'off', autocorrect: 'off', autocomplete: 'off' },
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Delete my account',
                cancelButtonText: 'Cancel',
                allowOutsideClick: false,
                inputValidator: function (value) {
                    if (value !== 'DELETE') {
                        return 'Please type DELETE to confirm';
                    }
                },
            }).then(function (result) {
                if (result.isConfirmed) submitForm('deleteAccountForm');
            });
        });
    }

    // Nullify API token — warning, logs the Android app out.
    const revokeTokenBtn = document.getElementById('revokeTokenBtn');
    if (revokeTokenBtn) {
        revokeTokenBtn.addEventListener('click', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Revoke API token?',
                text: 'Your Android app will immediately be logged out. You can generate a new token afterwards.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, revoke it',
                cancelButtonText: 'Cancel',
                allowOutsideClick: false,
                focusCancel: true,
            }).then(function (result) {
                if (result.isConfirmed) submitForm('revokeTokenForm');
            });
        });
    }
});
</script>
<?= $this->endSection() ?>
