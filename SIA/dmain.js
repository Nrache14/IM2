$(document).ready(function() {

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

    // Fetch and display data
    function loadUsers() {
        $.ajax({
            url: "student.php",
            method: "GET",
            dataType: "json"
        }).done(function(data) {
            let parent = $("#tableBody");
            parent.empty();

            data.forEach((item, index) => {
                let age = calculateAge(item.birthdate); 

                parent.append(`
                    <tr>
                        <td>${index + 1}</td>
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
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error("Failed to fetch data:", textStatus, errorThrown);
        });
    }

    loadUsers();

    $("#addUserForm").on("submit", function(event) {
        event.preventDefault();

        const birthdate = $("#InputBirthdate").val();
        const age = calculateAge(birthdate); 

        if (age === "N/A") {
            alert("Invalid birthdate! Please enter a valid date.");
            return;
        }

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
                birthdate: birthdate
            }
        }).done(function(result) {
            if (result.res === "success") {
                alert("User added successfully!");
                $("#createModal").modal('hide');
                $("#addUserForm")[0].reset(); 
                loadUsers(); 
            } else {
                alert("Error: " + (result.message || "Unable to add user."));
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error("Failed to add user:", textStatus, errorThrown);
        });
    });

    $("#editUserForm").on("submit", function(event) {
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
        }).done(function(result) {
            if (result.res === "success") {
                alert("User updated successfully!");
                $("#editModal").modal('hide');
                loadUsers(); // Reload user list
            } else {
                alert("Error: " + (result.msg || "Unable to update user."));
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error("Failed to update user:", textStatus, errorThrown);
            alert("An error occurred. Please try again.");
        });
    });
    

    $(document).on("click", ".edit-btn", function() {
        let student_id = $(this).data("id");
    
        $.ajax({
            url: "student.php", 
            type: "GET",
            dataType: "json"
        }).done(function(data) {
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
    
            $("#editModal").modal('show');
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error("Failed to fetch user data:", textStatus, errorThrown);
            alert("An error occurred. Please try again.");
        });
    });
    

    $(document).on("click", ".delete-btn", function() {
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
            .done(function(result) {
                if (result.res === "success") {
                    alert("User deleted successfully!");
                    loadUsers();
                } else {
                    alert("Error: " + (result.message || "Unable to delete user."));
                }
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                console.error("Failed to delete user:", textStatus, errorThrown);
                alert("An error occurred. Please try again.");
            });
        }
    });
});
