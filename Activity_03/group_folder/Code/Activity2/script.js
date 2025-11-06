function toggleForm(form){
    if (form === 'signup'){
        document.getElementById('login-form').style.display = 'none';
        document.getElementById('signup-form').style.display = 'block';
    }
    else {
        document.getElementById('login-form').style.display = 'block';
        document.getElementById('signup-form').style.display = 'none';
    }
}
function signup(){
    const name = document.getElementById('signupName').value;
    const email = document.getElementById('signupEmail').value;
    const password = document.getElementById('signupPassword').value;
    const role = document.getElementById('signupRole').value;
    const users = JSON.parse(localStorage.getItem('users')) || [];

    
    if (users.find(u=> u.email === email)){
        alert('Email already registered');
        return;
    }
    users.push({name,email,password,role});
    localStorage.setItem('users', JSON.stringify(users));
    alert('Account created! Please login.');
    toggleForm('login');
    
}
function login(){
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    const users = JSON.parse(localStorage.getItem('users')) || [];
    const user = users.find(u => u.email === email && u.password === password);

    if (!user) {
    alert('Invalid credentials!');
    return;
  }
  localStorage.setItem('currentUser', JSON.stringify(user));
   if (user.role === 'fi') {
    window.location.href = 'fi-dashboard.html';
  } else {
    alert(`Welcome ${user. name}! Dashboard for ${user.role} coming soon.`);
  }


}