document.getElementById("reset-form").addEventListener("submit", async function (e) {
    e.preventDefault();

    const email = document.getElementById("email").value.trim();
    const messageBox = document.getElementById("message");
    messageBox.textContent = '';

    if (!email) {
        messageBox.textContent = "Please enter your email.";
        return;
    }

    try {
        const res = await fetch(`${window.location.origin}/backend/app/send_reset_email.php`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ email })
        });

        const data = await res.json().catch(() => {
            throw new Error("Invalid JSON response");
        });

        if (data.success) {
            messageBox.style.color = "lightgreen";
            messageBox.textContent = data.message;
        } else {
            messageBox.style.color = "red";
            messageBox.textContent = data.message || "An error occurred. Please try again.";
        }

    } catch (error) {
        console.error("Fetch error:", error);
        messageBox.style.color = "red";
        messageBox.textContent = "Something went wrong. Please try again later.";
    }
});