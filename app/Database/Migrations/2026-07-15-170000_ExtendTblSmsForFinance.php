<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * LEGACY NO-OP STUB.
 *
 * This file originally added financial columns to two tables and created
 * two more in a single migration. It has been split into one file per
 * table to satisfy the "one migration file per table" convention:
 *
 *   2026-07-15-170001_AddFinanceColumnsToTblSms
 *   2026-07-15-170002_CreateTblSenderProfiles
 *   2026-07-15-170003_CreateTblSmsProcessing
 *   2026-07-15-170004_AddOrigSmsIntIdToAnalyzedTransactions
 *
 * The class name and filename are kept unchanged so the recorded migration
 * history (UID) and rollback behaviour remain intact. This stub is now a
 * no-op; ownership of the tables moved to the split files above.
 */
class ExtendTblSmsForFinance extends Migration
{
    public function up()
    {
        // No-op: schema changes are applied by the split per-table migrations.
    }

    public function down()
    {
        // No-op: schema changes are reverted by the split per-table migrations.
    }
}
