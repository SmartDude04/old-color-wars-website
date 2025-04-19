<?php

require_once "../../api/login/authentication.php";
require_once "../../api/groups/modify-groups.php";

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
if (isset($_POST["save"]) && isset($_POST["group-name"]) && isset($_POST["group-team"])) {
    // Check if we are updating or creating
    if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
        update_existing_group($_GET["id"], $_POST["group-name"], $_POST["group-team"]);
    } else {
        create_new_group($_POST["group-name"], $_POST["group-team"]);
    }

    header("location: ../index.php?page=groups");
    exit();
}

// Delete group if needed
if (isset($_GET["id"]) && is_numeric($_GET["id"]) && isset($_POST["delete"])) {
    delete_existing_group($_GET["id"]);

    header("location: ../index.php?page=groups");
    exit();
}

$name = "";
$team = "";

if (isset($_GET["id"])) {
    $result = get_existing_group($_GET["id"]);
    $name = $result["name"];
    $team = $result["team"];
}
?>

<!doctype html>
<html lang="en">
<head>
    <title>Edit Groups &bull; CGC Color Wars</title>
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="../css/fonts.css">
</head>
<body>

<script src="scripts.js" defer></script>

<div class="container">
    <div class="edit-view">
        <h1 class="header">Edit/Add Group</h1>

        <form method="post" action="" class="edit-form">
            <div class="input-container">
                <label for="group-name" class="input-label">Group Name</label>
                <input type="text" value="<?php echo $name;?>" required name="group-name" id="group-name" class="input">
            </div>

            <div class="input-container">
                <label for="group-team" class="input-label">Group Team</label>
                <select name="group-team" id="group-team" class="team-selector">
                    <?php

                    // Get all teams and show them as options
                    $rows = get_teams_for_selector();

                    foreach ($rows as $row) {
                        if ($row["tm_id"] == $team) {
                            echo "<option value='" . $row["tm_id"] . "' selected>" . $row["tm_name"] . "</option>";
                        } else {
                            echo "<option value='" . $row["tm_id"] . "'>" . $row["tm_name"] . "</option>";
                        }
                    }

                    ?>
                </select>
            </div>

            <div class="buttons">
                <input type="submit" value="Save" disabled="disabled" name="save" class="save-button button" id="submit">
                <?php
                if (isset($_GET["id"])) {
                    echo "<input type='submit' value='Delete Group' name='delete' class='delete-button button' id='delete'>";
                }
                ?>
            </div>
        </form>

    </div>
</div>
</body>
</html>
