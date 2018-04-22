<?php
/**
 * function_library/account_management.php
 *
 * Contains functions, which are used for account management.
 */

/**
 * Generates new account.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function generate_new_account($data, $connection) {
    $sql = "INSERT INTO accounts (account_id, public_key, pass, full_name) 
                  VALUES ('$data->address', '$data->public_key', '$data->password', '$data->name')";
    $connection->query($sql);
}

/**
 * Updates user details.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
function update_user_details($data, $connection) {
    $sql = "UPDATE accounts SET street_address='$data->street_address', city='$data->city', postcode='$data->postcode' WHERE account_id = '$data->account_id'";
    $connection->query($sql);
}

/**
 * Performs a login operation. Sends response status of the operation back to the server.
 *
 * @param $data - HTTP data, which was received from the server.
 * @param $connection - Database connection.
 */
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
    } else {
        $response->status = "LOGIN_FAIL";
    }
    http_response($response);
}