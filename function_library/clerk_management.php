<?php
/**
 * function_library/clerk_management.php
 *
 * Contains functions, which are used for clerk authorization and action management.
 */

/**
 * Performs clerk login action.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function login_clerk($data, $connection) {
    $response = null;
    $sql = "SELECT * FROM clerk_accounts WHERE clerk_id = '$data->address' and password = '$data->password'";
    $result = $connection->query($sql);
    if ($result->num_rows == 1) {
        $response = "CLERK_LOGGED_IN";
    }
    http_response($response);
}

/**
 * Fetches agreements, that need clerk's attentiaon.
 *
 * @param $connection - Database connection.
 */
function fetch_clerk_agreements($connection) {
    $response = array();
    $sql = "SELECT agreements.agreement_id, agreements.violation, agreements.terms_id, agreement_events.timestamp FROM agreements
            INNER JOIN agreement_events ON agreements.agreement_id = agreement_events.target_agreement AND agreement_events.event_type = 'S_REJECT'
            WHERE agreements.state = 'CLERK' ORDER BY agreement_events.timestamp ASC";
    $result = $connection->query($sql);
    while($row = mysqli_fetch_array($result)) { $response[] = $row; }
    http_response($response);
}

/**
 * Fetches resolved agreements.
 *
 * @param $connection - Database connection.
 */
function fetch_resolved_agreements($connection) {
    $response = array();
    $sql = "SELECT * FROM agreement_events WHERE event_type = 'CLERK'";
    $result = $connection->query($sql);
    while($row = mysqli_fetch_array($result)) { $response[] = $row; }
    http_response($response);
}

/**
 * Resolves a conflict by sending a message and deciding who should be responsible for the conflict, generates CLERK event.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function resolve_conflict($data, $connection) {
    $sql = "INSERT INTO clerk_actions (agreement_id, liable_party, message, clerk_id) VALUES ('$data->id', '$data->liable_party', '$data->message', '$data->clerk_id')";
    $connection->query($sql);
    $sql = "UPDATE agreements SET state = 'RESOLVED' WHERE agreement_id = '$data->id'";
    $connection->query($sql);

    //Add event to the database
    $event_payload = new stdClass();
    $event_payload->clerk_id = $data->clerk_id;
    $event_payload->agreement_id = $data->id;
    $event_payload->liable_party = $data->liable_party;
    $event_payload->message = "%PROTECTED INFORMATION%";
    generate_event($event_payload, $data->event_timestamp, "CLERK", $connection);
}

/**
 * Fetches clerk decision.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function fetch_clerk_decision($data, $connection) {
    $sql = "SELECT * FROM clerk_actions WHERE agreement_id = '$data->id'";
    $result = $connection->query($sql);
    $response = mysqli_fetch_array($result);
    http_response($response);
}

/**
 * Confirms decision by a party.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function confirm_clerk_decision($data, $connection) {
    $sql = "SELECT buyer_id, seller_id FROM agreements WHERE agreement_id = '$data->agreement_id'";
    $result = $connection->query($sql);
    $row = mysqli_fetch_array($result);

    //Check whether seller or buyer did confirm the decision
    if ($data->account_id == $row['seller_id']) {
        $sql = "UPDATE clerk_actions SET seller_confirm = TRUE WHERE agreement_id = '$data->agreement_id'";
        $connection->query($sql);
    }
    else if ($data->account_id == $row['buyer_id']) {
        $sql = "UPDATE clerk_actions SET buyer_confirm = TRUE WHERE agreement_id = '$data->agreement_id'";
        $connection->query($sql);
    }

    //Inactivate the agreement in case both seller and buyer confirmed the decision
    $sql = "SELECT buyer_confirm, seller_confirm FROM clerk_actions WHERE agreement_id = '$data->agreement_id'";
    $result = $connection->query($sql);
    $row = mysqli_fetch_array($result);
    if($row["buyer_confirm"] == TRUE && $row["seller_confirm"] == TRUE) {
        inactivate_resolved_agreement($data, $connection);
    }
}

/**
 * Inactivates the resolved agreement and its terms.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function inactivate_resolved_agreement ($data, $connection) {
    $sql = "UPDATE agreements SET state = 'INACTIVE' WHERE agreement_id = '$data->agreement_id'";
    $connection->query($sql);
    $sql = "UPDATE terms SET status = 'INACTIVE' WHERE agreement_id = '$data->agreement_id'";
    $connection->query($sql);
}