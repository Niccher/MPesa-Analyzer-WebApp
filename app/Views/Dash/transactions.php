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
                        <th scope="col">Status</th>
                        <th scope="col">Raw Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($transactions)) : ?>
                        <?php foreach ($transactions as $tx) : ?>
                            <?php 
                                $catLower = strtolower($tx->sms_category);
                                $badgeClass = 'bg-secondary';
                                if (strpos($catLower, 'receive') !== false) $badgeClass = 'bg-success';
                                elseif (strpos($catLower, 'sent') !== false) $badgeClass = 'bg-primary';
                                elseif (strpos($catLower, 'error') !== false) $badgeClass = 'bg-danger';
                                elseif (strpos($catLower, 'withdraw') !== false) $badgeClass = 'bg-warning text-dark';
                                elseif (strpos($catLower, 'fuliza') !== false) $badgeClass = 'bg-danger';
                                
                                $body = base64_decode($tx->sms_body);
                                $bodyStr = (mb_check_encoding($body, 'UTF-8')) ? $body : "Unable to decode";
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= date('M d, Y', ($tx->sms_time / 1000)) ?></div>
                                    <small class="text-muted"><?= date('h:i A', ($tx->sms_time / 1000)) ?></small>
                                </td>
                                <td><span class="badge rounded-pill <?= $badgeClass ?>"><?= htmlspecialchars($tx->sms_category) ?></span></td>
                                <td class="fw-bold"><?= htmlspecialchars($tx->sms_number) ?></td>
                                <td>
                                    <?php if ($tx->sms_seen): ?>
                                        <span class="text-success"><i class="fa-regular fa-eye"></i> Seen</span>
                                    <?php else: ?>
                                        <span class="text-muted"><i class="fa-regular fa-eye-slash"></i> New</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="d-inline-block text-truncate text-muted" style="max-width: 250px;" title="<?= htmlspecialchars($bodyStr) ?>">
                                        <?= htmlspecialchars($bodyStr) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Initialize DataTables with 25 rows per page and Bootstrap 5 styling
        var table = $('#transactionsTable').DataTable({
            pageLength: 25,
            lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "All"]],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search records..."
            },
            order: [[0, "desc"]] // Order by date descending by default
        });

        // Custom filtering via Bootstrap Breadcrumb Outline Buttons
        $('.filter-btn').on('click', function() {
            // Remove active class from all buttons, add to clicked
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');

            var filterValue = $(this).data('filter');
            
            if (filterValue === "Recent") {
                // Clear search, order by date (column 0)
                table.search('').columns().search('').order([0, 'desc']).draw();
            } else if (filterValue === "Till" || filterValue === "Paybill") {
                // Approximate filtering by search term in category or raw detail
                table.search(filterValue).draw();
            } else {
                // Search specifically in the Category column (Column 1)
                table.columns(1).search(filterValue).draw();
            }
        });
    });
</script>
<?= $this->endSection() ?>
