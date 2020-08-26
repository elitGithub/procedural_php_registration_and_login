<?php
ob_start();
session_start();

const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'login_db';

include 'db.php';
include 'is_email.php';
include 'functions.php';