<?php

use Random\RandomException;

function db_connect(): mysqli {
    $hostname = getenv("DB_HOSTNAME") ? getenv("DB_HOSTNAME") : "localhost";
    $username = "root";
    $password = getenv("DB_PASSWORD") ? getenv("DB_PASSWORD") : "NsW284i^n95raK@Y%N4#";
    $database = "color-wars";

    return new mysqli($hostname, $username, $password, $database);
}

function logout(): bool {
    // Remove the session
    if (isset($_SESSION["auth"]) || isset($_SESSION["role"]) || isset($_SESSION["name"])) {
        session_destroy();
    }

    // Delete the session from the database
    $conn = db_connect();
    $series_identifier = explode("|", $_COOKIE["auth"])[0];
    $query = $conn->prepare("DELETE FROM sessions WHERE sn_series_identifier = ?");
    $query->bind_param("s", $series_identifier);
    $query->execute();

    // Remove the cookie
    setcookie("auth", "", [
        'expires' => time() - 3600,
        'path' => '/',
        'samesite' => 'Strict'
    ]);

    return true;
}

function new_user($username, $password, $role): bool | string {
    // Register the user in the database

    $conn = db_connect();

    // Create their password hash
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Make sure no users have the same username
    $usr_stmt = $conn->prepare("SELECT * FROM users WHERE usr_name = ?");
    $usr_stmt->bind_param("s", $username);
    $usr_stmt->execute();
    $usr_result = $usr_stmt->get_result();

    if (mysqli_num_rows($usr_result) != 0) {
        return "username-taken";
    }

    // Insert them into the database
    $usr_insert = $conn->prepare("INSERT INTO users (usr_name, usr_password) VALUES (?, ?)");
    $usr_insert->bind_param("ss", $username, $password_hash);
    $usr_insert->execute();


    // Insert their username into the pending database
    $pend_insert = $conn->prepare("INSERT INTO pending (pnd_usr_name) VALUES (?)");
    $pend_insert->bind_param("s", $username);
    $pend_insert->execute();

    // Assign their role
    // Get the user ID
    $usr_id_fetch = $conn->prepare("SELECT usr_id FROM users WHERE usr_name = ?");
    $usr_id_fetch->bind_param("s", $username);
    $usr_id_fetch->execute();
    $result = $usr_id_fetch->get_result();
    $usr_id = $result->fetch_assoc()["usr_id"];

    // Add role to the database
    $role_insert = $conn->prepare("INSERT INTO roles (rl_usr_id, rl_role) VALUES (?, ?)");
    $role_insert->bind_param("ii", $usr_id, $role);
    $role_insert->execute();

    // Return true for success
    return true;
}

function valid_series($conn, $series_identifier, $user_hash): bool {

    $query = $conn->prepare("SELECT sn_username FROM sessions
                WHERE sn_series_identifier = ?
                AND SHA2(sn_username, 256) = ?
                AND sn_expire > UNIX_TIMESTAMP()");
    $query->bind_param("ss", $series_identifier, $user_hash);
    $query->execute();
    $result = $query->get_result();

    return $result->num_rows == 1;
}

/**
 * @throws RandomException
 */
function confirm_session(): bool {

    $expire_days = 7;

    // Check for a session, then cookie triplet, then cookie double (problem), then return false
    if (isset($_SESSION["auth"]) && $_SESSION["auth"] && isset($_SESSION["role"]) && isset($_SESSION["id"]) && isset($_SESSION["name"])) {
        // There is a session; no more info needed. Return true
        return true;
    } else if (isset($_COOKIE["auth"])) {
        // There is a cookie; continue with verification
        $conn = db_connect();

        $arr = explode("|", $_COOKIE["auth"]);

        // If the length of the cookie array does not match what is expected, delete the cookie, return false, and stop verification
        if (count($arr) != 3) {
            setcookie("auth", "", time() - 3600, "/");
            return false;
        }

        // Get the cookie triplet
        $series_identifier = $arr[0];
        $session_token = $arr[1];
        $user_hash = $arr[2];

        // Check if the triplet (with a correct expiry date) is present on the database
        $triplet = $conn->prepare("SELECT sn_username FROM sessions
                WHERE sn_series_identifier = ?
                AND sn_session_token = ?
                AND SHA2(sn_username, 256) = ?
                AND sn_expire > UNIX_TIMESTAMP()");
        $triplet->bind_param("sss", $series_identifier, $session_token, $user_hash);
        $triplet->execute();
        $result = $triplet->get_result();

        if (mysqli_num_rows($result) == 1) {
            // Cookie triplet was present!
            // Make a new cookie, create a session, and return a positive verification
            $row = $result->fetch_assoc();

            // Get the same info for some parts (redundant but should be done anyway)
            $username = $row["sn_username"];
            $user_hash = hash("sha256", $username);
            $expire = time() + ($expire_days * 24 * 60 * 60);

            // Make a new token
            $new_token = bin2hex(random_bytes(32));

            // Set the new cookie
            $cookie_val = "$series_identifier|$new_token|$user_hash";
            setcookie("auth", $cookie_val, [
                'expires' => $expire,
                'path' => '/',
                'samesite' => 'Strict'
            ]);

            // Update the session database record with the new session token
            $update_session = $conn->prepare("UPDATE sessions
                SET sn_session_token = ?, sn_expire = ?
                WHERE sn_series_identifier = ?
                AND SHA2(sn_username, 256) = ?");
            $update_session->bind_param("ssss", $new_token, $expire, $series_identifier, $user_hash);
            $update_session->execute();

            // Set up a session for the user
            if (session_status() == PHP_SESSION_NONE)
            {
                session_start([
                    'cookie_httponly' => true,
                    'cookie_samesite' => 'Strict'
                ]);

            }
            $_SESSION["auth"] = true;

            // Get their role and username and store that as a session
            $query = $conn->prepare("SELECT usr_id FROM users WHERE usr_name = ?");
            $query->bind_param("s", $username);
            $query->execute();
            $user_result = $query->get_result();
            $usr_id = $user_result->fetch_assoc()["usr_id"];
            $query = $conn->prepare("SELECT rl_role FROM roles WHERE rl_usr_id = ?");
            $query->bind_param("i", $usr_id);
            $query->execute();
            $role_result = $query->get_result();

            $_SESSION["role"] = mysqli_fetch_assoc($role_result)["rl_role"];
            $_SESSION["name"] = $username;
            $_SESSION["id"] = $usr_id;

            // Validate that all was successful and the user is confirmed logged in
            return true;


        } else if (valid_series($conn, $series_identifier, $user_hash)) {
            // Series is valid but token is not; something malicious has happened

            // Delete all database records with the series identifier present
            $query = $conn->prepare("DELETE FROM sessions WHERE sn_series_identifier = ?");
            $query->bind_param("s", $series_identifier);
            $query->execute();

            // Remove the cookie by setting it to expire a time in the past
            setcookie("auth", "", time() - 3600, "/");

            return false;
        } else {
            // Cookie was present but something else weird happened; delete it and return false
            // Possibly the database record was deleted before the cookie expired

            setcookie("auth", "", time() - 3600, "/");
            setcookie("PHPSESSID", "", time() - 3600, "/");

            return false;
        }

    } else {
        // No cookie or session present; return false

        return false;
    }
}

/**
 * @throws RandomException
 */
function login($username, $password): bool | string{
    $expire_days = 7;

    // Confirm the user is real
    $conn = db_connect();

    // Get the credentials
    $query = $conn->prepare("SELECT * FROM users WHERE usr_name = ?");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();
    $row = mysqli_num_rows($result) == 1 ? mysqli_fetch_assoc($result) : "";

    if (mysqli_num_rows($result) == 1 && password_verify($password, $row["usr_password"])) {

        // See if they are waiting for authentication
        $query = $conn->prepare("SELECT * FROM pending WHERE pnd_usr_name = ?");
        $query->bind_param("s", $username);
        $query->execute();
        $pnd_result = $query->get_result();
        if (mysqli_num_rows($pnd_result) > 0)
        {
            // User is pending approval; return that info
            return "pending";
        }

        // The user is real and the password is correct; continue to logging in and creating a cookie
        // Generate the info for a session row in the database and a cookie
        $series_identifier = bin2hex(random_bytes(32));
        $session_token = bin2hex(random_bytes(32));
        $username = $row["usr_name"];
        $user_hash = hash("sha256", $username);
        $expire = time() + ($expire_days * 24 * 60 * 60);

        // Generate a cookie
        $cookie_val = "$series_identifier|$session_token|$user_hash";
        setcookie("auth", $cookie_val, [
            'expires' => $expire,
            'path' => '/',
            'samesite' => 'Strict'
        ]);

        // Generate a session record on a database
        $query = $conn->prepare("INSERT INTO sessions (sn_series_identifier, sn_session_token, sn_username, sn_expire) VALUES (?, ?, ?, ?)");
        $query->bind_param("ssss", $series_identifier, $session_token, $username, $expire);
        $query->execute();

        // Create a session
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Strict'
        ]);
        $_SESSION["auth"] = true;

        // Get their role && username and store that as a session
        $usr_id = $row["usr_id"];
        $query = $conn->prepare("SELECT rl_role FROM roles WHERE rl_usr_id = ?");
        $query->bind_param("i", $usr_id);
        $query->execute();
        $role_result = $query->get_result();
        $_SESSION["role"] = mysqli_fetch_assoc($role_result)["rl_role"];
        $_SESSION["name"] = $username;
        $_SESSION["id"] = $usr_id;

        // Confirm the login was successful
        return true;
    } else {
        return false;
    }
}

function api_auth($auth): bool {

    $arr = explode("|", $auth);
    if (count($arr) != 3) {
        return false;
    }

    $conn = db_connect();
    $series_identifier = $arr[0];
    $session_token = $arr[1];
    $user_hash = $arr[2];

    $query = $conn->prepare("SELECT sn_username FROM sessions
                                    WHERE sn_series_identifier = ?
                                    AND sn_session_token = ?
                                    AND SHA2(sn_username, 256) = ?
                                    AND sn_expire > UNIX_TIMESTAMP()");

    $query->bind_param("sss", $series_identifier, $session_token, $user_hash);
    $query->execute();
    $result = $query->get_result();

    if (mysqli_num_rows($result) == 1) {
        return true;
    }

    return false;
}