<?php

require_once __DIR__ . "/login/authentication.php";
require_once __DIR__ . "/users/verify-user.php";

// Check a lockfile exists to prevent multiple runs of this codee
$lockfile = __DIR__ . "/init-admin.lock";

if (!file_exists($lockfile)) {
    if (getenv("AUTO_APPROVE_FIRST_USER") === "true") {
        $admin_username = getenv("ADMIN_USERNAME");
        $admin_password = getenv("ADMIN_PASSWORD");

        if ($admin_password) {
            $success = new_user($admin_username, $admin_password, 2);
            if ($success) {
                verify_user($admin_username);
                echo "Admin user created\n";
            }
        } else {
            echo "ADMIN_PASSWORD environment variable not set...\n";
            echo "Make sure passwords.env exists and is in the root folder of this project\n";
            exit(1);
        }
    }

    // Make lockfile to prevent future editing of the admin user
    touch($lockfile);
}