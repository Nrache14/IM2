$(document).ready(function() {
    // Fetch and display data
    function loadUsers() {
        $.ajax({
            url: "student.php",
            method: "GET",
            dataType: "json"
        }).done(function(data) {
            let parent = $("#tableBody");
            parent.empty(); // Clear existing rows

            data.forEach((item, index) => {
                parent.append(`
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.first_name}</td>
                        <td>${item.last_name}</td>
                        <td>${item.email}</td>
                        <td>${item.gender}</td>
                        <td>${item.course}</td>
                        <td>${item.address}</td>
                        <td>${item.age}</td>
                        <td>
                            <button class="btn btn-dark btn-sm" onclick="editUser(${item.id})">Edit</button>
                            <button class="btn btn-outline-dark btn-sm" onclick="deleteUser(${item.id})">Delete</button>
                        </td>
                    </tr>
                `);
            });
        }).fail(function(jqXHR, textStatus, errorThrown) {
        console.error("Failed to fetch data:", textStatus, errorThrown);
        });
    }

    loadUsers();

// Handle form submission to create new user
$("#addUserForm").on("submit", function(event) {
    event.preventDefault();

    const birthdate = $("#InputBirthdate").val();

    const age = new Date().getFullYear() - new Date(birthdate).getFullYear()

    $.ajax({
        url: "create.php",
        type: "POST",
        dataType: "json",
        data: {
            fname: $("#InputFname").val(),
            lname: $("#InputLname").val(),
            email: $("#InputEmail").val(),
            gender: $("#InputGender").val(),
            course: $("#InputCourse").val(),
            address: $("#InputAddress").val(),
            birthdate: birthdate,
            age: age

        }
    }).done(function(result) {
            if (result.res === "success") {
                alert("User added successfully!");
                $("#createModal").modal('hide');
                $("#addUserForm")[0].reset(); // Clear form inputs
                loadUsers(); // Reload table data
            } else {
                alert("Error: " + (result.message || "Unable to add user."));
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error("Failed to add user:", textStatus, errorThrown);
        });
    });
});

// Function to delete user
function deleteUser(id) {
    if (confirm("Are you sure you want to delete this user?")) {
        $.ajax({
            url: "delete.php",
            type: "POST",
            dataType: "json",
            data: { id: id }
        }).done(function(result) {
            if (result.res === "success") {
                alert("User deleted successfully!");
                window.location.reload();
            } else {
                alert("Error: Unable to delete user.");
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error("Failed to delete user:", textStatus, errorThrown);
        });
    }
}