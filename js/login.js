document.addEventListener('DOMContentLoaded', function() {
    // Get login form 
    const loginForm = document.querySelector('form');
    
    // Add event listener for form submission
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        // Get input values
        const email = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;

        // Basic client-side validation
        if (!email || !password) {
            alert('Please enter both email and password');
            return;
        }

        // Disable submit button to prevent multiple submissions
        const submitButton = e.target.querySelector('input[type="submit"]');
        submitButton.disabled = true;
        submitButton.value = 'Logging in...';

        // Prepare data for sending
        const data = {
            email: email,
            password: password
        };

        // Send login request
        fetch('login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            // Re-enable submit button
            submitButton.disabled = false;
            submitButton.value = 'Login';

            if (result.status === 'success') {
                // Create success message element
                const messageDiv = document.createElement('div');
                messageDiv.style.color = 'green';
                messageDiv.style.textAlign = 'center';
                messageDiv.style.marginTop = '10px';
                messageDiv.textContent = result.message;
                loginForm.appendChild(messageDiv);

                // Redirect after a short delay
                setTimeout(() => {
                    window.location.href = result.redirect;
                }, 1500);
            } else {
                // Create error message element
                const messageDiv = document.createElement('div');
                messageDiv.style.color = 'red';
                messageDiv.style.textAlign = 'center';
                messageDiv.style.marginTop = '10px';
                messageDiv.textContent = result.message;
                
                // Remove any existing message
                const existingMessage = loginForm.querySelector('div');
                if (existingMessage) {
                    existingMessage.remove();
                }
                
                // Append new error message
                loginForm.appendChild(messageDiv);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Re-enable submit button
            submitButton.disabled = false;
            submitButton.value = 'Login';

            // Create network error message
            const messageDiv = document.createElement('div');
            messageDiv.style.color = 'red';
            messageDiv.style.textAlign = 'center';
            messageDiv.style.marginTop = '10px';
            messageDiv.textContent = 'Network error. Please try again.';
            
            // Remove any existing message
            const existingMessage = loginForm.querySelector('div');
            if (existingMessage) {
                existingMessage.remove();
            }
            
            // Append new error message
            loginForm.appendChild(messageDiv);
        });
    });
});