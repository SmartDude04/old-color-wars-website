window.onload = function(){
    document.getElementById("team-name").addEventListener("keyup", function() {
        let nameInput = document.getElementById("team-name").value;
        let hexInput = document.getElementById('team-hex').value;

        if (nameInput !== "" && hexInput !== "") {
            document.getElementById('submit').removeAttribute("disabled");
        } else {
            document.getElementById('submit').setAttribute("disabled", "disabled ");
        }
    });

    document.getElementById("team-hex").addEventListener("keyup", function() {
        let nameInput = document.getElementById("team-name").value;
        let hexInput = document.getElementById('team-hex').value;

        if (nameInput !== "" && hexInput !== "") {
            document.getElementById('submit').removeAttribute("disabled");
        } else {
            document.getElementById('submit').setAttribute("disabled", "disabled ");
        }
    });


    // Run at the beginning
    let nameInput = document.getElementById("team-name").value;
    let hexInput = document.getElementById('team-hex').value;

    if (nameInput !== "" && hexInput !== "") {
        document.getElementById('submit').removeAttribute("disabled");
    } else {
        document.getElementById('submit').setAttribute("disabled", "disabled ");
    }
}

function confirmDelete() {
    if (typeof clicked !== "undefined" && clicked === "Save") {
        return true;
    }
    return confirm("Are you sure you want to delete this group? All points added to this group will be removed.");
}
