<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Transactions - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .trx-card {
        background: var(--card-bg);
        border-radius: 4px;
        border: 1px solid var(--card-border);
    }
    .badge { font-weight: 600; padding: 0.4em 0.8em; }
    .badge-dir { font-size: 0.65rem; padding: 0.2em 0.5em; }
    .page-item.active .page-link {
        background-color: var(--primary);
        border-color: var(--primary);
    }
    .page-link { color: var(--primary); border-radius: 4px !important; margin: 0 2px; }
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
        <a href="<?= base_url('dashboard/transactions?category=finances') ?>" class="btn btn-outline-dark filter-btn <?= $category === 'finances' ? 'active' : '' ?>"><i class="fa-solid fa-coins me-1"></i> All Finances</a>
        <a href="<?= base_url('dashboard/transactions?category=money_in') ?>" class="btn btn-outline-primary filter-btn <?= $category === 'money_in' ? 'active' : '' ?>"><i class="fa-solid fa-arrow-down me-1"></i> Money In</a>
        <a href="<?= base_url('dashboard/transactions?category=money_out') ?>" class="btn btn-outline-danger filter-btn <?= $category === 'money_out' ? 'active' : '' ?>"><i class="fa-solid fa-arrow-up me-1"></i> Money Out</a>
        <a href="<?= base_url('dashboard/transactions?category=notifications') ?>" class="btn btn-outline-secondary filter-btn <?= $category === 'notifications' ? 'active' : '' ?>"><i class="fa-solid fa-bell me-1"></i> Notifications</a>
        
        <div class="vr mx-1 d-none d-md-block"></div>
        
        <button onclick="exportTableToPDF('transactionsTable', 'transactions', this)" class="btn btn-dark filter-btn shadow-sm btn-sm rounded-pill px-3">
            <i class="fa-solid fa-file-pdf me-1"></i> PDF
        </button>
        <a href="<?= base_url('dashboard/transactions/export' . (!empty($category) ? '?category='.$category : '')) ?>" class="btn btn-dark filter-btn shadow-sm btn-sm rounded-pill px-3">
            <i class="fa-solid fa-file-csv me-1"></i> CSV
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php $cs = currency_symbol(); ?>
<div class="card trx-card mb-4">
    <div class="card-body p-3">
        <div class="table-responsive">
            <table id="transactionsTable" class="table table-bordered table-hover table-striped w-100 align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Date &amp; Time</th>
                        <th>Category / Sender</th>
                        <th>Amount</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($transactions)): ?>
                        <?php foreach ($transactions as $tx): ?>
                            <?php
                                $dir = strtolower($tx->cl_direction ?? '');
                                $cat = $tx->cl_category ?? 'Unclassified';
                                $amt = (float)($tx->analyzed_amount ?? 0);

                                if ($dir === 'incoming') {
                                    $badgeClass = 'bg-success';
                                    $dirLabel = '↓ Money In';
                                } elseif ($dir === 'outgoing') {
                                    $badgeClass = 'bg-danger';
                                    $dirLabel = '↑ Money Out';
                                } else {
                                    $badgeClass = 'bg-secondary';
                                    $dirLabel = '— Notification';
                                }

                                $body = base64_decode($tx->sms_body);
                                $bodyStr = mb_check_encoding($body, 'UTF-8') ? $body : "Unreadable content";

                                $ts = is_numeric($tx->sms_time) && $tx->sms_time > 1000000000000
                                    ? (int)($tx->sms_time / 1000)
                                    : (is_numeric($tx->sms_time) ? (int)$tx->sms_time : strtotime($tx->sms_time));
                                $datePart = date('D, M d, Y', $ts);
                                $timePart = date('h:i A', $ts);
                            ?>
                            <tr>
                                <td class="ps-4" style="min-width:120px;">
                                    <div class="fw-semibold small lh-1"><?= $datePart ?></div>
                                    <div class="text-muted" style="font-size:0.7rem; line-height:1;"><?= $timePart ?></div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <span class="badge rounded-pill <?= $badgeClass ?> badge-dir w-auto"><?= $dirLabel ?></span>
                                        <span class="small text-secondary" style="font-size:0.7rem;"><?= htmlspecialchars($cat) ?></span>
                                        <span class="font-monospace small text-primary" style="font-size:0.72rem;"><?= htmlspecialchars($tx->sms_number) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold small <?= $dir === 'incoming' ? 'text-success' : ($dir === 'outgoing' ? 'text-danger' : 'text-muted') ?>">
                                        <?php if ($amt > 0): ?>
                                            <?= $dir === 'incoming' ? '+' : ($dir === 'outgoing' ? '-' : '') ?>
                                            <?= $cs ?> <?= number_format($amt, 2) ?>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted small" style="word-break: break-word;">
                                            <?= htmlspecialchars($bodyStr) ?>
                                        </span>
                                        <?php if (!empty($tx->counterparty) && $tx->counterparty !== 'Unknown'): ?>
                                        <button class="btn btn-sm btn-outline-primary rounded-pill shadow-sm flex-shrink-0 py-0 px-2 fw-semibold recategorize-btn"
                                                data-trans-id="<?= $tx->sms__id ?>"
                                                data-counterparty="<?= htmlspecialchars($tx->counterparty) ?>"
                                                data-category="<?= htmlspecialchars($tx->analyzed_category ?? '') ?>"
                                                title="Smart Auto-Fix Rule"
                                                style="font-size:0.7rem;">
                                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Fix
                                        </button>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-light rounded-circle shadow-sm view-sms-btn flex-shrink-0"
                                                data-time="<?= $datePart ?> <?= $timePart ?>"
                                                data-body="<?= htmlspecialchars($bodyStr) ?>"
                                                title="View details">
                                            <i class="fa-solid fa-eye text-primary"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
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
                            <optgroup label="Transaction Types">
                                <option value="Received">Received</option>
                                <option value="Sent">Sent to Mobile</option>
                                <option value="Sent to LNM">Paybill / Till</option>
                                <option value="Paybill">Paybill</option>
                                <option value="Till">Till Number</option>
                                <option value="Withdraw">Withdrawal</option>
                                <option value="Fuliza Loan Taken">Fuliza Loan Taken</option>
                                <option value="Fuliza Loan Paid">Fuliza Loan Paid</option>
                            </optgroup>
                            <?php if (!empty($llm_categories)): ?>
                            <optgroup label="LLM Categories">
                                <?php foreach ($llm_categories as $lc): ?>
                                <option value="<?= htmlspecialchars($lc) ?>"><?= htmlspecialchars($lc) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endif; ?>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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
            
            const cp = $('#rc_keyword').val();
            const cat = $('#rc_category').val();
            
            if (!cat) {
                showAlert('Validation Error', 'Please select a category.', 'warning');
                return;
            }

            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

            $.ajax({
                url: '<?= base_url('dashboard/analyse/rule') ?>',
                method: 'POST',
                data: {
                    keyword: cp,
                    category: cat
                },
                success: function(response) {
                    if (response.status === 'success') {
                        showAlert('Success', response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('Error', response.message, 'danger');
                        btn.prop('disabled', false).text('Save Rule & Apply');
                    }
                },
                error: function() {
                    showAlert('Failed', 'Submission failed. Please try again.', 'danger');
                    btn.prop('disabled', false).text('Save Rule & Apply');
                }
            });
        });
    });

    // PDF Export — preserves table styling via html2canvas
    window.exportTableToPDF = function(tableId, filename, btnEl) {
        const btn = btnEl || document.querySelector(`button[onclick*="${tableId}"]`);
        if (!btn) return;
        const orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Exporting...';

        const table = document.getElementById(tableId);
        if (!table) { btn.disabled = false; btn.innerHTML = orig; return; }

        const wrapper = document.createElement('div');
        wrapper.style.padding = '20px';
        wrapper.style.background = '#fff';
        wrapper.style.width = table.offsetWidth + 'px';
        const clone = table.cloneNode(true);
        wrapper.appendChild(clone);
        document.body.appendChild(wrapper);

        html2canvas(wrapper, {
            scale: 2,
            useCORS: true,
            logging: false,
            backgroundColor: '#ffffff'
        }).then(canvas => {
            document.body.removeChild(wrapper);
            const imgData = canvas.toDataURL('image/png');
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('l', 'mm', 'a4');
            const pdfW = pdf.internal.pageSize.getWidth();
            const pdfH = (canvas.height * pdfW) / canvas.width;
            pdf.addImage(imgData, 'PNG', 0, 0, pdfW, pdfH);
            pdf.save(filename + '.pdf');
            btn.disabled = false;
            btn.innerHTML = orig;
        }).catch(err => {
            document.body.removeChild(wrapper);
            console.error('PDF export error:', err);
            if (typeof showAlert === 'function') showAlert('Export Failed', 'Could not generate PDF.', 'danger');
            btn.disabled = false;
            btn.innerHTML = orig;
        });
    }
</script>
<?= $this->endSection() ?>
