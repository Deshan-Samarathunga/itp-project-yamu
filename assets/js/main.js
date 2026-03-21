// Navbar ============================================================================
var navbar = document.getElementById('navbar');
function showMenu(){
    navbar.style.right = "0";
}
 function hideMenu(){
    navbar.style.right = "-300px";
}

// Sticky Navbar ============================================================================
const nav = document.querySelector("#nav");
const subNav = document.querySelector(".subnav-content");

window.onscroll = function() {
    scrollFunction()
};

function scrollFunction() {
    if (document.body.scrollTop > 42 || document.documentElement.scrollTop > 42) {
        nav.classList.add("scrolled-down");
        subNav.style.top="65px";
    } else {
        nav.classList.remove("scrolled-down");
        subNav.style.top="100px";
    }
}

// // Alert Box ============================================================================
// var alertElement = document.querySelector(".alert");
// var timeout = 3000;
// function hideAlert() {
//     // Hide or remove the alert element here
//     alertElement.style.display = "none"; 
// }

// // Set the timeout to hide or remove the alert element
// setTimeout(hideAlert, timeout);



