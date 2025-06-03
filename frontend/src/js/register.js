document.addEventListener("DOMContentLoaded", function() {
    const registerForm = document.querySelector('form');

    registerForm.addEventListener('submit', function(event) {
        event.preventDefault();

        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm-password').value;

        if (username === "" || email === "" || password === "" || confirmPassword === "") {
            alert("Please fill in all fields.");
            return;
        }

        if (password !== confirmPassword) {
            alert("Passwords do not match.");
            return;
        }

        const data = { username, email, password };

        fetch(`${window.location.origin}/backend/app/register.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams(data)
        })
        .then(response => {
            return response.json().catch(() => {
                alert('Something went wrong. Please try again later.');
                throw new Error('Invalid JSON response');
            });
        })
        .then(data => {
            if (data.success) {
                alert(data.message);
                registerForm.reset();
                window.location.href = '/html/login.html';
            } else {
                alert(data.message || 'An error occurred during registration.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong. Please try again later.');
        });
    });
});
