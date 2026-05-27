// public/js/app.js

// Function to check if user is authenticated and setup basic UI
async function checkAuth() {
    await checkAuthAndReturnUser();
}

// Function to check if user is authenticated, setup basic UI, and return the user object
async function checkAuthAndReturnUser() {
    try {
        const response = await fetch('/api/login.php', { method: 'GET' });
        if (!response.ok) {
            // Not authenticated
            if (window.location.pathname.indexOf('index.html') === -1 && window.location.pathname !== '/') {
                window.location.href = 'index.html';
            }
            return null;
        }

        const data = await response.json();
        const user = data.user;

        // Setup UI if we are in a protected page
        const nameDisplay = document.getElementById('userNameDisplay');
        if (nameDisplay) {
            nameDisplay.innerText = `${user.name} (${user.role})`;
        }

        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', async () => {
                await fetch('/api/login.php', { method: 'DELETE' });
                window.location.href = 'index.html';
            });
        }

        return user;
    } catch (error) {
        console.error("Error checking auth", error);
        return null;
    }
}
