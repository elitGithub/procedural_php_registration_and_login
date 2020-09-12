<?php

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

/**
 * @param $string
 * @return string
 */
function escape($string) {
    global $mysqli;
    return $mysqli->real_escape_string($string);
}

/**
 * @param $query
 * @return object
 */
function query($query) {
    global $mysqli;
    return confirm($mysqli->query($query));
}

/**
 * @param mysqli_result $result
 * @return mixed
 */
function fetchArray(mysqli_result $result) {
    return $result->fetch_array(MYSQLI_NUM);
}

function fetchAssoc($result) {
    return $result->fetch_array(MYSQLI_ASSOC);
}

/**
 * @param $result
 * @return object
 */
function confirm($result) {
    global $mysqli;
    if (mysqli_error($mysqli)) {
        die(mysqli_error($mysqli));
    }
    return $result;
}

/**
 * @param $result
 * @return mixed
 */
function countRows($result) {
    return $result->num_rows;
}
