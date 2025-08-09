// Check for welcome message in URL
window.addEventListener('DOMContentLoaded', (event) => {
    const urlParams = new URLSearchParams(window.location.search);
    const welcomeParam = urlParams.get('welcome');
    
    if (welcomeParam) {
        // Decode the username
        const username = decodeURIComponent(welcomeParam);
        
        // Create welcome message element
        const welcomeDiv = document.createElement('div');
        welcomeDiv.className = 'welcome-message';
        welcomeDiv.innerHTML = `
            <span class="welcome-text">Welcome, ${username}!</span>
            <button class="close-welcome">&times;</button>
        `;
        
        // Style the welcome message
        const style = document.createElement('style');
        style.textContent = `
            .welcome-message {
                position: fixed;
                top: 20px;
                right: 20px;
                background: linear-gradient(135deg, #4f46e5, #6366f1);
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                display: flex;
                align-items: center;
                z-index: 1000;
                animation: slideIn 0.5s ease-out;
            }
            
            .welcome-text {
                margin-right: 15px;
                font-weight: 500;
            }
            
            .close-welcome {
                background: none;
                border: none;
                color: white;
                font-size: 20px;
                cursor: pointer;
                padding: 0;
                line-height: 1;
            }
            
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
        
        // Add to body
        document.body.appendChild(welcomeDiv);
        
        // Close button functionality
        const closeBtn = welcomeDiv.querySelector('.close-welcome');
        closeBtn.addEventListener('click', () => {
            welcomeDiv.style.animation = 'slideOut 0.5s ease-out';
            setTimeout(() => welcomeDiv.remove(), 500);
            
            // Remove the welcome parameter from URL without reload
            const newUrl = window.location.href.split('?')[0];
            window.history.replaceState({}, document.title, newUrl);
        });
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (welcomeDiv.parentNode) {
                welcomeDiv.style.animation = 'fadeOut 0.5s ease-out';
                setTimeout(() => welcomeDiv.remove(), 500);
                
                // Remove the welcome parameter from URL without reload
                const newUrl = window.location.href.split('?')[0];
                window.history.replaceState({}, document.title, newUrl);
            }
        }, 5000);
    }
});
