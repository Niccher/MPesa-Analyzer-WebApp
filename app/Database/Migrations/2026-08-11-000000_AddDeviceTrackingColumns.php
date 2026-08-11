<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * LEGACY NO-OP STUB.
 *
 * This file originally added an IP column to two tables (tbl_Loot and
 * tbl_Devices) in a single migration. It has been split into one file per
 * table to satisfy the "one migration file per table" convention:
 *
 *   2026-08-11-000001_AddIpToTblLoot
 *   2026-08-11-000002_AddIpToTblDevices
 *
 * The class name and filename are kept unchanged so the recorded migration
 * history (UID) and rollback behaviour remain intact. This stub is now a
 * no-op; ownership of the schema changes moved to the split files above.
 */
class AddDeviceTrackingColumns extends Migration
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
