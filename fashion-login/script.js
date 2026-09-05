const passwordInput = document.querySelector("#password");
const togglePassword = document.querySelector("#togglePassword");
const loginForm = document.querySelector(".login-card");

togglePassword.addEventListener("click", () => {
  const isHidden = passwordInput.type === "password";
  passwordInput.type = isHidden ? "text" : "password";
  togglePassword.textContent = isHidden ? "Ẩn" : "Hiện";
  togglePassword.setAttribute("aria-label", isHidden ? "Ẩn mật khẩu" : "Hiện mật khẩu");
});

loginForm.addEventListener("submit", (event) => {
  event.preventDefault();
  const button = loginForm.querySelector(".primary-button");
  button.textContent = "Đang đăng nhập...";

  window.setTimeout(() => {
    button.textContent = "Đăng nhập";
  }, 1200);
});
