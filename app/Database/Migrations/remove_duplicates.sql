-- SQL to remove duplicates from tbl_Sms for M-Pesa Analyzer
-- This query identifies duplicates based on (sms_owner, sms__id, sms_time)
-- and keeps only the record with the highest SMS_ID (most recent insertion).

-- 1. Remove duplicates
DELETE t1 FROM tbl_Sms t1
INNER JOIN tbl_Sms t2 
WHERE 
    t1.SMS_ID < t2.SMS_ID AND 
    t1.sms_owner = t2.sms_owner AND 
    t1.sms__id = t2.sms__id AND 
    t1.sms_time = t2.sms_time;

-- 2. Apply unique constraint to prevent future duplicates
ALTER TABLE tbl_Sms ADD UNIQUE INDEX unq_sms_record (sms_owner, sms__id, sms_time);
