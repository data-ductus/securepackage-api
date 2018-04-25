<?php
/**
 * helpers/http_handler.php
 *
 * Contains functions, which are used for handling HTTP calls from the application.
 */

/**
 * Handles the HTTP request.
 *
 * @param $data - Payload of the HTTP request.
 */
function handle_http_call($data) {
    $connection = db_connect();
    switch ($data->action) {
        case "NEW_ACCOUNT":
            generate_new_account($data, $connection);
            break;
        case "LOGIN":
            login($data, $connection);
            break;
        case "FETCH_AGREEMENTS":
            fetch_agreements($data, $connection);
            break;
        case "NEW_ITEM":
            create_agreement($data, $connection);
            break;
        case "FETCH_AGREEMENT_INFO":
            fetch_agreement($data, $connection);
            break;
        case "FETCH_AGREEMENT_TERMS":
            fetch_agreement_terms($data, $connection);
            break;
        case "FETCH_AGREEMENT_ITEM":
            fetch_agreement_item($data, $connection);
            break;
        case "FETCH_AGREEMENT_IMAGES":
            fetch_agreement_images($data, $connection);
            break;
        case "FETCH_CURRENT_AGREEMENT_TERMS":
            fetch_current_agreement_terms($data, $connection);
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
            inactivate_proposal($data, $connection);
            break;
        case "REJECT_PROPOSAL":
            reject_proposal($data, $connection);
            break;
        case "ACCEPT_PROPOSAL":
            accept_proposal($data, $connection);
            break;
        case "REMOVE_ITEM":
            inactivate_agreement($data, $connection);
            break;
        case "FETCH_LOGISTICS_INFO":
            fetch_logistics_agreement_info($data, $connection);
            break;
        case "FETCH_LOGISTICS_SELLER":
            fetch_sender($data, $connection);
            break;
        case "FETCH_LOGISTICS_BUYER":
            fetch_receiver($data, $connection);
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
            fetch_recent_events($connection);
            break;
        case "FETCH_RECENT_AGREEMENTS":
            fetch_recent_agreements($connection);
            break;
        case "FETCH_ADDRESS":
            fetch_address($data, $connection);
            break;
        case "FETCH_ADDRESS_EVENTS":
            fetch_address_events($data, $connection);
            break;
        case "CLERK_LOGIN":
            login_clerk($data, $connection);
            break;
        case "FETCH_CLERK_AGREEMENTS":
            fetch_clerk_agreements($connection);
            break;
        case "FETCH_RESOLVED_AGREEMENTS":
            fetch_resolved_agreements($connection);
            break;
        case "FETCH_ACCOUNT":
            fetch_account($data, $connection);
            break;
        case "FETCH_ACCOUNT_HISTORY":
            fetch_account_history($data, $connection);
            break;
        case "RESOLVE_CONFLICT":
            resolve_conflict($data, $connection);
            break;
        case "FETCH_CLERK_DECISION":
            fetch_clerk_decision($data, $connection);
            break;
        case "CONFIRM_CLERK_DECISION":
            confirm_clerk_decision($data, $connection);
            break;
        default:
            http_response("ACTION_ERROR");
    }
    $connection->close();
}

/**
 * Echoes HTTP response back to the application.
 *
 * @param $payload - Response payload.
 */
function http_response($payload) {
    echo json_encode($payload);
}