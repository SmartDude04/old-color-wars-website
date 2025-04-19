<?php

function get_teams(): array {
    $conn = db_connect();

    $result = $conn->query("SELECT tm_id, tm_name FROM teams ORDER BY tm_name");

    $return_arr = array();
    while ($row = $result->fetch_assoc()) {
        $return_arr[] = $row;
    }

    return $return_arr;
}

function add_points($team_id, $group_id, $amount, $description): void {
    $conn = db_connect();

    // Make sure no bad data is getting into the database
    if (!(is_numeric($amount) && is_numeric($team_id) && is_numeric($group_id))) {
        return;
    }

    $usr_id = $_SESSION['id'];

    // $conn->execute_query("INSERT INTO points (pts_timestamp, pts_tm_id, pts_grp_id, pts_usr_id, pts_amount, pts_description) VALUES (CURRENT_TIMESTAMP(), $team_id, $group_id, $usr_id, $amount, '$description')");
    $query = $conn->prepare("INSERT INTO points (pts_timestamp, pts_tm_id, pts_grp_id, pts_usr_id, pts_amount, pts_description) VALUES (CURRENT_TIMESTAMP(), ?, ?, ?, ?, ?)");
    $query->bind_param("iiiis", $team_id, $group_id, $usr_id, $amount, $description);
    $query->execute();
    $query->close();
}

function update_points($id, $team, $group, $amount, $description): void {
    $conn = db_connect();
    $usr_id = $_SESSION['id'];

    if (!(is_numeric($id) && is_numeric($team) && is_numeric($group) && is_numeric($amount))) {
        return;
    }

    // Add an "edited" notice to the end of the description
    if (!strpos($description, "(Edited)")) {
        $description .= " (Edited)";
    }
    $query = $conn->prepare("UPDATE points SET pts_tm_id = ?, pts_grp_id = ?, pts_usr_id = ?, pts_amount = ?, pts_description = ? WHERE pts_id = ?");
    $query->bind_param("iiiisi", $team, $group, $usr_id, $amount, $description, $id);
    $query->execute();
    $query->close();
}

function delete_points($id): void {
    $conn = db_connect();

    if (!(is_numeric($id))) {
        return;
    }

    $conn->query("DELETE FROM points WHERE pts_id = '$id'");
}

function get_points_info($id): null | array {
    $conn = db_connect();

    if (!(is_numeric($id))) {
        return null;
    }

    $result = $conn->query("SELECT pts_tm_id, pts_grp_id, pts_amount, pts_description FROM points WHERE pts_id = '$id'");
    return $result->fetch_assoc();
}