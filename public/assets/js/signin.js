const sign_in = () => {
    const email = $id("user_email");
    const password = $id("user_password");
    const validatedValues = {
        email: [
            "email",
            false
        ],
        password: [
            "password",
            false
        ]
    }

   const elements = {
    email,
    password
   }

   Object.keys(validatedValues).forEach(key => {
    elements[key].addEventListener("keyup", (e) => {
        let validate = Validator.validate(validatedValues[key][0], e.target.value);
        checkInputValue(elements[key], validate);
        validatedValues[key][1] = validate;
    })
   })

    $id("sign_in_button").addEventListener("click", (e) => {
        if(validatedValues.email[1] && validatedValues.password[1]){
            const form = new FormData();
            form.append("email", email.value);
            form.append("password", password.value);

            e.target.innerHTML = "Wait ...";
            e.target.setAttribute("disabled", "true");

            fetchDataVersion2(`${BaseURI}/signin`, "POST", form)
            .then(response => {
                e.target.innerHTML = "Sign in";
                e.target.removeAttribute("disabled");

                if(isValidJsonData(response)){
                    if(response["type"] == "error"){
                        Toast.Error(response["message"]);
                    } else if(response["type"] == "success"){
                        Toast.Success(response["message"] + " in 3 seconds");
                        setTimeout(() => {
                            window.location.href = response["data"]["redirectionTo"]
                        }, 3000)
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
            Toast.Error("Email or Password Invalid");
        }
    })
}

document.readyState === "interactive" 
    ? sign_in() 
    : document.addEventListener("DOMContentLoaded", sign_in);