<?php
/**
 * function_library/proposal_management.php
 *
 * Contains functions, which are used for proposal management
 */

/**
 * Proposes new terms for a given agreement.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function propose_terms ($data, $connection) {
    $data = parseJSON($data);
    $sql = "INSERT INTO terms (terms_id, agreement_id, status, price, postage_time, author_account, accelerometer, 
                              pressure_low, pressure_high, humidity_low, humidity_high, temperature_low, temperature_high, gps) 
            VALUES ('$data->terms_id', '$data->agreement_id', 'PROPOSED', '$data->terms_price', '$data->terms_shipmenttime', '$data->account', 
                    $data->sensor_accelerometer, $data->sensor_pressure_low, $data->sensor_pressure_high, 
                    $data->sensor_humidity_low, $data->sensor_humidity_high, $data->sensor_temperature_low, $data->sensor_temperature_high,
                    $data->sensor_gps)";
    $connection->query($sql);

    //Add event to the database
    $event_payload = new stdClass();
    $event_payload->terms_id = $data->terms_id;
    $event_payload->agreement_id = $data->agreement_id;
    $event_payload->author_account = $data->account;
    generate_event($event_payload, $data->event_timestamp, "PROPOSE", $connection);
}

/**
 * Displays proposals of a given account.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function display_user_proposals ($data, $connection) {
    $response = array();
    $sql = "SELECT terms.*, agreements.seller_id, items.title FROM terms
            INNER JOIN agreements ON terms.agreement_id = agreements.agreement_id
            INNER JOIN items ON terms.agreement_id = items.agreement_id 
            WHERE author_account='$data->user_search' AND status!='INITIAL'";
    $result = $connection->query($sql);
    while($row = mysqli_fetch_array($result)) { $response[] = $row; }
    http_response($response);
}

/**
 * Inactivates a given proposal.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function inactivate_proposal ($data, $connection) {
    $sql = "UPDATE terms SET status = 'INACTIVE' WHERE terms_id = '$data->terms'";
    $connection->query($sql);
}

/**
 * Rejects/denies a given proposal.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function reject_proposal ($data, $connection) {
    $sql = "SELECT agreement_id FROM terms WHERE terms_id = '$data->terms'";
    $result = $connection->query($sql);
    $row = mysqli_fetch_array($result);
    $agreement_id = $row['agreement_id'];
    $sql = "UPDATE terms SET status = 'DENIED' WHERE terms_id = '$data->terms'";
    $connection->query($sql);

    //Add event to the database
    $event_payload = new stdClass();
    $event_payload->terms_id = $data->terms;
    $event_payload->agreement_id = $agreement_id;
    generate_event($event_payload, $data->event_timestamp, "DECLINE", $connection);
}

/**
 * Accepts a given proposal by changing state of the agreement, assigning accepted terms and rejecting other related terms.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function accept_proposal ($data, $connection) {
    $sql = "UPDATE terms SET status = 'ACCEPTED' WHERE terms_id = '$data->terms'";
    $connection->query($sql);
    $sql = "UPDATE terms SET status = 'DENIED' WHERE agreement_id = '$data->agreement' AND NOT terms_id = '$data->terms' AND NOT status = 'INITIAL'";
    $connection->query($sql);
    $sql = "UPDATE agreements SET state = 'LOCKED', terms_id = '$data->terms', date_locked = $data->time, buyer_id = '$data->buyer' WHERE agreement_id = '$data->agreement'";
    $connection->query($sql);

    //Add event to the database
    $event_payload = new stdClass();
    $event_payload->terms_id = $data->terms;
    $event_payload->agreement_id = $data->agreement;
    $event_payload->buyer_id = $data->buyer;
    generate_event($event_payload, $data->event_timestamp, "ACCEPT", $connection);
}