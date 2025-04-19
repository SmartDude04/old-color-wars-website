<?php

require_once "../../api/login/authentication.php";
require_once "../../api/teams/edit/modify-teams.php";

try {
    if (!(confirm_session() && $_SESSION["auth"] == 2)) {
        header("location: ../index.php");
        exit();
    }
} catch (\Random\RandomException $e) {
    header("location: ../index.php");
    exit();
}

// Check if the user has saved data; if so, update/create it in the database
if (isset($_POST["save"]) && isset($_POST["team-name"]) && isset($_POST["team-hex"])) {
    // Validate proper hex string
    $hex = $_POST["team-hex"];
    if (!ctype_xdigit(str_replace("#", "", $hex))) {
        $hex = "000000";
    }

    // Check if we are updating or creating
    if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
        update_existing_team($_GET["id"], $_POST["team-name"], $hex);
    } else {
        create_new_team($_POST["team-name"], $hex);
    }

    header("location: ../index.php?page=teams");
    exit();
}

// Delete team if needed
if (isset($_GET["id"]) && is_numeric($_GET["id"]) && isset($_POST["delete"])) {
    delete_existing_team($_GET["id"]);

    header("location: ../index.php?page=teams");
    exit();
}

$name = "";
$hex = "";

if (isset($_GET["id"])) {
    $result = get_existing_team($_GET["id"]);
    $name = $result["name"];
    $hex = $result["hex"];
}
?>

<!doctype html>
<html lang="en">
<head>
    <title>Edit Teams &bull; CGC Color Wars</title>
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="../css/fonts.css">
</head>
<body>

<script src="scripts.js" defer></script>
<div class="container">
    <div class="edit-view">
        <h1 class="header">Edit/Add Team</h1>

        <form method="post" action="" class="edit-form">
            <div class="input-container">
                <label for="team-name" class="input-label">Team Name</label>
                <input type="text" value="<?php echo $name;?>" required name="team-name" id="team-name" class="input">
            </div>

            <div class="input-container">
                <label for="team-hex" class="input-label">Team Hex Color</label>
                <input type="text" value="<?php echo $hex;?>" required placeholder="" name="team-hex" id="team-hex" class="input">
                <label for="team-hex" class="hex-explain">Can be found using an <a href="https://g.co/kgs/Lmr2RmW" target="_blank" class="hex-link">online color picker</a>.</label>
            </div>

            <div class="buttons">
                <input type="submit" value="Save" disabled="disabled" name="save" class="save-button button" id="submit">
                <?php
                if (isset($_GET["id"])) {
                    echo "<input type='submit' value='Delete Team' name='delete' class='delete-button button' id='delete'>";
                }
                ?>
            </div>
        </form>

    </div>
</div>
</body>
</html>
