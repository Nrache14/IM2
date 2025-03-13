$(document).ready(function () {

    // Fetch and display user data
    function loadUsers() {
        $.ajax({
            url: "student.php",
            method: "GET", // Ensure to use the method here
            dataType: "json" // Expect JSON response
        })
        .done(function(data) {
            let parent = $("#tableBody");
            parent.empty(); // Clear existing rows

            if (Array.isArray(data) && data.length) { // Check if data is an array and not empty
                data.forEach((item, index) => {
                    let age = calculateAge(item.birthdate); 
                    parent.append(`
                        <tr>
                            <td>${index + 1}</td>
                            <td><img src="${item.profile_image}" alt="Profile" style="width:50px;height:50px;"></td>
                            <td>${item.first_name || 'N/A'}</td>
                            <td>${item.last_name || 'N/A'}</td>
                            <td>${item.email || 'N/A'}</td>
                            <td>${item.gender || 'N/A'}</td>
                            <td>${item.course || 'N/A'}</td>
                            <td>${item.user_address || 'N/A'}</td>
                            <td>${age}</td> 
                            <td>
                                <button class="btn btn-dark btn-sm edit-btn" data-id="${item.student_id}">Edit</button>
                                <button class="btn btn-outline-dark btn-sm delete-btn" data-id="${item.student_id}">Delete</button>
                            </td>
                        </tr>
                    `);
                });
            } else {
                parent.append('<tr><td colspan="10">No users found</td></tr>'); // Message when table is empty
            }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            // console.error("Failed to fetch data:", textStatus, errorThrown);
            alert("An error occurred while fetching user data. Please tryyyyyyyyyy again.");
        });
    }

    loadUsers();

    // Handle adding a new user
$("#addUserForm").on("submit", function (event) {
    event.preventDefault(); // Prevent default form submission

    const formData = new FormData(this);
    const birthdate = $("#InputBirthdate").val();
    const age = calculateAge(birthdate);

    // Validate age
    if (age === "N/A") {
        alert("Invalid birthdate! Please enter a valid date.");
        return;
    }

    $.ajax({
        url: "create.php",  
        type: "POST",
        processData: false, 
        contentType: false, 
        data: formData
    })
    .done(function (result) {
        if (result.res === "success") {
            alert("User added successfully!");
            let newUser = `
                <tr>
                    <td>${$("#tableBody").children().length + 1}</td>
                    <td><img src="${result.image}" alt="Profile" style="width:50px;height:50px;"></td>
                    <td>${$("#InputFname").val()}</td>
                    <td>${$("#InputLname").val()}</td>
                    <td>${$("#InputEmail").val()}</td>
                    <td>${$("#InputGender").val()}</td>
                    <td>${$("#InputCourse").val()}</td>
                    <td>${$("#InputAddress").val()}</td>
                    <td>${age}</td>
                    <td>
                        <button class="btn btn-dark btn-sm edit-btn" data-id="${result.insertedId}">Edit</button>
                        <button class="btn btn-outline-dark btn-sm delete-btn" data-id="${result.insertedId}">Delete</button>
                    </td>
                </tr>
            `;
            $("#tableBody").append(newUser);
            $("#createModal").modal('hide');
            $("#addUserForm")[0].reset(); // Reset form
        } else {
            alert("Error: " + (result.message || "Unable to add user."));
        }
    })
    .fail(function (jqXHR, textStatus, errorThrown) {
        console.error("Failed to add user:", textStatus, errorThrown);
        alert("An error occurred. Please try again.");
    });
});

    // Handle editing user (remains unchanged)
    $("#editUserForm").on("submit", function (event) {
        event.preventDefault();

        $.ajax({
            url: "update.php",
            type: "POST",
            dataType: "json",
            data: {
                id: $("#editId").val(),
                fname: $("#editFname").val(),
                lname: $("#editLname").val(),
                email: $("#editEmail").val(),
                gender: $("#editGender").val(),
                course: $("#editCourse").val(),
                address: $("#editAddress").val(),
                birthdate: $("#editBirthdate").val()
            }
        })
        .done(function (result) {
            if (result.res === "success") {
                alert("User updated successfully!");
                $("#editModal").modal('hide');
                loadUsers(); // Reload user list
            } else {
                alert("Error: " + (result.msg || "Unable to update user."));
            }
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            console.error("Failed to update user:", textStatus, errorThrown);
            alert("An error occurred. Please try again.");
        });
    });
    
    // Open edit modal
    $(document).on("click", ".edit-btn", function () {
        let student_id = $(this).data("id");

        $.ajax({
            url: `student.php?id=${student_id}`, // Use the specific id to get user data
            type: "GET",
            dataType: "json"
        })
        .done(function (data) {
            let user = data.find(u => u.student_id == student_id);
            if (!user) {
                alert("User not found.");
                return;
            }

            // Populate edit form fields
            $("#editId").val(user.student_id);
            $("#editFname").val(user.first_name);
            $("#editLname").val(user.last_name);
            $("#editEmail").val(user.email);
            $("#editGender").val(user.gender);
            $("#editCourse").val(user.course);
            $("#editAddress").val(user.address);
            $("#editBirthdate").val(user.birthdate);

            $("#editModal").modal('show'); // Show edit modal
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            console.error("Failed to fetch user data:", textStatus, errorThrown);
            alert("An error occurred while fetching user data. Please try again.");
        });
    });
    
    // Handle deleting a user
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
                    loadUsers(); // Reload user list
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

    // Function to calculate age from birthdate
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
});