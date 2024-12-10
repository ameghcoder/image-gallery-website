const option = {
    load : 25,
    gap: 15, 
    nextPage: 2,
    maxWidth: 300, 
    query: "",
    container: "wallpaperContainer",
    api: ""
};

const imgObserver = (time = 1) => {
    
    const intersectCallback = (entries, observer) => {
        entries.forEach(e => {
            if(e.isIntersecting){
                const img = e.target;
                img.setAttribute("src", img.getAttribute("data-src"));
                img.classList.remove("animate-pulse");
                img.classList.remove("wallpaperCardLazyLoad");
                observer.unobserve(e.target);
            }
        })
    };
    
    const observer = new IntersectionObserver(intersectCallback);
    let wallpapersClass = $all("img.wallpaperCardLazyLoad");
    wallpapersClass.forEach(e => {
        if(time == 2){
            console.log(e);
        }
        observer.observe(e);
    })
}

class WallpaperLayout {
    constructor(containerSelector = option.container) {
        this.container = $id(containerSelector);
        this.gap = option.gap;
        this.columns = 0;
        this.wallpaperWidth = 0;
        this.calculateColumnWidth();
        this.columnHeights = Array(this.columns).fill(0); // Track the height of each column
        
        // Recalculate on window resize
        window.addEventListener('resize', () => this.onResize());
    }

    calculateColumnWidth(){
        let max = Math.ceil(this.container.clientWidth / option.maxWidth);
        let maxGapCol = max == 1 ? 0 : max - 1;
        let cardWidth = Math.floor((this.container.clientWidth - (option.gap * maxGapCol))/max);
        
        this.columns = max;
        this.wallpaperWidth = cardWidth;
    }

    /**
     * Positions a list of wallpapers by calculating the `left` and `top` positions.
     * @param {NodeList | HTMLElement[]} wallpapers - The wallpapers to position.
     */
    setWallpapers(lastPosition = 0) {
        const wallpapers = $all(`#${option.container} > div`);

        for(let i = lastPosition; i < wallpapers.length; i++){
            let wallpaper = wallpapers[i];
            
            // Find the column with the minimum height
            const minHeight = Math.min(...this.columnHeights);
            const columnIndex = this.columnHeights.indexOf(minHeight);

            // Set the position of the wallpaper
            const left = columnIndex * (this.wallpaperWidth + this.gap);
            const top = minHeight;

            // Apply absolute positioning to the wallpaper
            wallpaper.setAttribute(
                "style",
                `width: ${this.wallpaperWidth}px;
                position: absolute;
                left: 0;
                top: 0;
                transform: translateX(${left}px) translateY(${top}px);
                `
            )

            // Update the height of the column where this wallpaper was placed
            const wallpaperHeight = wallpaper.offsetHeight;
            this.columnHeights[columnIndex] += wallpaperHeight + this.gap;
        }

        // Update the height of the container to make sure it fits the wallpapers
        this.updateContainerHeight();
    }

    /**
     * Update the container height to fit all wallpapers.
     */
    updateContainerHeight() {
        const maxHeight = Math.max(...this.columnHeights);
        this.container.style.height = `${maxHeight}px`;
    }

    /**
     * Handle window resize event by recalculating column widths and re-positioning wallpapers.
     */
    onResize() {
        this.calculateColumnWidth();
        this.columnHeights = Array(this.columns).fill(0); // Reset column heights
        this.setWallpapers(); // Re-position all wallpapers
    }

    /**
     * Add new wallpapers and position them.
     * @param {HTMLElement[]} newWallpapers - The list of new wallpapers to add.
     */
    addWallpapers(newWallpapers) {
        let lastPosition =  $all(`#${option.container} > div`);
        lastPosition = lastPosition.length;
        this.container.insertAdjacentHTML("beforeend", newWallpapers);
        this.setWallpapers(lastPosition); // Position the new wallpapers
    }
}


class checkBottomAndLoadMore{
    constructor(endCheckerId = "gallery-end-checkers"){
        this.endChecker = $id(endCheckerId);
        this.loader = $(`#${endCheckerId} > .loader`);
        this.noWallpaper = $(`#${endCheckerId} > .no-wallpaper`);
    }

    startObserver(wallpaperGalleryCreator){
        new IntersectionObserver((entries, observer) => {
            entries.forEach(e => {
                if(e.isIntersecting){
                    this.loader.classList.remove("invisible");
                    const form = new FormData();
                    form.append("query", option.query);
                    form.append("page", option.nextPage);
                    form.append("range", option.load);

                    fetchDataVersion2(option.api, "POST", form)
                    .then(resp => {
                        this.loader.classList.add("invisible");
                        if(isValidHTML(resp) && resp != "end"){
                            wallpaperGalleryCreator.addWallpapers(resp);

                            // Start Image observation
                            imgObserver(2);

                            // Start End Observer
                            this.startObserver(wallpaperGalleryCreator);
                            
                            // update nextpage value
                            option.nextPage += 1;
                        } else{
                            this.noWallpaper.classList.remove("invisible");
                        }
                    })
                    .catch()

                    observer.unobserve(e.target);
                }
            })
        }).observe(this.endChecker);
    }
}
const search = () => {
    let galleryCreator = new WallpaperLayout();
    galleryCreator.setWallpapers();

    let loadMore = new checkBottomAndLoadMore();
    loadMore.startObserver(galleryCreator);

    setTimeout(() => {
        // Start Image Observation
        imgObserver();
    }, 5000);

}

document.readyState == "interactive" ? search() : document.addEventListener('DOMContentLoaded', search());