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
    
    .badge {
        font-weight: 600;
        padding: 0.4em 0.8em;
    }
    
    table.dataTable.table-striped > tbody > tr.odd > * {
        box-shadow: inset 0 0 0 9999px rgba(93, 95, 239, 0.02);
    }
    
    .page-item.active .page-link {
        background-color: var(--primary);
        border-color: var(--primary);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);">Transactions Database</h2>
        <p class="text-secondary mb-0">Search, filter, and review your parsed SMS records.</p>
    </div>
    <div class="d-flex flex-wrap gap-2 mb-1" id="categoryFilters">
        <button type="button" class="btn btn-outline-primary active filter-btn" data-filter="">All SMS</button>
        <button type="button" class="btn btn-outline-success filter-btn" data-filter="Receive">Received</button>
        <button type="button" class="btn btn-outline-primary filter-btn" data-filter="Sent">Sent</button>
        <button type="button" class="btn btn-outline-info filter-btn" data-filter="Recent">Recent Activity</button>
        <button type="button" class="btn btn-outline-warning filter-btn" data-filter="Paybill">Paybill</button>
        <button type="button" class="btn btn-outline-warning filter-btn" data-filter="Till">Till</button>
        <button type="button" class="btn btn-outline-danger filter-btn" data-filter="Fuliza">Fuliza</button>
        <button type="button" class="btn btn-outline-secondary filter-btn" data-filter="Error">Errors/Others</button>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card trx-card mb-4">
    <div class="card-body p-4">


        <div class="table-responsive">
            <table id="transactionsTable" class="table table-hover table-striped w-100 align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Date & Time</th>
                        <th scope="col">Category</th>
                        <th scope="col">Entity / Number</th>
                        <th scope="col">Preview</th>
                        <th scope="col" class="text-end px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($transactions)) : ?>
                        <?php foreach ($transactions as $tx) : ?>
                            <?php 
                                $catLower = strtolower($tx->sms_category);
                                $badgeClass = 'bg-secondary';
                                if (strpos($catLower, 'received') !== false) $badgeClass = 'bg-success';
                                elseif (strpos($catLower, 'sent') !== false) $badgeClass = 'bg-primary';
                                elseif (strpos($catLower, 'error') !== false) $badgeClass = 'bg-danger';
                                elseif (strpos($catLower, 'withdraw') !== false) $badgeClass = 'bg-warning text-dark';
                                elseif (strpos($catLower, 'fuliza') !== false) $badgeClass = 'bg-danger';
                                
                                $body = base64_decode($tx->sms_body);
                                $bodyStr = (mb_check_encoding($body, 'UTF-8')) ? $body : "Unable to decode";
                                
                                // Handle time if it's a string or ms timestamp
                                $displayTime = is_numeric($tx->sms_time) ? date('Y-m-d H:i:s', $tx->sms_time / 1000) : $tx->sms_time;
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= format_mpesa_date($tx->sms_time) ?></div>
                                </td>
                                <td><span class="badge rounded-pill <?= $badgeClass ?>"><?= htmlspecialchars($tx->sms_category) ?></span></td>
                                <td class="fw-bold"><?= htmlspecialchars($tx->sms_number) ?></td>
                                <td>
                                    <span class="d-inline-block text-truncate text-muted small" style="max-width: 250px;">
                                        <?= htmlspecialchars($bodyStr) ?>
                                    </span>
                                </td>
                                <td class="text-end px-4">
                                    <button class="btn btn-sm btn-light rounded-circle shadow-sm view-sms-btn" 
                                            data-time="<?= format_mpesa_date($tx->sms_time) ?>"
                                            data-body="<?= htmlspecialchars($bodyStr) ?>">
                                        <i class="fa-solid fa-eye text-primary"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
    </div>
</div>

<!-- SMS Detail Modal -->
<div class="modal fade" id="smsDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Transaction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php helper('mpesa_date'); ?>
<script>
    $(document).ready(function() {
        var table = $('#transactionsTable').DataTable({
            pageLength: 25,
            lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "All"]],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search records..."
            },
            order: [[0, "desc"]]
        });

        // Modal Logic
        const detailModal = new bootstrap.Modal(document.getElementById('smsDetailModal'));
        const modalTime = document.getElementById('modal-sms-time');
        const modalBody = document.getElementById('modal-sms-body');

        $('#transactionsTable').on('click', '.view-sms-btn', function() {
            modalTime.textContent = $(this).attr('data-time');
            modalBody.innerHTML = $(this).attr('data-body').replace(/\n/g, '<br>');
            detailModal.show();
        });

        $('.filter-btn').on('click', function() {
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            var filterValue = $(this).data('filter');
            
            if (filterValue === "Recent") {
                table.search('').columns().search('').order([0, 'desc']).draw();
            } else if (filterValue === "Till" || filterValue === "Paybill") {
                table.search(filterValue).draw();
            } else {
                table.columns(1).search(filterValue).draw();
            }
        });
    });
</script>
<?= $this->endSection() ?>
