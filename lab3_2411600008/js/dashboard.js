// Protect the dashboard page.
if (localStorage.getItem("loggedIn") !== "true") {
    window.location.href = "index.html";
}

const userName = localStorage.getItem("username") || "Admin";
document.getElementById("userName").textContent = userName;

// Time-based greeting.
const hour = new Date().getHours();
let greeting = "Good evening";
if (hour < 12) {
    greeting = "Good morning";
} else if (hour < 18) {
    greeting = "Good afternoon";
}
document.getElementById("greeting").textContent = `${greeting}, ${userName}!`;

// Theme-specific hotel statistics.
const stats = [
    { title: "Today's Reservations", value: "24" },
    { title: "Available Rooms", value: "18" },
    { title: "Occupied Rooms", value: "42" },
    { title: "Today's Revenue", value: "₱86,500" }
];

stats.forEach((stat, index) => {
    const number = index + 1;
    document.getElementById(`stat${number}-title`).textContent = stat.title;
    document.getElementById(`stat${number}-value`).textContent = stat.value;
});

// Theme-specific activity table.
const activities = [
    { guest: "Maria Santos", room: "Deluxe 204", activity: "New reservation", date: "Today, 8:30 AM", status: "Confirmed" },
    { guest: "John Cruz", room: "Suite 501", activity: "Checked in", date: "Today, 9:15 AM", status: "Checked In" },
    { guest: "Ana Reyes", room: "Standard 112", activity: "Reservation updated", date: "Today, 10:20 AM", status: "Updated" },
    { guest: "Mark Dela Cruz", room: "Deluxe 306", activity: "Checked out", date: "Today, 11:00 AM", status: "Completed" }
];

const activityTable = document.getElementById("activityTable");

activities.forEach(item => {
    const row = document.createElement("tr");
    row.innerHTML = `
        <td class="fw-semibold">${item.guest}</td>
        <td>${item.room}</td>
        <td>${item.activity}</td>
        <td>${item.date}</td>
        <td><span class="badge status-badge">${item.status}</span></td>
    `;
    activityTable.appendChild(row);
});

// Logout clears the simulated authentication state.
document.getElementById("logoutBtn").addEventListener("click", function () {
    localStorage.removeItem("loggedIn");
    localStorage.removeItem("username");
    localStorage.removeItem("loginTime");
    window.location.href = "index.html";
});
