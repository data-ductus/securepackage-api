<?php
/**
 * function_library/explorer_management.php
 *
 * Contains functions, which are used for exploration functionality management.
 */

/**
 * Fetches 10 recent events.
 *
 * @param $connection - Database connection.
 */
function fetch_recent_events ($connection) {
    $response = array();
    $sql = "SELECT event_id, event_type, timestamp, target_agreement FROM agreement_events ORDER BY timestamp DESC LIMIT 10";
    $result = $connection->query($sql);
    while($row = mysqli_fetch_array($result)) { $response[] = $row; }
    http_response($response);
}

/**
 * Fetches 10 recent agreements.
 *
 * @param $connection - Database connection.
 */
function fetch_recent_agreements ($connection) {
    $response = array();
    $sql = "SELECT agreement_id, state, date_created FROM agreements ORDER BY date_created DESC LIMIT 10";
    $result = $connection->query($sql);
    while($row = mysqli_fetch_array($result)) { $response[] = $row; }
    http_response($response);
}

/**
 * Fetches address parameters and information. Performs search of events, account, agreements and terms in that order.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function fetch_address ($data, $connection) {
    $response = new stdClass();

    //Search for events
    $sql = "SELECT * FROM agreement_events WHERE event_id = '$data->id'";
    $result = $connection->query($sql);
    if ($result->num_rows == 1) {
        $row = mysqli_fetch_array($result);
        $event_payload = new stdClass();
        $event_payload->event_id = $row["event_id"];
        $event_payload->type = $row["event_type"];
        $event_payload->timestamp = $row["timestamp"];
        $event_payload->payload = json_decode($row["event_payload"]);
        $response->payload = $event_payload;
        $response->address_type = "EVENT";
        http_response($response);
        return;
    }

    //Search for accounts
    $sql = "SELECT account_id, public_key FROM accounts WHERE account_id = '$data->id'";
    $result = $connection->query($sql);
    if ($result->num_rows == 1) {
        $payload = new stdClass();
        $row = mysqli_fetch_array($result);
        $payload->account_id = $row["account_id"];
        $payload->public_key = $row["public_key"];
        $response->payload = $payload;
        $response->address_type = "ACCOUNT";
        http_response($response);
        return;
    }

    //Search for agreements
    $sql = "SELECT * FROM agreements WHERE agreement_id = '$data->id'";
    $result = $connection->query($sql);
    if ($result->num_rows == 1) {
        $payload = new stdClass();
        $row = mysqli_fetch_array($result);
        $payload->agreement_id = $row["agreement_id"];
        $payload->seller_id = $row["seller_id"];
        $payload->buyer_id = $row["buyer_id"];
        $payload->state = $row["state"];
        $payload->violation = $row["violation"];
        $payload->terms_id = $row["terms_id"];
        $payload->date_created = $row["date_created"];
        $payload->date_locked = $row["date_locked"];

        //Add item information
        $sql = "SELECT * FROM items WHERE agreement_id = '$data->id'";
        $result = $connection->query($sql);
        if ($result->num_rows == 1) {
            $item_payload = new stdClass();
            $row = mysqli_fetch_array($result);
            $item_payload->title = $row["title"];
            $item_payload->description = $row["description"];

            //Add image information
            $images = array();
            $sql = "SELECT * FROM images WHERE item_id = '$data->id'";
            $result = $connection->query($sql);
            while($row = mysqli_fetch_array($result)) {
                $image_payload = new stdClass();
                $image_payload->image_id = $row["image_id"];
                $image_payload->image = $row["image"];
                $images[] = $image_payload;
            }
            $item_payload->images = $images;
            $payload->item = $item_payload;
        }

        //Add terms information
        $terms = array();
        $sql = "SELECT * FROM terms WHERE agreement_id = '$data->id'";
        $result = $connection->query($sql);
        while($row = mysqli_fetch_array($result)) {
            $terms_payload = new stdClass();
            $terms_payload->terms_id = $row["terms_id"];
            $terms_payload->author_account = $row["author_account"];
            $terms_payload->status = $row["status"];
            $terms_payload->price = $row["price"];
            $terms_payload->postage_time = $row["postage_time"];
            $terms_payload->accelerometer = $row["accelerometer"];
            $terms_payload->pressure_low = $row["pressure_low"];
            $terms_payload->pressure_high = $row["pressure_high"];
            $terms_payload->temperature_low = $row['temperature_low'];
            $terms_payload->temperature_high = $row['temperature_high'];
            $terms_payload->gps = $row['gps'];
            $terms[] = $terms_payload;
        }
        $payload->terms = $terms;

        $response->payload = $payload;
        $response->address_type = "AGREEMENT";
        http_response($response);
        return;
    }

    //Search for terms
    $sql = "SELECT * FROM terms WHERE terms_id = '$data->id'";
    $result = $connection->query($sql);
    if ($result->num_rows == 1) {
        $payload = new stdClass();
        $row = mysqli_fetch_array($result);
        $payload->terms_id = $row["terms_id"];
        $payload->agreement_id = $row["agreement_id"];
        $payload->author_account = $row["author_account"];
        $payload->status = $row["status"];
        $payload->price = $row["price"];
        $payload->postage_time = $row["postage_time"];
        $payload->accelerometer = $row["accelerometer"];
        $payload->pressure_low = $row["pressure_low"];
        $payload->pressure_high = $row["pressure_high"];
        $payload->temperature_low = $row['temperature_low'];
        $payload->temperature_high = $row['temperature_high'];
        $payload->gps = $row['gps'];
        $response->payload = $payload;
        $response->address_type = "TERMS";
        http_response($response);
        return;
    }

    //Search for images
    $sql = "SELECT * FROM images WHERE image_id = '$data->id'";
    $result = $connection->query($sql);
    if ($result->num_rows == 1) {
        $payload = new stdClass();
        $row = mysqli_fetch_array($result);
        $payload->image_id = $row["image_id"];
        $payload->item_id = $row["item_id"];
        $payload->image = $row["image"];
        $response->payload = $payload;
        $response->address_type = "IMAGE";
        http_response($response);
        return;
    }

    //If address is undefined
    $response->payload = "";
    $response->address_type = null;
    http_response($response);
}

/**
 * Fetches events of the agreement address.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function fetch_address_events ($data, $connection) {
    $response = array();
    $sql = "SELECT * FROM agreement_events WHERE target_agreement = '$data->id' ORDER BY timestamp DESC";
    $result = $connection->query($sql);
    while($row = mysqli_fetch_array($result)) { $response[] = $row; }
    http_response($response);
}