<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Search & Filtering - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .filter-card {
        background: white;
        border-radius: 16px;
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
    .form-control, .form-select {
        border-radius: 10px;
        padding: 0.6rem 1rem;
        border: 1px solid #e0e0e0;
    }
    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.25rem rgba(93, 95, 239, 0.1);
    }
    .btn-search {
        padding: 0.6rem 2rem;
        border-radius: 10px;
        font-weight: 600;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="mb-3">
    <h2 class="fw-bold mb-1" style="color: var(--primary);">Search & Filtering</h2>
    <p class="text-secondary mb-0">Deep dive into your transaction history with advanced filters.</p>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card filter-card mb-4">
    <div class="card-body p-4">
        <form method="GET" action="<?= base_url('dashboard/search') ?>" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase text-secondary">Search Keyword</label>
                <input type="text" name="search" class="form-control" placeholder="Recipient, number, reason..." value="<?= htmlspecialchars($filters['search']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase text-secondary">Category</label>
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat ?>" <?= $filters['category'] == $cat ? 'selected' : '' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase text-secondary">Date Range</label>
                <input type="text" id="reportrange" class="form-control" placeholder="Select dates...">
                <input type="hidden" name="date_from" id="date_from" value="<?= $filters['date_from'] ?>">
                <input type="hidden" name="date_to" id="date_to" value="<?= $filters['date_to'] ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase text-secondary">Amount Range (Ksh)</label>
                <div class="input-group">
                    <input type="number" name="min_amount" class="form-control" placeholder="Min" value="<?= $filters['min_amount'] ?>">
                    <input type="number" name="max_amount" class="form-control" placeholder="Max" value="<?= $filters['max_amount'] ?>">
                </div>
            </div>
            <div class="col-12 text-end mt-4">
                <a href="<?= base_url('dashboard/search') ?>" class="btn btn-light me-2 rounded-pill px-4">Reset</a>
                <button type="submit" class="btn btn-primary btn-search rounded-pill shadow-sm">
                    <i class="fa-solid fa-magnifying-glass me-2"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="searchTable" class="table table-hover table-striped mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Date & Time</th>
                        <th>Category</th>
                        <th>Number / Recipient</th>
                        <th>Transaction Preview</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $tx): ?>
                        <?php 
                            $bodyStr = base64_decode($tx->sms_body);
                            $catLower = strtolower($tx->sms_category);
                            $badgeClass = 'bg-secondary';
                            if (strpos($catLower, 'received') !== false) $badgeClass = 'bg-success';
                            elseif (strpos($catLower, 'sent') !== false) $badgeClass = 'bg-primary';
                            elseif (strpos($catLower, 'error') !== false) $badgeClass = 'bg-danger';
                            elseif (strpos($catLower, 'withdraw') !== false) $badgeClass = 'bg-warning text-dark';
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold fs-7"><?= format_mpesa_date($tx->sms_time) ?></div>
                            </td>
                            <td><span class="badge rounded-pill <?= $badgeClass ?>"><?= $tx->sms_category ?></span></td>
                            <td class="fw-semibold"><?= $tx->sms_number ?></td>
                            <td>
                                <span class="d-inline-block text-truncate text-muted small" style="max-width: 300px;">
                                    <?= htmlspecialchars($bodyStr) ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light rounded-circle shadow-sm view-sms-btn" 
                                        data-time="<?= format_mpesa_date($tx->sms_time) ?>"
                                        data-body="<?= htmlspecialchars($bodyStr) ?>">
                                    <i class="fa-solid fa-eye text-primary"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
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
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    $(document).ready(function() {
        $('#searchTable').DataTable({
            pageLength: 50,
            order: [[0, "desc"]],
            language: { search: "_INPUT_", searchPlaceholder: "Filter results..." }
        });

        // Date Range Picker
        var start = <?= !empty($filters['date_from']) ? "moment('".$filters['date_from']."')" : "moment().subtract(29, 'days')" ?>;
        var end = <?= !empty($filters['date_to']) ? "moment('".$filters['date_to']."')" : "moment()" ?>;

        function cb(start, end) {
            $('#reportrange').val(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            $('#date_from').val(start.format('YYYY-MM-DD'));
            $('#date_to').val(end.format('YYYY-MM-DD'));
        }

        $('#reportrange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
               'Today': [moment(), moment()],
               'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
               'Last 7 Days': [moment().subtract(6, 'days'), moment()],
               'Last 30 Days': [moment().subtract(29, 'days'), moment()],
               'This Month': [moment().startOf('month'), moment().endOf('month')],
               'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, cb);

        cb(start, end);

        // SMS Modal
        const detailModal = new bootstrap.Modal(document.getElementById('smsDetailModal'));
        const modalTime = document.getElementById('modal-sms-time');
        const modalBody = document.getElementById('modal-sms-body');

        $('#searchTable').on('click', '.view-sms-btn', function() {
            modalTime.textContent = $(this).attr('data-time');
            modalBody.innerHTML = $(this).attr('data-body').replace(/\n/g, '<br>');
            detailModal.show();
        });
    });
</script>
<?= $this->endSection() ?>
