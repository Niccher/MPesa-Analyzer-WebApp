<?php
// /opt/lampp/htdocs/hosts/Mpesa-Analyzer-Web2/db_debug.php
define('ENVIRONMENT', 'development');
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
$pathsConfig = __DIR__ . '/app/Config/Paths.php';
require realpath($pathsConfig) ?: $pathsConfig;
$paths = new Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';
$db = \Config\Database::connect();

echo "--- tbl_Analyzed_Transactions ---\n";
$query = $db->table('tbl_Analyzed_Transactions')->limit(5)->get();
print_r($query->getResult());

echo "\n--- tbl_Sms ---\n";
$query = $db->table('tbl_Sms')->orderBy('sms_time', 'DESC')->limit(5)->get();
foreach ($query->getResult() as $row) {
    $row->sms_body = base64_decode($row->sms_body);
    print_r($row);
}

echo "\n--- Last Upload Date ---\n";
$query = $db->table('tbl_Loot')->selectMax('loot_Created', 'max_date')->get();
print_r($query->getResult());
