<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?><?= lang('Auth.login') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>
    <div class="auth-header">
        <h1>Sign In</h1>
        <p>Access your analytics dashboard</p>
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

    <?php if (session('message')) : ?>
        <div class="alert alert-success"><?= session('message') ?></div>
    <?php endif ?>

    <form action="<?= url_to('login') ?>" method="post">
        <?= csrf_field() ?>

        <!-- Email -->
        <div class="form-group">
            <label for="floatingEmailInput" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="floatingEmailInput" name="email" inputmode="email" autocomplete="email" placeholder="you@domain.com" value="<?= old('email') ?>" required>
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="floatingPasswordInput" class="form-label">Password</label>
            <input type="password" class="form-control" id="floatingPasswordInput" name="password" inputmode="text" autocomplete="current-password" placeholder="••••••••" required>
        </div>

        <!-- Remember me -->
        <?php if (setting('Auth.sessionConfig')['allowRemembering']): ?>
            <div class="form-group" style="display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" name="remember" class="form-check-input" <?php if (old('remember')): ?> checked <?php endif ?>>
                <label class="form-label" style="margin-bottom: 0;">Remember me</label>
            </div>
        <?php endif; ?>

        <button type="submit" class="btn-primary">Sign In</button>
    </form>

    <div class="auth-footer">
        <p>Don't have an account? <a href="<?= url_to('register') ?>">Sign Up</a></p>
        <?php if (setting('Auth.allowMagicLinkLogins')) : ?>
            <p><a href="<?= url_to('magic-link') ?>">Forgot password?</a></p>
        <?php endif ?>
    </div>
<?= $this->endSection() ?>
