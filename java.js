document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');

    form.addEventListener('submit', function(event) {
        let errorMessages = [];

        if (usernameInput.value.trim() === '') {
            errorMessages.push('Username is required.');
        }

        if (passwordInput.value.trim() === '') {
            errorMessages.push('Password is required.');
        }

        if (errorMessages.length > 0) {
            event.preventDefault();
            alert(errorMessages.join('\\n'));
        }
    });
});
