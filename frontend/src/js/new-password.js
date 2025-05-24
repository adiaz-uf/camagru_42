document.getElementById("new-password-form").addEventListener("submit", function (e) {
    e.preventDefault();

    const password = document.getElementById("new-password").value;
    const token = new URLSearchParams(window.location.search).get("token");
    const messageBox = document.getElementById("message");

    fetch(`${window.location.origin}/backend/app/handle_new_password.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ token, password })
    })
    .then(response => {
        return response.json().catch(() => {
            throw new Error("Invalid JSON response");
        });
    })
    .then(data => {
        if (data.success) {
            messageBox.style.color = "lightgreen";
            messageBox.textContent = data.message;
            setTimeout(() => {
                window.location.href = "/html/login.html";
            }, 2000);
        } else {
            messageBox.style.color = "red";
            messageBox.textContent = data.message || "Something went wrong.";
        }
    })
    .catch(error => {
        console.error("Error:", error);
        messageBox.style.color = "red";
        messageBox.textContent = "Something went wrong. Please try again later.";
    });
});