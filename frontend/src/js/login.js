document.addEventListener("DOMContentLoaded", function() {
    // Selecciona el formulario de inicio de sesión
    const loginForm = document.querySelector('form');
    
    // Maneja el evento de envío del formulario
    loginForm.addEventListener('submit', function(event) {
        event.preventDefault(); // Evita que se envíe el formulario automáticamente
        
        // Obtiene los valores de los campos del formulario
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        
        // Validación básica del formulario
        if (username === "" || password === "") {
            alert("Please fill in both fields.");
            return;  // Si los campos están vacíos, no enviamos la solicitud
        }
        
        // Crear objeto con los datos a enviar
        const data = {
            username: username,
            password: password
        };
        
        // Realizar la solicitud AJAX para enviar los datos de inicio de sesión
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
                window.location.href = '/html/home.html';
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
