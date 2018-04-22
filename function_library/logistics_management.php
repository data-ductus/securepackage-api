<?php
/**
 * function_library/logistics_management.php
 *
 * Contains functions, which are used for logistics management.
 */

/**
 * Fetches agreement info for logistics purposes.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function fetch_logistics_agreement_info ($data, $connection) {
    $sql = "SELECT agreements.*, terms.* FROM agreements
            INNER JOIN terms ON agreements.terms_id = terms.terms_id
            WHERE agreements.agreement_id = '$data->agreement_id'";
    $result = $connection->query($sql);
    $response = mysqli_fetch_array($result);
    http_response($response);
}

/**
 * Check what direction the parcel is heading (delivery or return).
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function check_return ($data, $connection) {
    $response = "TRANSFER";
    $sql = "SELECT logistics_simulation.*, agreements.* FROM logistics_simulation 
            INNER JOIN agreements ON logistics_simulation.agreement_id = agreements.agreement_id WHERE logistics_simulation.agreement_id = '$data->agreement_id'";
    $result = $connection->query($sql);
    if ($result->num_rows == 2) {
        $response = "RETURN";
    }
    else if ($result->num_rows == 1) {
        $row = mysqli_fetch_array($result);
        if ($row['state'] == 'REJECTED') {
            $response = "RETURN";
        }
    }
    http_response($response);
}

/**
 * Fetches sender of the package (depends on the parcel heading).
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function fetch_sender ($data, $connection) {
    if (isset ($data->agreement_id)) {
        if ($data->direction == 'TRANSFER') {
            $sql = "SELECT accounts.account_id, accounts.full_name, accounts.street_address, accounts.city, accounts.postcode FROM accounts
                INNER JOIN agreements ON accounts.account_id = agreements.seller_id
                WHERE agreements.agreement_id = '$data->agreement_id'";
        }
        else if ($data->direction == 'RETURN') {
            $sql = "SELECT accounts.account_id, accounts.full_name, accounts.street_address, accounts.city, accounts.postcode FROM accounts
                INNER JOIN agreements ON accounts.account_id = agreements.buyer_id
                WHERE agreements.agreement_id = '$data->agreement_id'";
        }

    } else {
        $sql = "SELECT account_id, full_name, street_address, city, postcode FROM accounts
                WHERE account_id = '$data->seller_id'";
    }
    $result = $connection->query($sql);
    $response = mysqli_fetch_array($result);
    http_response($response);
}

/**
 * Fetches receiver of the package (depends on the parcel heading).
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function fetch_receiver ($data, $connection) {
    if (isset ($data->agreement_id)) {
        if ($data->direction == 'TRANSFER') {
            $sql = "SELECT accounts.account_id, accounts.full_name, accounts.street_address, accounts.city, accounts.postcode FROM accounts
                INNER JOIN agreements ON accounts.account_id = agreements.buyer_id
                WHERE agreements.agreement_id = '$data->agreement_id'";
        }
        else if ($data->direction == 'RETURN') {
            $sql = "SELECT accounts.account_id, accounts.full_name, accounts.street_address, accounts.city, accounts.postcode FROM accounts
                INNER JOIN agreements ON accounts.account_id = agreements.seller_id
                WHERE agreements.agreement_id = '$data->agreement_id'";
        }
    } else {
        $sql = "SELECT account_id, full_name, street_address, city, postcode FROM accounts
            WHERE account_id = '$data->buyer_id'";
    }
    $result = $connection->query($sql);
    $response = mysqli_fetch_array($result);
    http_response($response);
}

/**
 * Initiates logistics process by changing state, assigning sensors and logistics ID (kolli ID).
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function initiate_logistics ($data, $connection) {
    $sql = "INSERT INTO logistics_simulation (kolli_id, agreement_id, cost, weight, direction)
            VALUES ('$data->kolli_id', '$data->agreement_id', '$data->logistics_cost', '$data->item_weight', '$data->direction')";
    $connection->query($sql);
    assign_sensors($data, $connection);
    $sql = "SELECT sensor_id FROM sensors WHERE kolli_id = '$data->kolli_id'";
    $result = $connection->query($sql);
    http_response(mysqli_num_rows($result));
}

/**
 * Assigns sensors to the logistics instance.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function assign_sensors ($data, $connection) {
    //Accelerometer
    if (isset ($data->acc_sensor_id)) {
        $sql = "INSERT INTO sensors (sensor_id, kolli_id, sensor_type)
            VALUES ('$data->acc_sensor_id', '$data->kolli_id', 'ACC')";
        $connection->query($sql);
    }
    //Temperature sensor
    if (isset ($data->temp_sensor_id)) {
        $sql = "INSERT INTO sensors (sensor_id, kolli_id, sensor_type)
            VALUES ('$data->temp_sensor_id', '$data->kolli_id', 'TEMP')";
        $connection->query($sql);
    }
    //Humidity sensor
    if (isset ($data->humid_sensor_id)) {
        $sql = "INSERT INTO sensors (sensor_id, kolli_id, sensor_type)
            VALUES ('$data->humid_sensor_id', '$data->kolli_id', 'HUMID')";
        $connection->query($sql);
    }
    //Pressure sensor
    if (isset ($data->pres_sensor_id)) {
        $sql = "INSERT INTO sensors (sensor_id, kolli_id, sensor_type)
            VALUES ('$data->pres_sensor_id', '$data->kolli_id', 'PRES')";
        $connection->query($sql);
    }
    //GPS sensor
    if (isset ($data->gps_sensor_id)) {
        $sql = "INSERT INTO sensors (sensor_id, kolli_id, sensor_type)
            VALUES ('$data->gps_sensor_id', '$data->kolli_id', 'GPS')";
        $connection->query($sql);
        $sql = "INSERT INTO gps_data (sensor_id, latitude, longitude) VALUES ('$data->gps_sensor_id', NULL, NULL)";
        $connection->query($sql);
    }
}

/**
 * Fetches sensors, associated with the logistics instance.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function fetch_simulation_sensors ($data, $connection) {
    $response = array();
    $sql = "SELECT *  FROM sensors WHERE kolli_id = '$data->kolli_id'";
    $result = $connection->query($sql);
    while($row = mysqli_fetch_array($result)) { $response[] = $row; }
    http_response($response);
}

/**
 * Fetches simulation sensor thresholds.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function fetch_simulation_thresholds ($data, $connection) {
    $sql = "SELECT * FROM terms WHERE agreement_id = '$data->agreement_id' AND status = 'ACCEPTED'";
    $result = $connection->query($sql);
    $response = mysqli_fetch_array($result);
    http_response($response);
}

/**
 * Inserts sensor data into the database
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function insert_sensor_data ($data, $connection) {
    if (isset($data->gps_id)) {
        $sql = "UPDATE gps_data SET latitude = '$data->lat', longitude = '$data->lng' WHERE sensor_id = '$data->gps_id'";
    } else {
        $sql = "INSERT INTO sensor_data (sensor_id, output, server_timestamp) VALUES ('$data->id', '$data->output', NOW())";
    }
    $connection->query($sql);
}

/**
 * Fetches general logistics parameters
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function fetch_logistics_parameters ($data, $connection) {
    $sql = "SELECT * FROM logistics_simulation WHERE agreement_id = '$data->agreement_id' AND direction = '$data->direction'";
    $result = $connection->query($sql);
    $response = mysqli_fetch_array($result);
    http_response($response);
}

/**
 * Fetches data of a give sensor, which is used for further plotting and visualization.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function fetch_sensor_data ($data, $connection) {
    $response = array();
    if (isset($data->sensor_id)) {
        $sql = "SELECT * FROM sensor_data WHERE sensor_id = '$data->sensor_id' ORDER BY server_timestamp ASC";
    } else if (isset($data->gps_id)) {
        $sql = "SELECT * FROM gps_data WHERE sensor_id = '$data->gps_id'";
    }
    $result = $connection->query($sql);
    while($row = mysqli_fetch_array($result)) { $response[] = $row; }
    http_response($response);
}

/**
 * Violates the agreement. Called when threshold of one of the sensors have been exceeded for the first time.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function violate_agreement ($data, $connection) {
    $sql = "UPDATE agreements SET violation = TRUE WHERE agreement_id = '$data->agreement_id'";
    $connection->query($sql);

    //Add event to the database
    $event_payload = new stdClass();
    $event_payload->agreement_id = $data->agreement_id;
    generate_event($event_payload, $data->event_timestamp, "VIOLATE", $connection);
}