const sitemapInit = () => {
      $id("update-sitemap").addEventListener("click", (e) => {
        e.target.innerText = "Wait ...";
        e.target.setAttribute("disabled", "true");

        fetchDataVersion2(`/api/sitemap`, "POST", form)
        .then(response => {
            e.target.innerHTML = "Update Sitemap";
            e.target.removeAttribute("disabled");

            if(isValidJsonData(response)){
                if(response["type"] == "error"){
                    Toast.Error(response["message"]);
                } else if(response["type"] == "success"){
                    Toast.Success(response["message"]);
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
    })
    
}


document.readyState == "interactive" ? sitemapInit() : document.addEventListener("DOMContentLoaded", sitemapInit);