<?php
function generate_event($payload, $timestamp, $event_type, $connection) {
    $event_id = generateEventIdentifier(40);
    $payload_string = json_encode($payload);
    $sql = "INSERT INTO agreement_events (event_id, event_type, event_payload, timestamp) VALUES ('$event_id', '$event_type', '$payload_string', $timestamp)";
    $connection->query($sql);
    echo json_encode($sql);
}