<?php

function get_existing_team($id): null | array {
    $conn = db_connect();

    if (!is_numeric($id)) {
        return null;
    }

    $result = $conn->query("SELECT * FROM teams WHERE tm_id = $id");
    $row = $result->fetch_assoc();

    return array(
        "name" => $row['tm_name'],
        "hex" => '#' . $row["tm_hex_color"]
    );
}

function update_existing_team($id, $name, $hex): void {
    $conn = db_connect();

    if (!is_numeric($id)) {
        return;
    }

    // Clean $hex so it is just the numeric hex code and no # in front
    $hex = str_replace("#", "", $hex);
    $hex = mb_strtoupper($hex, "UTF-8");

    $query = $conn->prepare("UPDATE teams SET tm_name = ?, tm_hex_color = ? WHERE tm_id = ?");
    $query->bind_param("ssi", $name, $hex, $id);
    $query->execute();
    $query->close();
}

function create_new_team($name, $hex): void {
    $conn = db_connect();

    // Clean $hex so it is just the numeric hex code and no # in front
    $hex = str_replace("#", "", $hex);
    $hex = mb_strtoupper($hex, "UTF-8");

    $query = $conn->prepare("INSERT INTO teams (tm_name, tm_hex_color) VALUES (?, ?)");
    $query->bind_param("ss", $name, $hex);
    $query->execute();

    // Add 0 points to the table to have the team show on the homepage
    $query = $conn->prepare("SELECT tm_id FROM teams WHERE tm_name = ?");
    $query->bind_param("s", $name);
    $query->execute();
    $result = $query->get_result();
    $tm_id = $result->fetch_assoc()["tm_id"];
    $query = $conn->prepare("INSERT INTO points (pts_timestamp, pts_tm_id, pts_amount) VALUES (CURRENT_TIMESTAMP() , ?, 0)");
    $query->bind_param("i", $tm_id);
    $query->execute();
    $query->close();
}

function delete_existing_team($id): void {
    $conn = db_connect();

    if (!is_numeric($id)) {
        return;
    }

    // Delete all points added to the team
    $conn->query("DELETE FROM points WHERE pts_tm_id = '$id'");

    // Delete all groups assigned to the team
    $conn->query("DELETE FROM `groups` WHERE grp_tm_id = '$id'");

    $conn->query("DELETE FROM teams WHERE tm_id = '$id'");
}