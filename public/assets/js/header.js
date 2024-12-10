const hamburgerInit = () => {
    // Search Box Functions
    const searchBox = $id("search-box-container");
    const searchBtn = $id("search-box-btn");
    searchBtn.addEventListener("click", () => {
        searchBox.classList.toggle("h-0");
        $id("inputSearchTag").focus();
    })

    // Hamburger Btn Functions
    const hamburgerBtn = $id("hamburger-btn");
    const navigationLink = $id("navigation-link");
    hamburgerBtn.addEventListener("click", () => {
        let nl = navigationLink;
        nl.classList.contains("left-0") ?
        (() => {
            nl.classList.add("-left-full");
            nl.classList.remove("left-0");
        })():
        (() => {
            nl.classList.remove("-left-full");
            nl.classList.add("left-0");
        })();
    })
}

document.readyState == "interactive" ? hamburgerInit() : document.addEventListener('DOMContentLoaded', hamburgerInit());