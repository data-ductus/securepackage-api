<?php

include('helpers.php');

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
    echo json_encode($sql);
    $connection->query($sql);
    $sql = "INSERT INTO items (agreement_id, title, description) VALUES ('$data->id', '$data->title', '$data->description')";
    $connection->query($sql);
    $sql = "INSERT INTO images (image_id, item_id, image) VALUES ('$data->image_id', '$data->id', '$data->image')";
    $connection->query($sql);
}

/*function propose_terms($data, $connection) {
    $sql = "INSERT INTO terms (terms_id, agreement_id, status, item_id, price, postage_time, pressure, humidity, temperature, gps, accelerometer, author_account) 
            VALUES ('$data->terms_id', '$data->agreement_id', 'PROPOSED', '$data->item_id', '$data->price', '$data->postage_time', '$data->pressure',
                    '$data->humidity', '$data->temperature', '$data->gps', '$data->accelerometer', '$data->account_id')";
    $connection->query($sql);
}*/

function accept_terms($data, $connection) {
    $sql = "UPDATE terms SET status = 'ACCEPTED' WHERE terms_id = '$data->terms_id'";
    $connection->query($sql);
    $sql = "UPDATE agreements SET state = 'LOCKED', terms_id = '$data->terms_id'";
    $connection->query($sql);
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
    if ($connection->query($sql)) {
        echo json_encode("SUCCESS");
    } else {
        echo json_encode("FAILURE");
    }
}

function accept_proposal ($data, $connection) {
    $sql = "UPDATE terms SET status = 'ACCEPTED' WHERE terms_id = '$data->terms'";
    $connection->query($sql);
    $sql = "UPDATE terms SET status = 'DENIED' WHERE agreement_id = '$data->agreement' AND NOT terms_id = '$data->terms' AND NOT status = 'INITIAL'";
    $connection->query($sql);
    $sql = "UPDATE agreements SET state = 'LOCKED', terms_id = '$data->terms', date_locked = $data->time, buyer_id = '$data->buyer' WHERE agreement_id = '$data->agreement'";
    $connection->query($sql);
    echo json_encode($data);
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
}