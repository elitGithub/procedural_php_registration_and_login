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

/**
 * @param $to
 * @param $subject
 * @param $message
 * @param $headers
 * @return bool
 */
function sendMail($to, $subject, $message, $headers) {
    return mail($to, $subject, $message, $headers);
}

/**
 * @param $email
 * @return bool
 */
function emailAlreadyExists($email) {
    $sql = "SELECT `id` FROM `users` WHERE `email` = '{$email}';";
    $result = query($sql);
    if ($result && countRows($result) === 1) {
        return true;
    }
    return false;
}

/**
 * @param $userName
 * @return bool
 */
function usernameAlreadyExists($userName) {
    $sql = "SELECT `id` FROM `users` WHERE `username` = '{$userName}';";
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

/**
 * @param string $string
 * @return string
 */
function transformKey(string $string) {
    return ucfirst(str_replace('_', ' ', $string));
}

/**
 * @param $errorMessage
 * @return string
 */
function setValidationErrorMessage($errorMessage) {
    return "<div class='alert alert-danger' role='alert'><strong>Warning!</strong> {$errorMessage}</div>";
}

/**
 * @param $successMessage
 * @return string
 */
function setValidationSuccessMessage($successMessage) {
    return "<div class='alert alert-success' role='alert'><strong>Success!</strong> {$successMessage}</div>";
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

/**
 * @param $formData
 */
function registerUser($formData) {
    $formData = array_map('escape', $formData);
    extract($formData);
    $password = md5($password);
    $validation_code = md5($username.microtime());
    $query = "INSERT INTO `users`
    (`email`, `first_name`, `last_name`, `username`, `password`, `validation_code`, `active`) 
    VALUES ('{$register_email}', '{$first_name}', '{$last_name}', '{$username}', '{$password}', '{$validation_code}', 0);";

    if (query($query)) {
        $message = "Please click the link below to activate your account: 
        http://localhost/php_login/activate.php?email={$register_email}&code={$validation_code}";
        $headers = "From: noreply@mywebsite.com";
        sendMail($register_email, 'Activate Account', $message, $headers);
        $message = "<p class='bg-success text-center'>Please check your email for the activation link.</p>";
        setMessage($message);
        redirect('index.php');
    } else {
        echo setValidationErrorMessage('Error registering user, please contact system administration.');
    }
}

/**
 *
 */
function activationUser() {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['email'])) {
            $email = escape(clean($_GET['email']));
            $validationCode = escape(clean($_GET['code']));

            $query = "SELECT * FROM `users` WHERE `email` = '{$email}' AND `validation_code` = '{$validationCode}';";
            $result = query($query);
            if (countRows($result) > 0) {
                $user = fetchAssoc($result);
                $query = "UPDATE `users` SET `active` = 1, `validation_code` = 0 WHERE `id` = {$user['id']} AND `email` = '{$user['email']}' AND `validation_code` = '{$validationCode}';";
                query($query);
                setMessage(setValidationSuccessMessage('User activation successful, please login.'));
                redirect('login.php');
            } else {
                echo setValidationErrorMessage('Error during user activation, please check the validation code.');
            }
        }
    }
}

/**
 * Validate input from login form
 */
function validateLogin(): void {
    $errors = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = escape(clean($_POST['email']));
        $password = md5($_POST['password']);
        if (empty($email) || empty($password)) {
            $message = 'Missing required fields, please review the data you sent.';
            echo setValidationErrorMessage($message);
            $errors[] = $message;
        }
        if (empty($errors)) {
            if (loginUser($email, $password)) {
                redirect('index.php');
            } else {
                $message = 'The provided credentials are incorrect.';
                echo setValidationErrorMessage($message);
                $errors[] = $message;
            }
        }
    }
}

/**
 * @param $email
 * @param $password
 * @return bool
 */
function loginUser($email, $password) {
    $query = "SELECT * FROM `users` WHERE `email` = '{$email}' AND `active` = 1;";
    $result = query($query);
    if (countRows($result) > 0) {
        $user = fetchAssoc($result);
        if (strcmp($password, $user['password']) === 0) {
            $_SESSION['email'] = $user['email'];
            $sessionToken = generateToken();
            if (isset($_POST['remember']) && $_POST['remember'] === 'on') {
                setcookie('login_token', $sessionToken, time() + 86400);
            }
        }
        return true;
    }
    return false;
}

/**
 * @return bool
 */
function isLoggedIn() {
    if (isset($_SESSION['email']) && isset($_SESSION['token']) && isset($_COOKIE['login_token'])) {
        return (bool) ($_SESSION['token'] === $_COOKIE['login_token']);
    }
    if (isset($_SESSION['email']) && isset($_SESSION['token'])) {
        return true;
    }
    return false;
}

function recoverPassword($email) {
    $errors = [];
    if($_SERVER['REQUEST_METHOD'] === "POST") {
        $email = clean($email);
        if (!is_email($email)) {
            $message = 'The provided credentials are incorrect.';
            echo setValidationErrorMessage($message);
            $errors[] = $message;
        }
        if (isset($_SESSION['token']) && $_POST['token'] === $_SESSION['token']) {
            if (emailAlreadyExists($email)) {
                $validation_code = md5($email . microtime());
                setcookie('temp_access_code', $validation_code, time() + 900);
                $sql = "UPDATE users SET validation_code = '".escape($validation_code)."' WHERE email = '".escape($email)."'";
                query($sql);
                $subject = "Please reset your password";
                $message =  " Here is your password reset code {$validation_code}
			Click here to reset your password http://localhost.com/php_login/code.php?email={$email}&code={$validation_code}";
                $headers = "From: noreply@edwincodecollege.com";
                sendMail($email, $subject, $message, $headers);
                setMessage("<p class='bg-success text-center'>Please check your email or spam folder for a password reset code</p>");
                redirect("index.php");
            } else {
                $message = 'The provided credentials are incorrect.';
                echo setValidationErrorMessage($message);
                $errors[] = $message;
            }
        } else {
            redirect('index.php');
        }
    }
}

function validateCode() {
    $errMessage = "<p class='bg-danger text-center'>Your validation code has expired, please try again.</p>";
    if (isset($_COOKIE['temp_access_code']) &&
        $_SERVER['REQUEST_METHOD'] === "POST" &&
        isset($_GET['email']) &&
        isset($_GET['code'])) {
        $code = isset($_POST['code']) ? escape(clean($_POST['code'])) : null;
        $email = isset($_GET['email']) ? escape(clean($_GET['email'])) : null;
        if (empty($code)) {
            setMessage($errMessage);
            return;
        }
        $query = "SELECT * FROM `users` WHERE `validation_code` = '{$code}' AND email = '{$email}';";
        $result = query($query);

        if (countRows($result) === 1) {
            setcookie('email', $email, time() + 300, HTTP_COOKIE_HTTPONLY);
            redirect('reset.php');
        } else {
            setMessage($errMessage);
            return;
        }
    } else {
        setMessage($errMessage);
        redirect('recover.php');
    }
}

function resetUserPassword() {
    if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_COOKIE['temp_access_code']) && isset($_COOKIE['email'])) {
        $email = escape(clean($_COOKIE['email']));
        $code = escape(clean($_COOKIE['temp_access_code']));
        $query = "SELECT * FROM users WHERE email = '{$email}' AND validation_code = '{$code}';";
        $result = query($query);
        $user = fetchAssoc($result);
        if ($user['email'] === $email) {
            $password = md5(escape(clean($_POST['password'])));
            $query = "UPDATE users SET password = '{$password}' WHERE id = {$user['id']} AND email = '{$user['email']}';";
            if (query($query)) {
                setMessage("<p class='bg-success text-center'>Password changed successfully!</p>");
                $query = "UPDATE `users` SET validation_code = 0 WHERE id = {$user['id']} AND email = '{$user['email']}';";
                query($query);
                redirect('login.php');
            }
        } else {
            echo setValidationErrorMessage('These was no email found.');
        }
    }
}