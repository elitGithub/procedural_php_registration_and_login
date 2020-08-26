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
 * @return bool|mysqli_result
 */
function query($query) {
    global $mysqli;
    return $mysqli->query($query);
}

/**
 * @param mysqli_result $result
 * @return mixed
 */
function fetchArray(mysqli_result $result) {
    return $result->fetch_array(MYSQLI_NUM);
}

function fetchAssoc(mysqli_result $result) {
    return $result->fetch_array(MYSQLI_ASSOC);
}

/**
 * @param mysqli_result $result
 */
function confirm(mysqli_result $result) {
    if (!$result) {
        global $con;
        die(mysqli_error($con));
    }
}

function countRows(mysqli_result $result) {
    return $result->num_rows;
}
