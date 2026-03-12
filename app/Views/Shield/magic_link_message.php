<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?><?= lang('Auth.useMagicLink') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>
    <div class="auth-header">
        <h1>Check Your Inbox</h1>
        <p>We've sent a magic link to your email.</p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <div style="font-size: 4rem; margin-bottom: 20px;">📧</div>
        <p style="color: var(--text-secondary); line-height: 1.6;">
            Click the link in the email to log in securely.<br>
            If you don't see it, be sure to check your spam folder.
        </p>
    </div>

    <div class="auth-footer" style="margin-top: 40px;">
        <a href="<?= url_to('login') ?>" class="btn-primary" style="display:inline-block; text-decoration:none;">Back to Login</a>
    </div>
<?= $this->endSection() ?>
