document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const user = document.getElementById("username").value;
  const pass = document.getElementById("password").value;

  const storedUser = localStorage.getItem(user);

  if (
    (user === "admin" && pass === "admin123") ||
    (user === "docente" && pass === "docente123") ||
    (storedUser && storedUser === pass)
  ) {
    alert("Inicio de sesión exitoso");
    window.location.href = "dashboard.html"; // redirige al dashboard
  } else {
    alert("Usuario o contraseña incorrecta");
  }
});

document.getElementById("signupForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const newUser = document.getElementById("newUser").value;
  const newPass = document.getElementById("newPass").value;

  if (localStorage.getItem(newUser)) {
    alert("Este usuario ya existe");
  } else {
    localStorage.setItem(newUser, newPass);
    alert("Usuario registrado con éxito. Ya puedes iniciar sesión.");
    document.getElementById("signupForm").style.display = "none";
    document.getElementById("loginForm").style.display = "block";
  }
});

document.getElementById("showSignup").addEventListener("click", function (e) {
  e.preventDefault();
  document.getElementById("loginForm").style.display = "none";
  document.getElementById("signupForm").style.display = "block";
});

document.getElementById("showLogin").addEventListener("click", function (e) {
  e.preventDefault();
  document.getElementById("signupForm").style.display = "none";
  document.getElementById("loginForm").style.display = "block";
});
