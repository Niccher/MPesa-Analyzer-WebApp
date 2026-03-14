<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Transactions - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .trx-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: none;
    }
    .badge { font-weight: 600; padding: 0.4em 0.8em; }
    .page-item.active .page-link {
        background-color: var(--primary);
        border-color: var(--primary);
    }
    .page-link { color: var(--primary); border-radius: 8px !important; margin: 0 2px; }
    .filter-btn.active { box-shadow: 0 4px 12px rgba(93, 95, 239, 0.25); }
    .pagination { margin: 0; }
    .table tbody tr:hover { background: rgba(93,95,239,0.03); }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);">Transactions Database</h2>
        <p class="text-secondary mb-0">
            Showing <strong><?= number_format(($page - 1) * $perPage + 1) ?>–<?= number_format(min($page * $perPage, $total)) ?></strong>
            of <strong><?= number_format($total) ?></strong> records
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2 mb-1">
        <a href="<?= base_url('dashboard/transactions') ?>" class="btn btn-outline-primary filter-btn <?= empty($category) ? 'active' : '' ?>">All SMS</a>
        <a href="<?= base_url('dashboard/transactions?category=Received') ?>" class="btn btn-outline-success filter-btn <?= $category === 'Received' ? 'active' : '' ?>">Received</a>
        <a href="<?= base_url('dashboard/transactions?category=Sent') ?>" class="btn btn-outline-primary filter-btn <?= $category === 'Sent' ? 'active' : '' ?>">Sent</a>
        <a href="<?= base_url('dashboard/transactions?category=Withdraw') ?>" class="btn btn-outline-warning filter-btn <?= $category === 'Withdraw' ? 'active' : '' ?>">Withdraw</a>
        <a href="<?= base_url('dashboard/transactions?category=Fuliza') ?>" class="btn btn-outline-danger filter-btn <?= $category === 'Fuliza' ? 'active' : '' ?>">Fuliza</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card trx-card mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped w-100 align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Date &amp; Time</th>
                        <th>Category</th>
                        <th>Counterparty</th>
                        <th>Preview</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($transactions)): ?>
                        <?php foreach ($transactions as $tx): ?>
                            <?php
                                $catLower = strtolower($tx->sms_category);
                                $badgeClass = 'bg-secondary';
                                if (strpos($catLower, 'received') !== false) $badgeClass = 'bg-success';
                                elseif (strpos($catLower, 'sent') !== false) $badgeClass = 'bg-primary';
                                elseif (strpos($catLower, 'withdraw') !== false) $badgeClass = 'bg-warning text-dark';
                                elseif (strpos($catLower, 'fuliza') !== false) $badgeClass = 'bg-danger';
                                elseif (strpos($catLower, 'error') !== false) $badgeClass = 'bg-danger';

                                $body = base64_decode($tx->sms_body);
                                $bodyStr = mb_check_encoding($body, 'UTF-8') ? $body : "Unable to decode";
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold small"><?= format_mpesa_date($tx->sms_time) ?></div>
                                </td>
                                <td>
                                    <span class="badge rounded-pill <?= $badgeClass ?>"><?= htmlspecialchars($tx->sms_category) ?></span>
                                    <?php if (!empty($tx->analyzed_category)): ?>
                                        <div class="mt-1"><span class="badge bg-light text-dark border border-secondary border-opacity-25" style="font-size:0.7rem;"><i class="fa-solid fa-tag me-1 text-secondary"></i> <?= htmlspecialchars($tx->analyzed_category) ?></span></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold small text-dark"><?= !empty($tx->counterparty) ? htmlspecialchars($tx->counterparty) : '<span class="text-muted fst-italic">Unknown</span>' ?></div>
                                    <div class="text-muted font-monospace" style="font-size:0.7rem;"><?= htmlspecialchars($tx->sms_number) ?></div>
                                </td>
                                <td>
                                    <span class="d-inline-block text-truncate text-muted small" style="max-width: 250px;">
                                        <?= htmlspecialchars($bodyStr) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <?php if (!empty($tx->counterparty) && $tx->counterparty !== 'Unknown'): ?>
                                    <button class="btn btn-sm btn-outline-primary rounded-pill shadow-sm me-2 py-1 px-3 fw-semibold recategorize-btn"
                                            data-trans-id="<?= $tx->sms__id ?>"
                                            data-counterparty="<?= htmlspecialchars($tx->counterparty) ?>"
                                            data-category="<?= htmlspecialchars($tx->analyzed_category ?? '') ?>"
                                            title="Smart Auto-Fix Rule">
                                        <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Fix
                                    </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-light rounded-circle shadow-sm view-sms-btn"
                                            data-time="<?= format_mpesa_date($tx->sms_time) ?>"
                                            data-body="<?= htmlspecialchars($bodyStr) ?>">
                                        <i class="fa-solid fa-eye text-primary"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-inbox fa-2x mb-3 d-block opacity-25"></i>
                                No transactions found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top bg-light rounded-bottom">
            <div class="text-muted small">
                Page <strong><?= $page ?></strong> of <strong><?= $totalPages ?></strong>
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0 gap-1">
                    <!-- Previous -->
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link rounded-3 shadow-none" href="<?= base_url("dashboard/transactions?page=".($page-1).(!empty($category) ? "&category=$category" : '')) ?>">
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>
                    </li>

                    <?php
                    $start = max(1, $page - 3);
                    $end   = min($totalPages, $page + 3);
                    if ($start > 1): ?>
                        <li class="page-item">
                            <a class="page-link rounded-3 shadow-none" href="<?= base_url("dashboard/transactions?page=1".(!empty($category) ? "&category=$category" : '')) ?>">1</a>
                        </li>
                        <?php if ($start > 2): ?>
                            <li class="page-item disabled"><span class="page-link shadow-none">…</span></li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link rounded-3 shadow-none" href="<?= base_url("dashboard/transactions?page=$i".(!empty($category) ? "&category=$category" : '')) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($end < $totalPages): ?>
                        <?php if ($end < $totalPages - 1): ?>
                            <li class="page-item disabled"><span class="page-link shadow-none">…</span></li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link rounded-3 shadow-none" href="<?= base_url("dashboard/transactions?page=$totalPages".(!empty($category) ? "&category=$category" : '')) ?>"><?= $totalPages ?></a>
                        </li>
                    <?php endif; ?>

                    <!-- Next -->
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link rounded-3 shadow-none" href="<?= base_url("dashboard/transactions?page=".($page+1).(!empty($category) ? "&category=$category" : '')) ?>">
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="text-muted small">
                <?= number_format($total) ?> total records
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- SMS Detail Modal -->
<div class="modal fade" id="smsDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Transaction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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

<!-- Recategorize Modal -->
<div class="modal fade" id="recategorizeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-wand-magic-sparkles text-primary me-2"></i>Smart Auto-Fix</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-4">Teach the system! Choose the correct category for this entity. All past and future transactions matching this name will be updated.</p>
                <form id="recategorizeForm">
                    <input type="hidden" id="rc_trans_id" name="trans_id">
                    
                    <div class="mb-3">
                        <label class="text-secondary small fw-bold text-uppercase">Entity / Keyword</label>
                        <input type="text" class="form-control bg-light border-0" id="rc_keyword" name="keyword" readonly>
                    </div>

                    <div class="mb-4">
                        <label class="text-secondary small fw-bold text-uppercase">Correct Category</label>
                        <select class="form-select form-select-lg placeholder-wave border-primary-subtle" id="rc_category" name="category" required>
                            <option value="" disabled selected>Select a category...</option>
                            <option value="Groceries">Groceries & Supermarkets</option>
                            <option value="Food">Food & Dining</option>
                            <option value="Transport">Transport & Fuel</option>
                            <option value="Utilities">Utilities & Bills</option>
                            <option value="Shopping">Shopping & Retail</option>
                            <option value="Health">Health & Fitness</option>
                            <option value="Entertainment">Entertainment</option>
                            <option value="Family">Family & Personal</option>
                            <option value="Business">Business Expenses</option>
                            <option value="Other">Other Miscellaneous</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 bg-light rounded-bottom-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="saveRuleBtn">Save Rule & Apply</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        const detailModal = new bootstrap.Modal(document.getElementById('smsDetailModal'));
        const fixModal = new bootstrap.Modal(document.getElementById('recategorizeModal'));
        
        const modalTime = document.getElementById('modal-sms-time');
        const modalBody = document.getElementById('modal-sms-body');

        $(document).on('click', '.view-sms-btn', function() {
            modalTime.textContent = $(this).attr('data-time');
            modalBody.innerHTML = $(this).attr('data-body').replace(/\n/g, '<br>');
            detailModal.show();
        });

        // Recategorize Logic
        $(document).on('click', '.recategorize-btn', function() {
            $('#rc_trans_id').val($(this).data('trans-id'));
            $('#rc_keyword').val($(this).data('counterparty'));
            
            // Optionally pre-select current category if it matches the dropdown
            const currentCat = $(this).data('category');
            $('#rc_category').val('');
            $('#rc_category option').each(function() {
                if ($(this).val() === currentCat) {
                    $('#rc_category').val(currentCat);
                }
            });
            
            fixModal.show();
        });

        $('#saveRuleBtn').click(function() {
            const btn = $(this);
            const form = $('#recategorizeForm');
            
            if (!$('#rc_category').val()) {
                alert('Please select a category.');
                return;
            }

            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

            $.ajax({
                url: '<?= base_url('dashboard/analyse/rule') ?>',
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                        btn.prop('disabled', false).text('Save Rule & Apply');
                    }
                },
                error: function(xhr) {
                    alert('Submission failed. Please try again.');
                    btn.prop('disabled', false).text('Save Rule & Apply');
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>
