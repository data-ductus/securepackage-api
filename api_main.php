<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

include ('db_connect.php');
include ('db_operations.php');
include ('logistics_api.php');
include ('helpers.php');
include ('event_generator.php');

$data = json_decode(file_get_contents("php://input"));

handleHttpCall($data);

function handleHttpCall($data) {
    $connection = db_connect();
    switch ($data->action) {
        case "NEW_ACCOUNT":
            generate_new_account($data, $connection);
            break;
        case "LOGIN":
            login($data, $connection);
            break;
        case "FETCH_AGREEMENTS":
            display_agreements($data, $connection);
            break;
        case "NEW_ITEM":
            add_new_item($data, $connection);
            break;
        case "FETCH_AGREEMENT_INFO":
            display_agreement($data, $connection);
            break;
        case "FETCH_AGREEMENT_TERMS":
            display_agreement_terms($data, $connection);
            break;
        case "FETCH_AGREEMENT_ITEM":
            display_agreement_item($data, $connection);
            break;
        case "FETCH_AGREEMENT_IMAGES":
            display_agreement_images($data, $connection);
            break;
        case "PROPOSE_TERMS":
            propose_terms($data, $connection);
            break;
        case "UPDATE_USER_INFO":
            update_user_details($data, $connection);
            break;
        case "FETCH_USER_PROPOSALS":
            display_user_proposals($data, $connection);
            break;
        case "REMOVE_PROPOSAL":
            remove_proposal($data, $connection);
            break;
        case "REJECT_PROPOSAL":
            reject_proposal($data, $connection);
            break;
        case "ACCEPT_PROPOSAL":
            accept_proposal($data, $connection);
            break;
        case "REMOVE_ITEM":
            remove_item($data, $connection);
            break;
        case "FETCH_LOGISTICS_INFO":
            fetch_logistics_agreement_info($data, $connection);
            break;
        case "FETCH_LOGISTICS_SELLER":
            fetch_seller($data, $connection);
            break;
        case "FETCH_LOGISTICS_BUYER":
            fetch_buyer($data, $connection);
            break;
        case "START_LOGISTICS_PROCESS":
            initiate_logistics($data, $connection);
            break;
        case "FETCH_SIMULATION_SENSORS":
            fetch_simulation_sensors($data, $connection);
            break;
        case "FETCH_SIMULATION_THRESHOLDS":
            fetch_simulation_thresholds($data, $connection);
            break;
        case "SENSOR_DATA":
            insert_sensor_data($data, $connection);
            break;
        case "VIOLATE":
            violate_agreement($data, $connection);
            break;
        case "ALTER_STATE":
            alter_agreement_state($data, $connection);
            break;
        case "FETCH_LOGISTICS_PARAMETERS":
            fetch_logistics_parameters($data, $connection);
            break;
        case "FETCH_SENSOR_DATA":
            fetch_sensor_data($data, $connection);
            break;
        case "CHECK_RETURN":
            check_return($data, $connection);
            break;
        case "FETCH_RECENT_EVENTS":
            fetch_recent_events($data, $connection);
            break;
        case "FETCH_RECENT_AGREEMENTS":
            fetch_recent_agreements($data, $connection);
            break;
        case "FETCH_ADDRESS":
            fetch_address($data, $connection);
            break;
        default:
            echo json_encode("ACTION_ERROR");
    }
    $connection->close();
}



