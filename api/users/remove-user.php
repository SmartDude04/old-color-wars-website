<?php

function remove_user($usr_name): void {
    $conn = db_connect();

    $usr_name = $_GET["name"];

    // Make sure the user is still pending so no one can do malicious things
    $query = $conn->prepare("SELECT pnd_usr_name FROM pending WHERE pnd_usr_name = ?");
    $query->bind_param("s", $usr_name);
    $query->execute();
    $pnd_usr_cnt = $query->get_result();
    if (mysqli_num_rows($pnd_usr_cnt) !== 1) {
        exit();
    }

    // Get the user ID and delete from roles
    $query = $conn->prepare("SELECT usr_id FROM users WHERE usr_name = ?");
    $query->bind_param("s", $usr_name);
    $query->execute();
    $usr_id_result = $query->get_result();
    $usr_id = mysqli_num_rows($usr_id_result) == 1 ? $usr_id_result->fetch_assoc()["usr_id"] : -1;

    $conn->query("DELETE FROM roles WHERE rl_usr_id = $usr_id");
    $conn->query("DELETE FROM users WHERE usr_id = $usr_id");
    $query = $conn->prepare("DELETE FROM pending WHERE pnd_usr_name = ?");
    $query->bind_param("s", $usr_name);
    $query->execute();
    $query->close();
}