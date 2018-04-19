<?php

function fetch_logistics_agreement_info ($data, $connection) {
    $sql = "SELECT agreements.*, terms.* FROM agreements
            INNER JOIN terms ON agreements.terms_id = terms.terms_id
            WHERE agreements.agreement_id = '$data->agreement_id'";
    $result = $connection->query($sql);
    $row = mysqli_fetch_array($result);
    echo json_encode($row);
}

function check_return ($data, $connection) {
    $returning = "TRANSFER";
    $sql = "SELECT logistics_simulation.*, agreements.* FROM logistics_simulation 
            INNER JOIN agreements ON logistics_simulation.agreement_id = agreements.agreement_id WHERE logistics_simulation.agreement_id = '$data->agreement_id'";
    $result = $connection->query($sql);
    if ($result->num_rows == 2) {
        $returning = "RETURN";
    }
    else if ($result->num_rows == 1) {
        $row = mysqli_fetch_array($result);
        if ($row['state'] == 'REJECTED') {
            $returning = "RETURN";
        }
    }
    echo json_encode($returning);
}

function fetch_seller ($data, $connection) {
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
    $row = mysqli_fetch_array($result);
    echo json_encode($row);
}

function fetch_buyer ($data, $connection) {
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
    $row = mysqli_fetch_array($result);
    echo json_encode($row);
}

function initiate_logistics ($data, $connection) {
    $sql = "INSERT INTO logistics_simulation (kolli_id, agreement_id, cost, weight, direction)
            VALUES ('$data->kolli_id', '$data->agreement_id', '$data->logistics_cost', '$data->item_weight', '$data->direction')";
    $connection->query($sql);
    assign_sensors($data, $connection);
    $sql = "SELECT sensor_id FROM sensors WHERE kolli_id = '$data->kolli_id'";
    $result = $connection->query($sql);
    echo json_encode(mysqli_num_rows($result));
}

function assign_sensors ($data, $connection) {
    if (isset ($data->acc_sensor_id)) {
        $sql = "INSERT INTO sensors (sensor_id, kolli_id, sensor_type)
            VALUES ('$data->acc_sensor_id', '$data->kolli_id', 'ACC')";
        $connection->query($sql);
    }
    if (isset ($data->temp_sensor_id)) {
        $sql = "INSERT INTO sensors (sensor_id, kolli_id, sensor_type)
            VALUES ('$data->temp_sensor_id', '$data->kolli_id', 'TEMP')";
        $connection->query($sql);
    }
    if (isset ($data->humid_sensor_id)) {
        $sql = "INSERT INTO sensors (sensor_id, kolli_id, sensor_type)
            VALUES ('$data->humid_sensor_id', '$data->kolli_id', 'HUMID')";
        $connection->query($sql);
    }
    if (isset ($data->pres_sensor_id)) {
        $sql = "INSERT INTO sensors (sensor_id, kolli_id, sensor_type)
            VALUES ('$data->pres_sensor_id', '$data->kolli_id', 'PRES')";
        $connection->query($sql);
    }
    if (isset ($data->gps_sensor_id)) {
        $sql = "INSERT INTO sensors (sensor_id, kolli_id, sensor_type)
            VALUES ('$data->gps_sensor_id', '$data->kolli_id', 'GPS')";
        $connection->query($sql);
        $sql = "INSERT INTO gps_data (sensor_id, latitude, longitude) VALUES ('$data->gps_sensor_id', NULL, NULL)";
        $connection->query($sql);
    }
}

function fetch_simulation_sensors ($data, $connection) {
    $response = array();
    $sql = "SELECT *  FROM sensors WHERE kolli_id = '$data->kolli_id'";
    $result = $connection->query($sql);
    while($row = mysqli_fetch_array($result)) {
        $response[] = $row;
    }
    echo json_encode($response);
}

function fetch_simulation_thresholds ($data, $connection) {
    $sql = "SELECT * FROM terms WHERE agreement_id = '$data->agreement_id' AND status = 'ACCEPTED'";
    $result = $connection->query($sql);
    $row = mysqli_fetch_array($result);
    echo json_encode($row);
}

function insert_sensor_data ($data, $connection) {
    if (isset($data->gps_id)) {
        $sql = "UPDATE gps_data SET latitude = '$data->lat', longitude = '$data->lng' WHERE sensor_id = '$data->gps_id'";
    } else {
        $sql = "INSERT INTO sensor_data (sensor_id, output, server_timestamp) VALUES ('$data->id', '$data->output', NOW())";
    }
    $connection->query($sql);
}

function fetch_logistics_parameters ($data, $connection) {
    $sql = "SELECT * FROM logistics_simulation WHERE agreement_id = '$data->agreement_id' AND direction = '$data->direction'";
    $result = $connection->query($sql);
    $row = mysqli_fetch_array($result);
    echo json_encode($row);
}

function fetch_sensor_data ($data, $connection) {
    $response = array();
    if (isset($data->sensor_id)) {
        $sql = "SELECT * FROM sensor_data WHERE sensor_id = '$data->sensor_id' ORDER BY server_timestamp ASC";
    } else if (isset($data->gps_id)) {
        $sql = "SELECT * FROM gps_data WHERE sensor_id = '$data->gps_id'";
    }
    $result = $connection->query($sql);
    while($row = mysqli_fetch_array($result)) {
        $response[] = $row;
    }
    echo json_encode($response);
}

function violate_agreement ($data, $connection) {
    $sql = "UPDATE agreements SET violation = TRUE WHERE agreement_id = '$data->agreement_id'";
    $connection->query($sql);

    //Add event to the database
    $event_payload = new stdClass();
    $event_payload->agreement_id = $data->agreement_id;
    generate_event($event_payload, $data->event_timestamp, "VIOLATE", $connection);
}

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
        default:
            echo json_encode('ERROR');
            break;
    }
    generate_event($event_payload, $data->event_timestamp, $event_type, $connection);
}