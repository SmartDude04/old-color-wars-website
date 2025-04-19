<?php

function verify_user($usr_name): void {
    $conn = db_connect();

    $query = $conn->prepare("DELETE FROM pending WHERE pnd_usr_name = ?");
    $query->bind_param("s", $usr_name);
    $query->execute();
    $query->close();
}
