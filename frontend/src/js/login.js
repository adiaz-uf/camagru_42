document.addEventListener("DOMContentLoaded", function() {
    const loginForm = document.querySelector('form');
    
    loginForm.addEventListener('submit', function(event) {
        event.preventDefault(); 
    
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        
        if (username === "" || password === "") {
            alert("Please fill in both fields.");
            return; 
        }

        const data = {
            username: username,
            password: password
        };
        fetch(`${window.location.origin}/backend/app/login.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                loginForm.reset();
                window.location.href = '/html/galery.html';
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong. Please try again later.');
        });
    });
});
