<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Profile Settings - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-user me-2"></i>Profile Settings</h2>
        <p class="text-secondary mb-0">Update your account details and password.</p>
    </div>
    <a href="<?= base_url('dashboard/settings') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Settings
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (session()->has('message')) : ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= session('message') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (session()->has('error')) : ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= session('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card settings-card h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4" style="color: var(--primary);">Account Details</h5>
                <form action="<?= base_url('dashboard/settings/profile/save') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username</label>
                        <input type="text" class="form-control" name="username"
                               value="<?= old('username', $user->username ?? '') ?>"
                               placeholder="Your display name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" class="form-control" name="email"
                               value="<?= old('email', $user->email ?? '') ?>"
                               placeholder="your@email.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Member Since</label>
                        <p class="text-muted mb-0"><?= $user->created_at ?? 'N/A' ?></p>
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card settings-card h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4" style="color: var(--primary);"><i class="fa-solid fa-lock me-2"></i>Change Password</h5>
                <form action="<?= base_url('dashboard/settings/profile/save') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Current Password</label>
                        <input type="password" class="form-control" name="current_password" placeholder="Enter current password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password</label>
                        <input type="password" class="form-control" name="new_password" placeholder="At least 8 characters" minlength="8">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Confirm New Password</label>
                        <input type="password" class="form-control" name="confirm_password" placeholder="Repeat new password">
                    </div>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-semibold">
                        <i class="fa-solid fa-key me-1"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
