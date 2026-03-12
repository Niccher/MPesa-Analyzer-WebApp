<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Transactions - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .table-card {
        background: white;
        border-radius: var(--radius);
        padding: 24px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        margin-top: 20px;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .table-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .search-box {
        position: relative;
        width: 300px;
    }

    .search-box input {
        width: 100%;
        padding: 10px 15px 10px 35px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        font-family: 'Outfit', sans-serif;
        outline: none;
        transition: border-color 0.2s;
    }

    .search-box input:focus {
        border-color: var(--primary);
    }

    .search-box i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #a0a0a0;
    }

    .table-container {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }

    .data-table th, .data-table td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #f0f0f0;
    }

    .data-table th {
        background-color: #f8f9fa;
        color: var(--text-light);
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        position: sticky;
        top: 0;
    }

    .data-table tbody tr {
        transition: background-color 0.2s;
    }

    .data-table tbody tr:hover {
        background-color: var(--hover-bg);
    }

    .badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .badge-success { background: rgba(46, 213, 115, 0.1); color: #2ED573; }
    .badge-primary { background: rgba(93, 95, 239, 0.1); color: var(--primary); }
    .badge-danger { background: rgba(255, 71, 87, 0.1); color: #FF4757; }
    .badge-warning { background: rgba(255, 165, 2, 0.1); color: #FFA502; }
    .badge-default { background: #f0f0f0; color: #666; }

    .sms-snippet {
        font-size: 0.85rem;
        color: #888;
        max-width: 300px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="header-section">
    <h2 style="font-weight: 700; color: var(--primary);">Transactions Database</h2>
    <p style="color: var(--text-light);">Search and filter through all extracted records.</p>
</div>

<div class="table-card">
    <div class="table-header">
        <h3 class="table-title"><i class="fa-solid fa-list-ul" style="color: var(--primary)"></i> Recent Records</h3>
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Search transactions...">
        </div>
    </div>

    <div class="table-container">
        <table class="data-table" id="transactionsTable">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Category</th>
                    <th>Entity/Number</th>
                    <th>Status</th>
                    <th>Raw Detail</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transactions)) : ?>
                    <?php foreach ($transactions as $tx) : ?>
                        <?php 
                            // Determine badge color based on category
                            $badgeClass = 'badge-default';
                            if (strpos(strtolower($tx->sms_category), 'receive') !== false) $badgeClass = 'badge-success';
                            elseif (strpos(strtolower($tx->sms_category), 'sent') !== false) $badgeClass = 'badge-primary';
                            elseif (strpos(strtolower($tx->sms_category), 'error') !== false) $badgeClass = 'badge-danger';
                            elseif (strpos(strtolower($tx->sms_category), 'withdraw') !== false) $badgeClass = 'badge-warning';
                            
                            // Try to decode the base64 body safely
                            $body =  base64_decode($tx->sms_body);
                            $bodyStr = (mb_check_encoding($body, 'UTF-8')) ? $body : "Unable to decode";
                        ?>
                        <tr>
                            <td class="search-target"><strong><?= date('M d, Y', ($tx->sms_time / 1000)) ?></strong><br><small><?= date('h:i A', ($tx->sms_time / 1000)) ?></small></td>
                            <td class="search-target"><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($tx->sms_category) ?></span></td>
                            <td class="search-target"><strong><?= htmlspecialchars($tx->sms_number) ?></strong></td>
                            <td><?= $tx->sms_seen ? '<i class="fa-regular fa-eye text-success"></i> Seen' : '<i class="fa-regular fa-eye-slash text-muted"></i> New' ?></td>
                            <td class="search-target"><span class="sms-snippet" title="<?= htmlspecialchars($bodyStr) ?>"><?= htmlspecialchars($bodyStr) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 30px; color: #888;">No transaction data found for this account. Make sure you have synced from the Android app!</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Simple fast client-side search filtering
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.getElementById('transactionsTable').getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        
        for (let i = 0; i < rows.length; i++) {
            let cells = rows[i].getElementsByClassName('search-target');
            let match = false;
            if (cells.length > 0) {
                for (let j = 0; j < cells.length; j++) {
                    if (cells[j].innerText.toLowerCase().indexOf(filter) > -1) {
                        match = true;
                        break;
                    }
                }
                rows[i].style.display = match ? "" : "none";
            }
        }
    });
</script>
<?= $this->endSection() ?>
