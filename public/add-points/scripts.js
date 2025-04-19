window.onload = function () {
    let element = document.getElementById('points-team');
    let teamId = element.options[element.selectedIndex].value;
    changeGroups(teamId);

    document.getElementById("points-team").addEventListener("change", function () {
        let element = document.getElementById('points-team');
        let teamId = element.options[element.selectedIndex].value;
        changeGroups(teamId);
    });

    document.getElementById("points-amount").addEventListener("keyup", function () {
        let amountInput = document.getElementById("points-amount").value;

        if (amountInput !== "") {
            document.getElementById('add').removeAttribute("disabled");
        } else {
            document.getElementById('add').setAttribute("disabled", "disabled ");
        }
    });

    // If editing, the save button should automatically be working; run the check right when the page loads
    let amountInput = document.getElementById("points-amount").value;
    if (amountInput !== "") {
        document.getElementById('add').removeAttribute("disabled");
    } else {
        document.getElementById('add').setAttribute("disabled", "disabled ");
    }
}

function changeGroups(team) {
    fetch(`../inter-api/get-groups.php?id=${team}&auth=${getAuthCookie()}`)
        .then (response => response.text())
        .then (data => {
            let groups = data;
            let groupsSelector = document.getElementById('points-group');

            // Delete the old options for groups
            groupsSelector.innerHTML = '';

            // Explode the groups string into a 2D array
            groups = groups.split("|");

            // Add each group to the group selector
            for (const group of groups) {
                let groupParts = group.split("~");
                let id = groupParts[0];
                let name = groupParts[1];

                if (typeof selectedGroupId !== 'undefined' && selectedGroupId.toString() === id) {
                        groupsSelector.innerHTML += `<option value='${id}' selected>${name}</option>`;
                        selectedGroupId = undefined;
                } else {
                    groupsSelector.innerHTML += `<option value='${id}'>${name}</option>`;
                }
            }
        });
}

function getAuthCookie() {
    let cookies = document.cookie.split(";");
    let auth = "";
    for (const cookie of cookies) {
        if (cookie.indexOf("auth=") !== -1) {
            auth = cookie.split("=")[1];
        }
    }
    return auth;
}

function confirmDelete() {
    if (typeof clicked !== "undefined" && clicked === "Save") {
        return true;
    }
    return confirm("Are you sure you want to delete this entry?");
}