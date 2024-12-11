const uploadInit = () => {
    const title = $id("wallpaper-title");
    const titleLength = $id("title-length");
    const descriptionLength = $id("description-length");
    const description = $id("wallpaper-description");
    const wallpaperUpload = $id("wallpaper-upload");
    const wallpaperPreview = $id("wallpaper-preview");
    const uploadWallpaper = $id("save-wallpaper");
    let wallData = ""; // this can save wallpaper data    

    const validatedValues = {
        title: [
            "title",
            false
        ],
        description: [
            "description",
            false
        ]
    }

    // create a object of declared form variables
    const elements = {
        title,
        description
    }
    const otherContainer = {
        title: titleLength,
        description: descriptionLength
    }

    // loop through all form variable that need validation
    Object.keys(validatedValues).forEach(key => {
        elements[key].addEventListener("keyup", e => {
            otherContainer[key].innerText = e.target.value.length;
            let validate = Validator.validate(validatedValues[key][0], e.target.value);
            checkInputValue(elements[key], validate);
            validatedValues[key][1] = validate;
        })
    })

    // wallpaper preview
    wallpaperUpload.addEventListener("change", (e) => {
        wallData = e.target.files[0];
        const readImg = new FileReader();
        readImg.readAsDataURL(wallData);

        readImg.onload = (img) => {
            wallpaperPreview.setAttribute("src", img.target.result);
        }
        readImg.onerror = (e) => {
            Toast.Error(e);
        }

    })

    // Upload Wallpaper
    uploadWallpaper.addEventListener("click", (e) => {
        if(
            validatedValues.title[1] &&
            validatedValues.description[1] &&
            wallData
        ){
            const form = new FormData();
            form.append("title", title.value);
            form.append("description", description.value);
            form.append("wallpaper", wallData);
            form.append("type", "regular");

            e.target.innerText = "Wait ...";
            e.target.setAttribute("disabled", "true");

            fetchDataVersion2(`/api/upload`, "POST", form)
            .then(response => {
                e.target.innerHTML = "Upload Wallpaper";
                e.target.removeAttribute("disabled");

                if(isValidJsonData(response)){
                    if(response["type"] == "error"){
                        Toast.Error(response["message"]);
                    } else if(response["type"] == "success"){
                        Toast.Success(response["message"]);
                        // reset all values
                        title.value = description.value = wallData = "";
                        wallpaperPreview.setAttribute("src", wallpaperPreview.getAttribute("data-src"));
                    } else{
                        throw new Error("Something went wrong");
                    }
                } else{
                    throw new Error("Reload page, or Try again later");
                }
            })
            .catch(err => {
                Toast.Error(err.message);
            })

        } else{
            Toast.Error("Enter all information first and then upload");
        }
    })
    




}


document.readyState === "interactive" ? uploadInit() : document.addEventListener("DOMContentLoaded", uploadInit());