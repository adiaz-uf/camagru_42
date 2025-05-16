export function protectRoute() {
    return fetch(`${window.location.origin}/backend/app/get_user_data.php`)
        .then(response => {
            if (response.status === 401) {
                window.location.href = 'login.html';
                throw new Error('Not authenticated');
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('page-blocker').style.display = 'none';
        })
        .catch(err => {
            console.error('Auth check failed:', err);
        });
}