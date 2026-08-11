<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * LEGACY NO-OP STUB.
 *
 * This file originally added user_id tracking columns to five tables plus
 * data backfills and a deduplication unique key, all in a single migration.
 * It has been split into one file per table to satisfy the "one migration
 * file per table" convention:
 *
 *   2026-08-10-010001_AddUserIdToTblSms
 *   2026-08-10-010002_AddUserIdToTblLoot
 *   2026-08-10-010003_AddUserIdToTblSmsClassification
 *   2026-08-10-010004_AddUserIdToTblLootSummary
 *   2026-08-10-010005_AddUserIdToTblDevices
 *
 * The class name and filename are kept unchanged so the recorded migration
 * history (UID) and rollback behaviour remain intact. This stub is now a
 * no-op; ownership of the schema changes and backfills moved to the split
 * files above (which run in dependency order: Sms, Loot, Classification,
 * LootSummary, Devices).
 */
class AddUserIdAndDedupKey extends Migration
{
    public function up()
    {
        // No-op: schema changes and backfills are applied by the split
        // per-table migrations.
    }

    public function down()
    {
        // No-op: schema changes are reverted by the split per-table migrations.
    }
}
