-- Comprehensive De-duplication and Index Fix for tbl_Sms
-- 1. Drop the problematic UNIQUE index on sms_time if it exists
-- (This was likely created as UNIQUE by mistake, causing the #1062 error)
ALTER TABLE tbl_Sms DROP INDEX IF EXISTS idx_sms_time;

-- 2. Create it as a normal (non-unique) INDEX for performance
ALTER TABLE tbl_Sms ADD INDEX idx_sms_time (sms_time);

-- 3. Remove actual duplicates based on the composite key
-- We keep only the record with the highest SMS_ID
DELETE t1 FROM tbl_Sms t1
INNER JOIN tbl_Sms t2 
WHERE 
    t1.SMS_ID < t2.SMS_ID AND 
    t1.sms_owner = t2.sms_owner AND 
    t1.sms__id = t2.sms__id AND 
    t1.sms_time = t2.sms_time;

-- 4. Apply the correct UNIQUE composite index
-- This ensures no future duplicates for the SAME message from the SAME owner
ALTER TABLE tbl_Sms ADD UNIQUE INDEX unq_sms_record (sms_owner, sms__id, sms_time);
