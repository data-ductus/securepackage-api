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

function generateEventIdentifier($length) {
    $characters = '0123456789abcdef';
    $charactersLength = strlen($characters);
    $identifier = '';
    for ($i = 0; $i < $length; $i++) {
        $identifier .= $characters[rand(0, $charactersLength - 1)];
    }
    return $identifier;
}