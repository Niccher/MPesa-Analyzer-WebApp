<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><a class="nav-link <?= ($active ?? '') === 'status' ? 'active' : '' ?>" href="<?= base_url('admin/ml') ?>">Status</a></li>
    <li class="nav-item"><a class="nav-link <?= ($active ?? '') === 'models' ? 'active' : '' ?>" href="<?= base_url('admin/ml/models') ?>">Models</a></li>
    <li class="nav-item"><a class="nav-link <?= ($active ?? '') === 'config' ? 'active' : '' ?>" href="<?= base_url('admin/ml/config') ?>">Config</a></li>
    <li class="nav-item"><a class="nav-link <?= ($active ?? '') === 'test' ? 'active' : '' ?>" href="<?= base_url('admin/ml/test') ?>">Test Prompt</a></li>
    <li class="nav-item"><a class="nav-link <?= ($active ?? '') === 'prompts' ? 'active' : '' ?>" href="<?= base_url('admin/ml/prompts') ?>">Prompts</a></li>
    <li class="nav-item"><a class="nav-link <?= ($active ?? '') === 'senders' ? 'active' : '' ?>" href="<?= base_url('admin/ml/senders') ?>">Senders</a></li>
    <li class="nav-item"><a class="nav-link <?= ($active ?? '') === 'allowed' ? 'active' : '' ?>" href="<?= base_url('admin/ml/allowed') ?>">Allowed</a></li>
    <li class="nav-item"><a class="nav-link <?= ($active ?? '') === 'jobs' ? 'active' : '' ?>" href="<?= base_url('admin/ml/jobs') ?>">Jobs</a></li>
</ul>
