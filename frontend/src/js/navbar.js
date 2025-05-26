document.addEventListener('DOMContentLoaded', function() {
    const navHTML = `
    <nav class="navbar">
        <div class="logo">Camagru</div>
        <button class="menu-toggle" aria-label="Toggle menu" aria-expanded="false">
            &#9776;
        </button>
        <div class="nav-links">
            <a href="/html/galery.html">Galery</a>
            <a href="/html/edit.html">Edit Photo</a>
            <a href="/html/profile.html">Profile</a>
            <button class="logout-btn" onclick="location.href='/backend/app/logout.php'" aria-label="Logout">
                Logout
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M16 17v-3H7v-4h9V7l5 5-5 5z"/>
                    <path d="M4 4h9v2H4v12h9v2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/>
                </svg>
            </button>
        </div>
    </nav>
    `;
    
    document.querySelectorAll('.navbar-container').forEach(container => {
        container.innerHTML = navHTML;
        
        const menuToggle = container.querySelector('.menu-toggle');
        const navLinks = container.querySelector('.nav-links');
        
        menuToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            
            const isExpanded = navLinks.classList.contains('active');
            menuToggle.setAttribute('aria-expanded', isExpanded);
        });
        
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('active');
                menuToggle.setAttribute('aria-expanded', 'false');
            });
        });
    });
});