<?php
/**
 * function_library/event_management.php
 *
 * Contains functions, which are used for event management.
 */

/**
 * Generates an event, which is tied to an agreement.
 *
 * @param $payload - Payload of the event.
 * @param $timestamp - Application time of when event occured.
 * @param $event_type - Type of the event.
 * @param $connection - Database connection.
 */
function generate_event($payload, $timestamp, $event_type, $connection) {
    $event_id = generateEventIdentifier(40);
    $payload_string = json_encode($payload);
    $sql = "INSERT INTO agreement_events (event_id, event_type, event_payload, timestamp, target_agreement) VALUES ('$event_id', '$event_type', '$payload_string', $timestamp, '$payload->agreement_id')";
    $connection->query($sql);
    http_response($event_type);
}