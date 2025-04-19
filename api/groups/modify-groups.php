<?php

function get_existing_group($id): array {
    if (!is_numeric($id)) {
        return array();
    }

    $conn = db_connect();

    $result = $conn->query("SELECT * FROM groups WHERE grp_id = '$id'");
    $row = $result->fetch_assoc();

    return array(
        "name" => $row['grp_name'],
        "team" => $row["grp_tm_id"]
    );
}

function update_existing_group($id, $name, $team): void {
    $conn = db_connect();

    if (!is_numeric($id)) {
        return;
    }


    $query = $conn->prepare("UPDATE groups SET grp_name = ?, grp_tm_id = ? WHERE grp_id = ?");
    $query->bind_param("sii", $name, $team, $id);
    $query->execute();
    $query->close();
}

function create_new_group($name, $team): void {
    $conn = db_connect();

    $query = $conn->prepare("INSERT INTO groups (grp_name, grp_tm_id) VALUES (?, ?)");
    $query->bind_param("si", $name, $team);
    $query->execute();
    $query->close();
}

function delete_existing_group($id): void {
    $conn = db_connect();

    if (!is_numeric($id)) {
        return;
    }

    // Delete points entries with the group id
    $conn->query("DELETE FROM `points` WHERE pts_grp_id = '$id'");

    $conn->query("DELETE FROM `groups` WHERE grp_id = '$id'");
}

function get_teams_for_selector(): array {
    $conn = db_connect();

    $result = $conn->query("SELECT tm_id, tm_name FROM teams ORDER BY tm_name");

    $return_array = array();
    while ($row = $result->fetch_assoc()) {
        $return_array[] = $row;
    }

    return $return_array;
}