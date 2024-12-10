const contactUsInit = () => {
    const name = $id("your-name");
    const email = $id("your-mail");
    const country = $id("your-country");
    const subject = $id("your-subject");
    const message = $id("your-message");

    const validatedValues = {
        name: [
            "string",
            false
        ],
        email: [
            "email",
            false
        ],
        country: [
            "country",
            false
        ],
        subject: [
            "string",
            false
        ],
        message: [
            "msg",
            false
        ]
    }
    
    const elements = {
        name,
        email,
        country,
        subject,
        message
    }

    Object.keys(validatedValues).forEach(key => {
        elements[key].addEventListener("keyup", (e) => {
            let validate = Validator.validate(validatedValues[key][0], e.target.value);
            checkInputValue(elements[key], validate);
            validatedValues[key][1] = validate;
        })
    })

    $id("sendMessageBtn").addEventListener("click", (e) => {
        if(
            validatedValues.name[1] &&
            validatedValues.email[1] &&
            validatedValues.country[1] &&
            validatedValues.subject[1] &&
            validatedValues.message[1]
        ){
            const form = new FormData();
            form.append("name", name.value);
            form.append("email", email.value);
            form.append("country", country.value);
            form.append("subject", subject.value);
            form.append("message", message.value);

            e.target.innerHTML = "Wait ...";
            e.target.setAttribute("disabled", "true");

            fetchDataVersion2(`${BaseURI}/contact`, "POST", form)
            .then(response => {
                e.target.innerHTML = "Sign in";
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
        } else{
            Toast.Error("Email or Password Invalid");
        }
    })

}

document.readyState == "interactive"
    ? contactUsInit()
    : document.addEventListener("DOMContentLoaded", contactUsInit());