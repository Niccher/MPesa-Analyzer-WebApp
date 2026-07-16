<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?><?= lang('Auth.register') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>
    <div class="auth-header">
        <h1>Create Account</h1>
        <p>Join the financial analysis community</p>
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

    <form action="<?= url_to('register') ?>" method="post">
        <?= csrf_field() ?>

        <!-- Email -->
        <div class="form-group">
            <label for="floatingEmailInput" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="floatingEmailInput" name="email" inputmode="email" autocomplete="email" placeholder="you@domain.com" value="<?= old('email') ?>" required>
        </div>

        <!-- Username -->
        <div class="form-group">
            <label for="floatingUsernameInput" class="form-label">Username</label>
            <input type="text" class="form-control" id="floatingUsernameInput" name="username" inputmode="text" autocomplete="username" placeholder="your_username" value="<?= old('username') ?>" required>
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="floatingPasswordInput" class="form-label">Password</label>
            <input type="password" class="form-control" id="floatingPasswordInput" name="password" inputmode="text" autocomplete="new-password" placeholder="••••••••" required>
        </div>

        <!-- Password (Again) -->
        <div class="form-group">
            <label for="floatingPasswordConfirmInput" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="floatingPasswordConfirmInput" name="password_confirm" inputmode="text" autocomplete="new-password" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-primary">Sign Up</button>
    </form>

    <div class="auth-footer">
        <p>Already have an account? <a href="<?= url_to('login') ?>">Sign In</a></p>
    </div>
<?= $this->endSection() ?>
