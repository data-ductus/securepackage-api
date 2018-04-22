<?php
/**
 * helpers/helpers.php
 *
 * Contains helper functions (generators, parsers etc.).
 */

/**
 * Modifies JSON array by handling null and boolean values.
 *
 * @param $data - JSON array.
 * @return mixed - Modified JSON array.
 */
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

/**
 * Generates event identifier of a desired length.
 *
 * @param $length - Length of the desired hexadecimal identifier.
 * @return string - Event identifier.
 */
function generateEventIdentifier($length) {
    $characters = '0123456789abcdef';
    $charactersLength = strlen($characters);
    $identifier = '';
    for ($i = 0; $i < $length; $i++) {
        $identifier .= $characters[rand(0, $charactersLength - 1)];
    }
    return $identifier;
}