<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?>Forgot Password <?= $this->endSection() ?>

<?= $this->section('main') ?>
    <div class="auth-header">
        <div class="brand-icon"><i class="fa-solid fa-key"></i></div>
        <h1>Forgot Password</h1>
        <p>Enter your email to receive a login link</p>
    </div>

    <?php if (session('error')) : ?>
        <div class="alert alert-error"><?= session('error') ?></div>
    <?php elseif (session('errors')) : ?>
        <div class="alert alert-error">
            <?php foreach (session('errors') as $error) : ?>
                <?= $error ?><br>
            <?php endforeach ?>
        </div>
    <?php endif ?>

    <form action="<?= url_to('magic-link') ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="floatingEmailInput" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="floatingEmailInput" name="email" autocomplete="email" placeholder="you@domain.com" value="<?= old('email', auth()->user()->email ?? '') ?>" required>
        </div>

        <button type="submit" class="btn-primary">
            <i class="fa-solid fa-paper-plane me-2"></i>Send Magic Link
        </button>
    </form>

    <div class="auth-footer">
        <p class="mb-1">Remember your password? <a href="<?= url_to('login') ?>">Sign In</a></p>
    </div>
<?= $this->endSection() ?>
