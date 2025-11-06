//Switch between login and signup forms
function toggleForm(form) {
if (form === 'signup') {
    document.getElementById('login-form').style.display = 'none';
    document.getElementById('signup-form').style.display = 'block';
} else {
    document.getElementById('login-form').style.display = 'block';
    document.getElementById('signup-form').style.display = 'none';
}
}

// Sign up user and store in localStorage
function signup() {
    const name = document.getElementById('signupName').value.trim();
    const email = document.getElementById('signupEmail').value.trim();
    const password = document.getElementById('signupPassword').value.trim();
    const role = document.getElementById('signupRole').value;

    const users = JSON.parse(localStorage.getItem('users')) || [];

if (users.find(u => u.email === email)) {
    alert('Email already registered!');
    return;
}

users.push({ name, email, password, role });
localStorage.setItem('users', JSON.stringify(users));

alert('Account created successfully! Please log in.');
toggleForm('login');
}

//Login user and redirect based on role
function login() {
    const email = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPassword').value.trim();

    const users = JSON.parse(localStorage.getItem('users')) || [];
    const user = users.find(u => u.email === email && u.password === password);

if (!user) {
    alert('Invalid email or password!');
    return;
}

localStorage.setItem('currentUser', JSON.stringify(user));

if (user.role === 'fi') {
    window.location.href = 'fi-dashboard.html';
} else {
    alert(`Welcome ${user.name}! Dashboard for ${user.role} coming soon.`);
}
}

//Logout user and go back to login page
function logout() {
localStorage.removeItem('currentUser');
alert('You have logged out successfully.');

  //Redirect based on which version is open
if (window.location.pathname.includes('.php')) {
    window.location.href = 'index.php';
} else {
    window.location.href = 'index.html';
}
}

//When dashboard page is open
document.addEventListener('DOMContentLoaded', function () {
const currentUser = JSON.parse(localStorage.getItem("currentUser"));

  //If user is logged in and on the dashboard page
if (currentUser && (window.location.pathname.includes("fi-dashboard.html") || window.location.pathname.includes("fi-dashboard.php"))) {

    if (currentUser.role !== "fi") {
    alert("Access denied. Please log in as a Faculty Intern.");
    if (window.location.pathname.includes(".php")) {
        window.location.href = "index.php";
    } else {
        window.location.href = "index.html";
    }
    return;
    }

    //Display user information
    document.getElementById("welcomeMessage").textContent = `Welcome, ${currentUser.name}!`;
    document.getElementById("profileName").textContent = currentUser.name;

    // Dashboard sample data
    const dashboardData = {
    courses: ["Introduction to Computing", "Data Structures", "Web Technologies"],
    sessions: ["Week 1 - Attendance", "Week 2 - Lab Participation", "Week 3 - Midsem prep"],
    reports: ["Attendance Report", "Lab Report", "Performance Report"]
    };

    //Load into sections
    document.getElementById("courseList").innerHTML = dashboardData.courses.map(c => `<li>${c}</li>`).join("");
    document.getElementById("sessionList").innerHTML = dashboardData.sessions.map(s => `<li>${s}</li>`).join("");
    document.getElementById("reportList").innerHTML = dashboardData.reports.map(r => `<li>${r}</li>`).join("");
}
});

//Switch visible dashboard section
function showSection(sectionId) {
    document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active'));
    document.getElementById(sectionId).classList.add('active');
}
