// script.js
document.getElementById('userForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('save_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('User saved successfully!');
            loadUsers(); // Refresh the user list
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
});

function loadUsers() {
    fetch('get_users.php')
    .then(response => response.json())
    .then(data => {
        const userList = document.getElementById('userList');
        userList.innerHTML = ''; // Clear the existing list

        data.forEach(user => {
            const userDiv = document.createElement('div');
            userDiv.innerHTML = `
                <p><strong>Name:</strong> ${user.name}</p>
                <p><strong>Email:</strong> ${user.email}</p>
                <img src="${user.profile_image}" alt="Profile Image" width="100"><hr>
            `;
            userList.appendChild(userDiv);
        });
    })
    .catch(error => console.error('Error:', error));
}

// Load users on page load
loadUsers();