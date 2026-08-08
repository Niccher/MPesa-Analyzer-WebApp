<div class="card settings-card">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-database me-2"></i> Database Information</h5>
        <div class="row g-3 mb-4">
            <div class="col-6">
                <div class="p-3 bg-light rounded">
                    <div class="fw-bold text-primary fs-4"><?= $db_size_mb ?> MB</div>
                    <small class="text-muted">Database Size</small>
                </div>
            </div>
            <div class="col-6">
                <div class="p-3 bg-light rounded">
                    <div class="fw-bold text-primary fs-4"><?= $total_tables ?></div>
                    <small class="text-muted">Tables</small>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Table Name</th>
                        <th>Fields</th>
                        <th>Rows</th>
                        <th>Fields (first 10)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tables as $t): ?>
                    <tr>
                        <td class="fw-semibold"><?= $t['name'] ?></td>
                        <td><?= $t['fields'] ?></td>
                        <td><?= number_format($t['rows']) ?></td>
                        <td><small class="text-muted"><?= htmlspecialchars($t['field_list']) ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>