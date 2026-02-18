<?php
error_reporting(0);
require_once ('fpx.php');
$bank = new FPX();

$post = [
	'mode' => $_REQUEST['mode'],
	'env' => $_REQUEST['env'],
	'exchange' => $_REQUEST['exchange']
];

$bank_list = $bank->get_bank_list($post);

header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

echo json_encode($bank_list, JSON_PRETTY_PRINT);