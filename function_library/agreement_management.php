<?php
/**
 * function_library/agreement_management.php
 *
 * Contains functions, which are used for agreement management.
 */

/**
 * Creates an agreement, appends the agreement and initial terms to the database.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function create_agreement($data, $connection) {
    //Parse boolean values in the JSON HTTP array
    $data = parseJSON($data);

    //Insert agreement
    $sql = "INSERT INTO agreements (agreement_id, seller_id, state, date_created, terms_id) 
            VALUES ('$data->id', '$data->account', 'CREATED', $data->time, '$data->terms_id')";
    $connection->query($sql);

    //Insert initial terms
    $sql = "INSERT INTO terms (terms_id, agreement_id, status, price, postage_time, author_account, accelerometer, 
                              pressure_low, pressure_high, humidity_low, humidity_high, temperature_low, temperature_high, gps) 
            VALUES ('$data->terms_id', '$data->id', 'INITIAL', '$data->terms_price', '$data->terms_shipmenttime', '$data->account', 
                    $data->sensor_accelerometer, $data->sensor_pressure_low, $data->sensor_pressure_high, 
                    $data->sensor_humidity_low, $data->sensor_humidity_high, $data->sensor_temperature_low, $data->sensor_temperature_high,
                    $data->sensor_gps)";
    $connection->query($sql);

    //Insert the item details
    $sql = "INSERT INTO items (agreement_id, title, description) VALUES ('$data->id', '$data->title', '$data->description')";
    $connection->query($sql);

    //Insert images
    $sql = "INSERT INTO images (image_id, item_id, image) VALUES ('$data->image_id', '$data->id', '$data->image')";
    $connection->query($sql);

    //Add event to the database
    $event_payload = new stdClass();
    $event_payload->agreement_id = $data->id;
    $event_payload->author_account = $data->account;
    $event_payload->terms_id = $data->terms_id;
    generate_event($event_payload, $data->event_timestamp, "CREATE", $connection);
}

/**
 * Fetches agreements, according to a search criteria
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function fetch_agreements($data, $connection) {
    $response = array();
    $sql = "SELECT agreements.*, items.title, terms.price, images.image FROM agreements
                INNER JOIN items ON agreements.agreement_id = items.agreement_id
                INNER JOIN terms ON agreements.agreement_id = terms.agreement_id AND terms.terms_id = agreements.terms_id
                INNER JOIN images ON agreements.agreement_id = images.item_id";
    if (isset ($data->user_search)) {
        $sql .= " WHERE agreements.seller_id = '$data->user_search' AND agreements.state != 'INACTIVE'";
    }
    if (isset ($data->status)) {
        $sql .= " AND agreements.state = '$data->status'";
    }
    if (isset ($data->buyer_search)) {
        $sql .= " WHERE agreements.buyer_id = '$data->buyer_search'";
    }
    $result = $connection->query($sql);
    while($row = mysqli_fetch_array($result)) { $response[] = $row; }
    http_response($response);
}

/**
 * Fetches an agreements and its details.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function fetch_agreement ($data, $connection) {
    $sql = "SELECT * FROM agreements WHERE agreement_id='$data->id'";
    $result = $connection->query($sql);
    $response = mysqli_fetch_array($result);
    http_response($response);
}

/**
 * Fetches terms of an agreement, according to status criteria.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function fetch_agreement_terms ($data, $connection) {
    $response = array();
    $sql = "SELECT * FROM terms WHERE agreement_id='$data->id'";
    if (isset($data->status)) {
        $sql .= " AND status = '$data->status'";
    }
    $result = $connection->query($sql);
    while($row = mysqli_fetch_array($result)) { $response[] = $row; }
    http_response($response);
}

/**
 * Fetches item information of an agreement.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function fetch_agreement_item ($data, $connection) {
    $sql = "SELECT * FROM items WHERE agreement_id='$data->id'";
    $result = $connection->query($sql);
    $response = mysqli_fetch_array($result);
    http_response($response);
}

/**
 * Fetches images of agreement's item.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function fetch_agreement_images ($data, $connection) {
    $response = array();
    $sql = "SELECT image FROM images WHERE item_id='$data->id'";
    $result = $connection->query($sql);
    while($row = mysqli_fetch_array($result)) { $response[] = $row; }
    http_response($response);
}

/**
 * Inactivates an agreement and its terms.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function inactivate_agreement ($data, $connection) {
    $sql = "UPDATE agreements SET state = 'INACTIVE' WHERE agreement_id = '$data->agreement'";
    $connection->query($sql);
    $sql = "UPDATE terms SET status = 'INACTIVE' WHERE agreement_id = '$data->agreement'";
    $connection->query($sql);

    //Add event to the database
    $event_payload = new stdClass();
    $event_payload->agreement_id = $data->agreement;
    generate_event($event_payload, $data->event_timestamp, "S_ABORT", $connection);
}

/**
 * Alters state of an agreement in other case then removal, approval of terms and creating of the agreement.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function alter_agreement_state ($data, $connection) {
    $sql = "UPDATE agreements SET state = '$data->state' WHERE agreement_id = '$data->agreement_id'";
    $connection->query($sql);

    //Add event to the database
    $event_type = "";
    $event_payload = new stdClass();
    $event_payload->agreement_id = $data->agreement_id;
    switch ($data->state) {
        case 'TRANSIT':
            $event_type = "S_POST";
            break;
        case 'DELIVERED':
            $event_type = "B_DELIVER";
            break;
        case 'COMPLETE':
            $event_type = "B_APPROVE";
            break;
        case 'REJECTED':
            $event_type = "B_REJECT";
            break;
        case 'RETURN':
            $event_type = "B_POST";
            break;
        case 'RETURNED':
            $event_type = "S_DELIVER";
            break;
        case 'CLERK':
            $event_type = "S_REJECT";
            break;
        case 'INACTIVE':
            //Inactivate terms and the agreement
            $event_type = "S_APPROVE";
            $sql = "UPDATE terms SET status = 'INACTIVE' WHERE agreement_id = '$data->agreement_id' AND status = 'DENIED'";
            $connection->query($sql);
            break;
        default:
            http_response("STATE_ERROR");
            break;
    }
    generate_event($event_payload, $data->event_timestamp, $event_type, $connection);
}