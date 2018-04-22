<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

//Include all necessary libraries and helpers
include('function_library/account_management.php');
include('function_library/agreement_management.php');
include('function_library/event_management.php');
include('function_library/explorer_management.php');
include('function_library/logistics_management.php');
include('function_library/proposal_management.php');
include('helpers/db_connect.php');
include('helpers/helpers.php');
include('helpers/http_handler.php');

//Decode http request payload
$data = json_decode(file_get_contents("php://input"));

//Handle the request
handle_http_call($data);





