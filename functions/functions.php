<?php

/**
 * @param $string
 * @return string
 */
function clean($string) {
    return urldecode(html_entity_decode(htmlentities(strip_tags($string))));
}

/**
 * @param string $location
 */
function redirect(string $location) {
    return header("Location: $location");
}

/**
 * @param string $location
 */
function jsRedirect(string $location) {
    echo "<script>window.parent.location.href = '{$location}'</script>";
}

/**
 * @param string $message
 */
function setMessage (string $message) {
    if (!empty($message)) {
        $_SESSION['message'] = $message;
    } else {
        $message = '';
    }
}

/**
 *
 */
function displayMessage() {
    if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    }
}

function emailAlreadyExists($email) {
    $sql = "SELECT id FROM users WHERE email = '{$email}';";
    $result = query($sql);
    if ($result && countRows($result) === 1) {
        return true;
    }
    return false;
}

function usernameAlreadyExists($userName) {
    $sql = "SELECT id FROM users WHERE username = '{$userName}';";
    $result = query($sql);
    if ($result && countRows($result) === 1) {
        return true;
    }
    return false;
}

/**
 * @return string
 */
function generateToken() {
    $token = $_SESSION['token'] = md5(uniqid(mt_rand(), true));
    return $token;
}

function transformKey(string $string) {
    return ucfirst(str_replace('_', ' ', $string));
}

function setValidationErrorMessage($errorMessage) {
    return "<div class='alert alert-danger' role='alert'><strong>Warning!</strong> {$errorMessage}</div>";
}

/**
 * validate $_POST data.
 */
function validateUserRegistration() {
    $errors = [];
    $minLength = 2;
    $maxLength = 20;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($_POST as $key => $value) {
            $formData[$key] = clean($value);
            $valueLength = strlen(clean($value));
            if ($valueLength < $minLength) {
                $key = transformKey($key);
                $errors[] = "Your {$key} cannot be less than {$minLength} characters!";
            }

            if ($key !== 'register_email' && $valueLength > $maxLength) {
                $key = transformKey($key);
                $errors[] = "Your {$key} cannot be more than {$maxLength} characters!";
            }
        }

        if ($formData['password'] !== $formData['confirm_password']) {
            $errors[] = "Password fields do not match";
        }

        if (!is_email($formData['register_email'])) {
            $errors[] = "Your email address is not valid. Please correct it and try again.";
        }

        if (emailAlreadyExists($formData['register_email'])) {
            $errors[] = "The email {$formData['register_email']} already exists, please try a different email address, or if you're already a member, please login.";
        }

        if (usernameAlreadyExists($formData['username'])) {
            $errors[] = "The Username {$formData['username']} already exists, please try a different username, or if you're already a member, please login.";
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo setValidationErrorMessage($error);
            }
        }

        if (empty($errors)) {
            registerUser($formData);
        }
    }
}

function registerUser($formData) {
    $formData = array_map('escape', $formData);
    extract($formData);
    $password = md5($password);
    $validation_code = md5($username.microtime());
    $query = "INSERT INTO users 
    (`email`, `first_name`, `last_name`, `username`, `password`, `validation_code`, `active`) 
    VALUES ('{$register_email}', '{$first_name}', '{$last_name}', '{$username}', '{$password}', '{$validation_code}', 1);";

    if (query($query)) {

    } else {
        echo setValidationErrorMessage('Error registering user, please contact system administration.');
    }
}