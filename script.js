// Disable right click
document.addEventListener("contextmenu", function (e) {
  e.preventDefault();
});

// Disable left click
// document.addEventListener("mousedown", function (e) {
//   if (e.button === 0) { // 0 = left button
//     e.preventDefault();
//   }
// });

// Disable drag
document.addEventListener("dragstart", function (e) {
  e.preventDefault();
});

// Disable text selection
document.addEventListener("selectstart", function (e) {
  e.preventDefault();
});

// Disable F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U, Ctrl+C/X/V/P
document.addEventListener("keydown", function (e) {
  const key = e.key.toLowerCase(); // normalize key

  // Block F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U
  if (
    key === "f12" ||
    (e.ctrlKey && e.shiftKey && (key === "i" || key === "j")) ||
    (e.ctrlKey && key === "u")
  ) {
    e.preventDefault();
  }

  // Block Ctrl+C, Ctrl+X, Ctrl+V, Ctrl+P
  if (
    e.ctrlKey && (key === "c" || key === "x" || key === "v" || key === "p")
  ) {
    e.preventDefault();
  }
});

// Disable copy, cut, paste
document.addEventListener("copy", function (e) {
  e.preventDefault();
});
document.addEventListener("cut", function (e) {
  e.preventDefault();
});
document.addEventListener("paste", function (e) {
  e.preventDefault();
});