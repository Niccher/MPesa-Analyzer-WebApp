<?php
$conn = new mysqli('127.0.0.1', 'chegecac_mpesa', '9*5Uhv)GsDME', 'chegecac_My_Mpesa');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "--- tbl_Analyzed_Transactions Count ---\n";
$res = $conn->query("SELECT COUNT(*) as cnt FROM tbl_Analyzed_Transactions");
print_r($res->fetch_assoc());

echo "\n--- tbl_Sms Count ---\n";
$res = $conn->query("SELECT COUNT(*) as cnt FROM tbl_Sms");
print_r($res->fetch_assoc());

echo "\n--- tbl_Loot Last Date ---\n";
$res = $conn->query("SELECT MAX(loot_Created) as max_date FROM tbl_Loot");
print_r($res->fetch_assoc());

echo "\n--- tbl_Analyzed_Transactions Sample ---\n";
$res = $conn->query("SELECT * FROM tbl_Analyzed_Transactions LIMIT 2");
while($row = $res->fetch_assoc()) print_r($row);

$conn->close();
