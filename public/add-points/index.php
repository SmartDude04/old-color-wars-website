<?php

require_once "../../api/login/authentication.php";
require_once "../../api/points/points-helper.php";

try {
    if (!confirm_session()) {
        header("location: ../index.php");
        exit();
    }
} catch (\Random\RandomException $e) {
    header("location: ../index.php");
    exit();
}

if (isset($_POST["add"]) && isset($_POST["points-team"]) && isset($_POST["points-group"]) && isset($_POST["points-amount"]) && isset($_POST["points-desc"])) {
    if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
        update_points($_GET["id"], $_POST["points-team"], $_POST["points-group"], $_POST["points-amount"], $_POST["points-desc"]);
        header("location: ../index.php?page=history");
    } else {
        add_points($_POST["points-team"], $_POST["points-group"], $_POST["points-amount"], $_POST["points-desc"]);
        header("location: ../index.php");
    }
    exit();
}

if (isset($_POST["delete"]) && isset($_GET["id"]) && is_numeric($_GET["id"])) {
    delete_points($_GET["id"]);
    header("location: ../index.php?page=history");
    exit();
}

$selected_team = "";
$selected_group = "";
$amount = 0;
$description = "";

if (isset($_GET["id"])) {
    $selections = get_points_info($_GET["id"]);
    $selected_team = $selections["pts_tm_id"];
    $selected_group = $selections["pts_grp_id"];
    $amount = $selections["pts_amount"];
    $description = $selections["pts_description"];
}

?>

<!doctype html>
<html lang="en">
<head>
    <title><?php if (isset($_GET["id"])) {echo "Edit";} else {echo "Add";} ?> Points &bull; CGC Color Wars</title>
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="../css/fonts.css">
</head>
<body>
<?php
if ($selected_group !== "") {
    echo "<script> let selectedGroupId = $selected_group;</script>";
}
?>
<script src="scripts.js" defer></script>
<div class="container">
    <div class="add-points-container">
        <?php
        if (isset($_GET["id"])) {
            echo "<h1 class='header'>Edit Points</h1>";
        } else {
            echo "<h1 class='header'>Add Points</h1>";
        }
        ?>

        <form method="post" action="" class="points-form" onsubmit="return confirmDelete();">
            <div class="selection-container">
                <label for="points-team" class="points-label">Team</label>
                <select name="points-team" id="points-team" required class="points-selector">
                    <?php

                    $teams = get_teams();
                    foreach ($teams as $team) {
                        if ($team["tm_id"] == $selected_team) {
                            echo "<option value='" . $team["tm_id"] . "' selected>" . $team["tm_name"] . "</option>";
                        } else {
                            echo "<option value='" . $team["tm_id"] . "'>" . $team["tm_name"] . "</option>";
                        }
                    }

                    ?>
                </select>
            </div>

            <div class="selection-container">
                <label for="points-group" class="points-label">Group</label>
                <select name="points-group" id="points-group" required class="points-selector">

                </select>
            </div>

            <div class="selection-container">
                <label for="points-amount" class="points-label">Amount</label>
                <input type="number" value="<?php echo $amount !== 0 ? $amount : "";?>" required name="points-amount" id="points-amount" class="points-input">
            </div>

            <div class="selection-container">
                <label for="points-desc" class="points-label">Description (Optional)</label>
                <input type="text" value="<?php echo $description != "" ? $description : "";?>" name="points-desc" id="points-desc" class="points-input desc-input">
            </div>

            <div class="buttons">
                <input type="submit" value="Save" disabled="disabled" name="add" class="add-button" id="add" onclick="clicked = 'Save'">
                <?php

                if (isset($_GET["id"])) {
                    echo "<input type='submit' value='Delete' name='delete' class='delete-button' id='delete'>";
                }

                ?>
            </div>
        </form>
    </div>
</div>
</body>
</html>