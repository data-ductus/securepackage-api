<?php

function generate_new_account($data, $connection) {
    $sql = "INSERT INTO accounts (account_id, public_key, pass, full_name) 
                  VALUES ('$data->address', '$data->public_key', '$data->password', '$data->name')";
    $connection->query($sql);
}

function update_user_details($data, $connection) {
    $sql = "UPDATE accounts SET street_address='$data->street_address', city='$data->city', postcode='$data->postcode' WHERE account_id = '$data->account_id'";
    $connection->query($sql);
}

function login($data, $connection) {
    $response = new stdClass();
    $sql = "SELECT * FROM accounts WHERE account_id = '$data->address' AND pass = '$data->password'";
    $result = $connection->query($sql);
    if ($result->num_rows == 1) {
        $row = mysqli_fetch_array($result);
        $response->status = "LOGIN_SUCCESS";
        $response->address = $row['account_id'];
        $response->name = $row['full_name'];
        $response->street_address = $row['street_address'];
        $response->city = $row['city'];
        $response->postcode = $row['postcode'];
        echo json_encode($response);
    } else {
        $response->status = "LOGIN_FAIL";
        echo json_encode($response);
    }
}

function add_new_item($data, $connection) {
    //Parse boolean values in the JSON HTTP array
    $data = parseJSON($data);

    $sql = "INSERT INTO agreements (agreement_id, seller_id, state, date_created, terms_id) 
            VALUES ('$data->id', '$data->account', 'CREATED', $data->time, '$data->terms_id')";
    $connection->query($sql);

    $sql = "INSERT INTO terms (terms_id, agreement_id, status, price, postage_time, author_account, accelerometer, 
                              pressure_low, pressure_high, humidity_low, humidity_high, temperature_low, temperature_high, gps) 
            VALUES ('$data->terms_id', '$data->id', 'INITIAL', '$data->terms_price', '$data->terms_shipmenttime', '$data->account', 
                    $data->sensor_accelerometer, $data->sensor_pressure_low, $data->sensor_pressure_high, 
                    $data->sensor_humidity_low, $data->sensor_humidity_high, $data->sensor_temperature_low, $data->sensor_temperature_high,
                    $data->sensor_gps)";
    $connection->query($sql);

    $sql = "INSERT INTO items (agreement_id, title, description) VALUES ('$data->id', '$data->title', '$data->description')";
    $connection->query($sql);

    $sql = "INSERT INTO images (image_id, item_id, image) VALUES ('$data->image_id', '$data->id', '$data->image')";
    $connection->query($sql);

    //Add event to the database
    $event_payload = new stdClass();
    $event_payload->agreement_id = $data->id;
    $event_payload->author_account = $data->account;
    $event_payload->terms_id = $data->terms_id;
    generate_event($event_payload, $data->event_timestamp, "CREATE", $connection);
}

function display_agreements($data, $connection) {
    $response = array();
    $sql = "SELECT agreements.*, items.title, terms.price, images.image FROM agreements
                INNER JOIN items ON agreements.agreement_id = items.agreement_id
                INNER JOIN terms ON agreements.agreement_id = terms.agreement_id AND terms.terms_id = agreements.terms_id
                INNER JOIN images ON agreements.agreement_id = images.item_id";
    if (isset ($data->user_search)) {
        $sql .= " WHERE agreements.seller_id = '$data->user_search'";
    }
    if (isset ($data->status)) {
        //echo json_encode("XD");
        $sql .= " AND agreements.state = '$data->status'";
    }
    if (isset ($data->buyer_search)) {
        $sql .= " WHERE agreements.buyer_id = '$data->buyer_search'";
    }
    $result = $connection->query($sql);
    while($row = mysqli_fetch_array($result)) {
        $response[] = $row;
    }
    echo json_encode($response);
}

function display_agreement ($data, $connection) {
    $sql = "SELECT * FROM agreements WHERE agreement_id='$data->id'";
    $result = $connection->query($sql);
    $row = mysqli_fetch_array($result);
    echo json_encode($row);
}

function display_agreement_terms ($data, $connection) {
    $response = array();
    $sql = "SELECT * FROM terms WHERE agreement_id='$data->id'";
    if (isset($data->status)) {
        $sql .= " AND status = '$data->status'";
    }

    $result = $connection->query($sql);
    while($row = mysqli_fetch_array($result)) {
        $response[] = $row;
    }
    echo json_encode($response);
}

function display_agreement_item ($data, $connection) {
    $sql = "SELECT * FROM items WHERE agreement_id='$data->id'";
    $result = $connection->query($sql);
    $row = mysqli_fetch_array($result);
    echo json_encode($row);
}

function display_agreement_images ($data, $connection) {
    $response = array();
    $sql = "SELECT image FROM images WHERE item_id='$data->id'";
    $result = $connection->query($sql);
    while($row = mysqli_fetch_array($result)) {
        $response[] = $row;
    }
    echo json_encode($response);
}

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

function display_user_proposals ($data, $connection) {
    $response = array();
    $sql = "SELECT terms.*, agreements.seller_id, items.title FROM terms
            INNER JOIN agreements ON terms.agreement_id = agreements.agreement_id
            INNER JOIN items ON terms.agreement_id = items.agreement_id 
            WHERE author_account='$data->user_search' AND status!='INITIAL'";
    $result = $connection->query($sql);
    while($row = mysqli_fetch_array($result)) {
        $response[] = $row;
    }
    echo json_encode($response);
}

function remove_proposal ($data, $connection) {
    $sql = "DELETE FROM terms WHERE terms_id = '$data->terms'";
    if ($connection->query($sql)) {
        echo json_encode("SUCCESS");
    } else {
        echo json_encode("FAILURE");
    }
}

function reject_proposal ($data, $connection) {
    $sql = "UPDATE terms SET status = 'DENIED' WHERE terms_id = '$data->terms'";
    $connection->query($sql);

    //Add event to the database
    $event_payload = new stdClass();
    $event_payload->terms_id = $data->terms;
    generate_event($event_payload, $data->event_timestamp, "DECLINE", $connection);
}

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

function remove_item ($data, $connection) {
    $sql = "DELETE FROM images WHERE item_id = '$data->agreement'";
    $connection->query($sql);
    $sql = "DELETE FROM items WHERE agreement_id = '$data->agreement'";
    $connection->query($sql);
    $sql = "DELETE FROM terms WHERE agreement_id = '$data->agreement'";
    $connection->query($sql);
    $sql = "DELETE FROM agreements WHERE agreement_id = '$data->agreement'";
    $connection->query($sql);

    $event_payload = new stdClass();
    $event_payload->agreement_id = $data->agreement;

    //Add event to the database
    generate_event($event_payload, $data->event_timestamp, "S_ABORT", $connection);

}

function fetch_recent_events ($data, $connection) {
    $response = array();
    $sql = "SELECT event_id, event_type, timestamp FROM agreement_events ORDER BY timestamp DESC LIMIT 10";
    $result = $connection->query($sql);
    while($row = mysqli_fetch_array($result)) {
        $response[] = $row;
    }
    echo json_encode($response);
}

function fetch_recent_agreements ($data, $connection) {
    $response = array();
    $sql = "SELECT agreement_id, state, date_created FROM agreements LIMIT 10";
    $result = $connection->query($sql);
    while($row = mysqli_fetch_array($result)) {
        $response[] = $row;
    }
    echo json_encode($response);
}

function fetch_address ($data, $connection) {
    $response = new stdClass();

    $sql = "SELECT * FROM agreement_events WHERE event_id = '$data->id'";
    $result = $connection->query($sql);
    if ($result->num_rows == 1) {
        $row = mysqli_fetch_array($result);
        $event_payload = new stdClass();
        $event_payload->type = $row["event_type"];
        $event_payload->timestamp = $row["timestamp"];
        $event_payload->payload = json_decode($row["event_payload"]);
        $response->payload = $event_payload;
        $response->address_type = "EVENT";
        echo json_encode($response);
        return;
    }

    $sql = "SELECT account_id, public_key FROM accounts WHERE account_id = '$data->id'";
    $result = $connection->query($sql);
    if ($result->num_rows == 1) {
        $payload = new stdClass();
        $row = mysqli_fetch_array($result);
        $payload->account_id = $row["account_id"];
        $payload->public_key = $row["public_key"];
        $response->payload = $payload;
        $response->address_type = "ACCOUNT";
        echo json_encode($response);
        return;
    }

    $sql = "SELECT account_id, public_key FROM accounts WHERE account_id = '$data->id'";
    $result = $connection->query($sql);
    if ($result->num_rows == 1) {
        $payload = new stdClass();
        $row = mysqli_fetch_array($result);
        $payload->account_id = $row["account_id"];
        $payload->public_key = $row["public_key"];
        $response->payload = $payload;
        $response->address_type = "ACCOUNT";
        echo json_encode($response);
        return;
    }

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

        $sql = "SELECT * FROM items WHERE agreement_id = '$data->id'";
        $result = $connection->query($sql);
        if ($result->num_rows == 1) {
            $item_payload = new stdClass();
            $row = mysqli_fetch_array($result);
            $item_payload->title = $row["title"];
            $item_payload->description = $row["description"];

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
        echo json_encode($response);
        return;
    }

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
        echo json_encode($response);
        return;
    }

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
        echo json_encode($response);
        return;
    }
    $response->payload = "";
    $response->address_type = null;

    echo json_encode($response);
}