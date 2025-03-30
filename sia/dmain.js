$(document).ready(function () {
    function calculateAge(birthdate) {
        if (!birthdate) return "N/A";

        let birthDateObj = new Date(birthdate);
        if (isNaN(birthDateObj)) return "N/A";

        let today = new Date();
        let age = today.getFullYear() - birthDateObj.getFullYear();
        let monthDiff = today.getMonth() - birthDateObj.getMonth();
        let dayDiff = today.getDate() - birthDateObj.getDate();

        if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
            age--;
        }
        return age;
    }

    // Function to update user count
    function updateUserCount() {
        let userCount = $("#tableBody tr").length;
        $("#userCount").text(userCount);
    }

    // Fetch and display data
    function loadUsers() {
        $.ajax({
            url: "student.php",
            method: "GET",
            dataType: "json"
        }).done(function (data) {
            let parent = $("#tableBody");
            parent.empty();

            data.forEach((item, index) => {
                let age = calculateAge(item.birthdate);
                let profileImage = item.profile_image
                    ? `<img src="uploads/${item.profile_image}" width="50" height="50" class="rounded-circle">`
                    : "No Image";

                parent.append(`
                    <tr>
                        <td>${index + 1}</td>
                        <td>${profileImage}</td>
                        <td>${item.first_name}</td>
                        <td>${item.last_name}</td>
                        <td>${item.email}</td>
                        <td>${item.gender}</td>
                        <td>${item.course}</td>
                        <td>${item.address}</td>
                        <td>${age}</td>
                        <td>
                            <button class="btn btn-dark btn-sm edit-btn" data-id="${item.student_id}">Edit</button>
                            <button class="btn btn-outline-dark btn-sm delete-btn" data-id="${item.student_id}">Delete</button>
                        </td>
                    </tr>
                `);
            });

            updateUserCount(); // Update count after loading users
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.error("Failed to fetch data:", textStatus, errorThrown);
        });
    }

    loadUsers();

    $("#addUserForm").on("submit", function (event) {
        event.preventDefault();

        const birthdate = $("#InputBirthdate").val();
        const age = calculateAge(birthdate);

        if (age === "N/A") {
            alert("Invalid birthdate! Please enter a valid date.");
            return;
        }

        let formData = new FormData(this);

        $.ajax({
            url: "create.php",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (response) {
                if (response.res === "success") {
                    alert("User added successfully!");
                    $("#addUserForm")[0].reset();
                    $("#createModal").modal("hide");
                    loadUsers();
                } else {
                    console.error("Server Error:", response.message);
                    alert(response.message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                try {
                    let jsonResponse = JSON.parse(jqXHR.responseText);
                    console.error("AJAX error:", jsonResponse.message);
                    alert(jsonResponse.message);
                } catch (e) {
                    console.error("Unexpected response:", jqXHR.responseText);
                    alert("An unexpected error occurred.");
                }
            }
        });
    });

    $("#editUserForm").on("submit", function (event) {
        event.preventDefault();

        let formData = new FormData(this);
        formData.append("id", $("#editId").val());

        $.ajax({
            url: "update.php",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (response) {
                if (response.res === "success") {
                    alert("User updated successfully!");
                    $("#editModal").modal("hide");
                    loadUsers();
                } else {
                    alert("Error: " + (response.msg || "Unable to update user."));
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error("Failed to update user:", textStatus, errorThrown);
                alert("An error occurred. Please try again.");
            }
        });
    });

    $(document).on("click", ".edit-btn", function () {
        let student_id = $(this).data("id");

        $.ajax({
            url: "student.php",
            type: "GET",
            dataType: "json"
        }).done(function (data) {
            let user = data.find(u => u.student_id == student_id);
            if (!user) {
                alert("User not found.");
                return;
            }

            $("#editId").val(user.student_id);
            $("#editFname").val(user.first_name);
            $("#editLname").val(user.last_name);
            $("#editEmail").val(user.email);
            $("#editGender").val(user.gender);
            $("#editCourse").val(user.course);
            $("#editAddress").val(user.address);
            $("#editBirthdate").val(user.birthdate);

            $("#editModal").modal("show");
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.error("Failed to fetch user data:", textStatus, errorThrown);
            alert("An error occurred. Please try again.");
        });
    });

    $(document).on("click", ".delete-btn", function () {
        let student_id = $(this).data("id");

        if (!student_id) {
            alert("Invalid user ID.");
            return;
        }

        if (confirm("Are you sure you want to delete this user?")) {
            $.ajax({
                url: "delete.php",
                type: "POST",
                dataType: "json",
                data: { id: student_id }
            })
                .done(function (result) {
                    if (result.res === "success") {
                        alert("User deleted successfully!");
                        loadUsers();
                    } else {
                        alert("Error: " + (result.message || "Unable to delete user."));
                    }
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    console.error("Failed to delete user:", textStatus, errorThrown);
                    alert("An error occurred. Please try again.");
                });
        }
    });
});
