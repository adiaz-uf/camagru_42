document.addEventListener("DOMContentLoaded", async function() {
  const options = document.querySelectorAll('.toggle-switch .option');
  const toggleSwitch = document.querySelector('.toggle-switch');

  try {
    const response = await fetch(`${window.location.origin}/backend/app/get_user_data.php`, { credentials: 'include' });
    if (!response.ok) throw new Error('Not logged in or error fetching data');
    const data = await response.json();

    // Show username and email
    document.getElementById('username-display').textContent = data.username;
    document.getElementById('email-display').textContent = data.email;

    // Show switch
    options.forEach(o => o.classList.remove('active'));
    const activeOption = Array.from(options).find(o => o.dataset.value === String(data.notifications));
    if (activeOption) activeOption.classList.add('active');
    toggleSwitch.classList.add('visible');

  } catch (error) {
    console.error('Error fetching user data:', error);
    window.location.href = 'login.html';
  }
  options.forEach(option => {
    option.addEventListener('click', async () => {
      options.forEach(o => o.classList.remove('active'));
      option.classList.add('active');

      const value = option.dataset.value;

      try {
        const response = await fetch(`${window.location.origin}/backend/app/update_notifications.php`, {
          method: 'POST',
          credentials: 'include',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ notifications: value === "1" ? 1 : 0 })
        });
        const data = await response.json();
        if (!data.success) alert('Error actualizando la configuración');
      } catch (error) {
        alert('Error al conectar con el servidor');
      }
    });
  });
});

// Change username
document.getElementById('change-username').addEventListener('submit', function(e) {
    e.preventDefault();
    const newUsername = document.getElementById('username').value;

    fetch(`${window.location.origin}/backend/app/update_username.php`, {
        method: 'POST',
        body: new URLSearchParams({ username: newUsername })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('username-display').textContent = newUsername;
            alert('Username updated successfully');
        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error updating username');
    });
});

// Change email
document.getElementById('change-email').addEventListener('submit', function(e) {
    e.preventDefault();
    const newEmail = document.getElementById('email').value;

    fetch(`${window.location.origin}/backend/app/update_email.php`, {
        method: 'POST',
        body: new URLSearchParams({ email: newEmail })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('email-display').textContent = newEmail;
            alert('Email updated successfully');
        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error updating email');
    });
});

// Change password
document.getElementById('change-password').addEventListener('submit', function(e) {
    e.preventDefault();
    const oldPassword = document.getElementById('old-password').value;
    const newPassword = document.getElementById('new-password').value;

    fetch(`${window.location.origin}/backend/app/update_password.php`, {
        method: 'POST',
        body: new URLSearchParams({ old_password: oldPassword, new_password: newPassword })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Password updated successfully');
        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error updating password');
    });
});