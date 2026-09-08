<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPerformanceIndexesToTblSmsAndLoot extends Migration
{
    public function up(): void
    {
        // Add composite index for fast category and timeframe aggregations
        $this->db->query("CREATE INDEX IF NOT EXISTS idx_sms_owner_cat_date ON tbl_Sms (sms_owner(64), sms_category, sms_trans_date)");

        // Add index for fast loot batch resolution
        $this->db->query("CREATE INDEX IF NOT EXISTS idx_sms_loot_source ON tbl_Sms (sms_loot_source(64))");

        // Add composite index for loot device and owner lookups
        $this->db->query("CREATE INDEX IF NOT EXISTS idx_loot_device_owner ON tbl_Loot (loot_Device(64), loot_Owner(64))");
    }

    public function down(): void
    {
        $this->db->query("DROP INDEX IF EXISTS idx_sms_owner_cat_date ON tbl_Sms");
        $this->db->query("DROP INDEX IF EXISTS idx_sms_loot_source ON tbl_Sms");
        $this->db->query("DROP INDEX IF EXISTS idx_loot_device_owner ON tbl_Loot");
    }
}
