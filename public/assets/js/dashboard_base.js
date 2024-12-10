

const userDashboard = () => {
    let hamburgerBtn = $id("hamburger-btn");
    let menu = $id("navigation-menu");

    hamburgerBtn.addEventListener("click", () => {
        menu.classList.contains("-left-full")
            ? classInterChanger("-left-full", "left-0", menu)
            : classInterChanger("left-0", "-left-full", menu);
    })
}

document.readyState == "interactive" 
    ? userDashboard() 
    : document.addEventListener("DOMContentLoaded", userDashboard());