document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.getElementById('signupForm');
    const errorMessage = document.getElementById('errorMessage');

    signupForm.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        // Reset error message
        errorMessage.style.display = 'none';
        errorMessage.innerHTML = '';

        // Collect form data
        const formData = new FormData(signupForm);

        // Send AJAX request
        fetch('signup.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Successful signup
                errorMessage.style.color = '#2ecc71';
                errorMessage.innerHTML = data.message;
                errorMessage.style.display = 'block';

                // Redirect after a short delay
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            } else {
                // Handle errors
                errorMessage.style.color = '#e74c3c';
                if (Array.isArray(data.errors)) {
                    errorMessage.innerHTML = data.errors.join('<br>');
                } else {
                    errorMessage.innerHTML = 'An unexpected error occurred.';
                }
                errorMessage.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            errorMessage.style.color = '#e74c3c';
            errorMessage.innerHTML = 'Network error. Please try again.';
            errorMessage.style.display = 'block';
        });
    });
});