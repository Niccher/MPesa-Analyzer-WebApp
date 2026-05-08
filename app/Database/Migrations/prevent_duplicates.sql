-- Database cleanup and duplicate prevention for M-Pesa Analyzer
-- 1. Ensure unique constraint on SMS records (Owner + ID + Time)
ALTER TABLE tbl_Sms ADD UNIQUE INDEX unq_sms_record (sms_owner, sms__id, sms_time);

-- 2. Optional: Remove any existing duplicates before applying index
-- (Uncomment and run if the ALTER fails due to existing duplicates)
/*
DELETE t1 FROM tbl_Sms t1
INNER JOIN tbl_Sms t2 
WHERE 
    t1.sms_id < t2.sms_id AND 
    t1.sms_owner = t2.sms_owner AND 
    t1.sms__id = t2.sms__id AND 
    t1.sms_time = t2.sms_time;
*/
