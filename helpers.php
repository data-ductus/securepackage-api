<?php
function parseJSON($data) {
    foreach($data as &$field) {
        if ($field === null) {
            $field = 'NULL';
        }
        else if ($field === false) {
            $field = 0;
        }
        else if ($field === true) {
            $field = 1;
        }
    }
    return $data;
}

function checkQuery ($query) {
    if ($query) {
        return "Query success (" . mysqli_affected_rows($query) . ")";
    }
    else {
        return "Query failed";
    }
}