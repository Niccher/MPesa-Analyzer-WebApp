<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * LEGACY NO-OP STUB.
 *
 * This file originally created four tables (tbl_Loot, tbl_Loot_Summary,
 * tbl_Devices, tbl_User_Devices) in a single migration. It has been split
 * into one file per table to satisfy the "one migration file per table"
 * convention:
 *
 *   2026-06-22-000005_CreateTblLoot
 *   2026-06-22-000006_CreateTblLootSummary
 *   2026-06-22-000007_CreateTblDevices
 *   2026-06-22-000008_CreateTblUserDevices
 *
 * The class name and filename are kept unchanged so the recorded migration
 * history (UID) and rollback behaviour remain intact. This stub is now a
 * no-op; ownership of the tables moved to the split files above.
 */
class CreateMissingMpesaTables extends Migration
{
    public function up()
    {
        // No-op: tables are created by the split per-table migrations.
    }

    public function down()
    {
        // No-op: tables are dropped by the split per-table migrations.
    }
}
