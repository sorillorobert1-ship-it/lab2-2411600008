const loginButton = document.getElementById("loginBtn");
const loginAlert = document.getElementById("loginAlert");

function showAlert(message, type) {
    loginAlert.textContent = message;
    loginAlert.className = `alert alert-${type}`;
}

loginButton.addEventListener("click", function () {
    const username = document.getElementById("username").value.trim();
    const password = document.getElementById("password").value;

    if (username === "admin" && password === "password123") {
        localStorage.setItem("loggedIn", "true");
        localStorage.setItem("username", username);
        localStorage.setItem("loginTime", new Date().toISOString());

        showAlert("Login successful! Opening dashboard...", "success");

        setTimeout(() => {
            window.location.href = "dashboard.html";
        }, 500);
    } else {
        showAlert("Invalid credentials. Please use admin / password123.", "danger");
    }
});
