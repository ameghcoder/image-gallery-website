const $ = e => document.querySelector(e);
const $all = e => document.querySelectorAll(e);
const $id = e => document.getElementById(e);
/**
 * This function create html elements.
 * @param {string} e tag name that you want to create
 * @returns
 */
const $ele = e => document.createElement(e);
const BaseURI = "/api";

function fetchDataVersion2(url, method, data = null, btnId = null, validation = true) {
    /**
     * Changes the text of the button with the given ID based on the type parameter.
     * @param {number} type - The type of text change (0: loading, 1: default, -1: error).
     * @param {string} btnId - The ID of the button to update. who have to add a attribute with the name "data-default-text" and add default value of your button.
    */
    function changeButtonText(type, btnId) {
        let btn = $id(btnId);
        if (!btn) return;
        if (type === 0) {
            btn.innerHTML = "wait...";
        } else if (type === 1) {
            btn.innerHTML = btn.getAttribute("data-default-text");
        } else if (type === -1) {
            btn.innerHTML = "an error occurred";
        }
    }
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open(method, url, true);

        // Change button text to loading state if button ID is provided
        if (btnId !== null) changeButtonText(0, btnId);

        // Set CSRF token header if validation is true
        if (validation) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                xhr.setRequestHeader("X-CSRF-Token", csrfToken.getAttribute('content'));
            }
        }

        // Handle state changes of the XMLHttpRequest
        xhr.onreadystatechange = () => {
            if (xhr.readyState === 4) {
                if (xhr.status >= 200 && xhr.status < 300) {
                    if (btnId !== null) changeButtonText(1, btnId);
                    try {
                        const response = JSON.parse(xhr.responseText);
                        resolve(response);
                    } catch (e) {
                        resolve(xhr.responseText);
                    }
                } else {
                    if (btnId !== null) changeButtonText(-1, btnId);
                    reject(new Error(`Error: ${xhr.status} ${xhr.statusText}`));
                }
            }
        };

        xhr.onerror = (e) => {
            if (btnId !== null) changeButtonText(-1, btnId);
            reject(new Error("Error: Network Error"));
        };

        // Send the request with or without data based on the method
        if (method === "GET") {
            xhr.send();
        } else {
            xhr.send(data); // Send FormData directly
        }
    });
}

/**
 * Checks if a object is valid JSON.
 * @param {object} str - The object to check.
 * @returns {boolean} - Returns true if the string is valid JSON, otherwise false.
 */
function isValidJsonData(str) {
    try {
        JSON.stringify(str);
        return true;
    } catch (e) {
        return false;
    }
}

/**
 * Checks if a given string is valid JSON.
 * @param {string} str - The string to check.
 * @returns {boolean} - Returns true if the string is valid JSON, otherwise false.
 */
function isValidJsonString(str) {
    try {
        JSON.parse(str);
        return true;
    } catch (e) {
        return false;
    }
}

function isHTMLElement(element) {
    return element instanceof HTMLElement;
}

/**
 * This function helps you to interchnage classname from an element. Means you can remove a class and add a class at the same time.
 * @param {string} from Classname that you want to remove
 * @param {string} to Classname that you want to add
 * @param {HTMLElement} element element from which you want to interchange the classname
 */
function classInterChanger(from, to, element) {
    if (isHTMLElement(element)) {
        element.classList.remove(from);
        element.classList.add(to);
    }
}

function isValidHTML(html) {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    
    // Check for parsing errors
    const isErrorPresent = doc.querySelector('parsererror');
    return !isErrorPresent;
}
const checkInputValue = (inputField, validatedValue) => {
    if(!validatedValue){
        inputField.setAttribute("style", "border: 5px solid red")
    } else{
        inputField.removeAttribute("style");
    }

}
class Validator {
    static validate(type, data){
        let pattern = "";
        switch (type) {
            case "email":
                pattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
                break;
            case "string":
                pattern = /^[a-zA-Z\s]+$/;
                break;
            case "msg": 
                pattern = /^[a-zA-Z0-9 .,?!:;+\-*/=@#&_(){}\[\]'""]+$/;
                break;
            case "password":
                // pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,30}$/;
                pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,30}$/;
                break;
            case "otp":
                pattern = /^\d+$/;
                break;
            case "title": 
                pattern = /^[a-zA-Z0-9\s\-_]{5,60}$/;
                break;            
            case "description":
                pattern = /^[a-zA-Z0-9\s,\.\-]{20,120}$/;
                break;
            case "country":
                pattern = /^[\p{L}\s'()-]+$/u;
                break;

            default:
                break;
        }
        return pattern.test(data);
    }
}
class Toast{
    static ToastBase() {
        return Swal.mixin({
            toast: true,
            position: "bottom-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
              toast.onmouseenter = Swal.stopTimer;
              toast.onmouseleave = Swal.resumeTimer;
            }
        })
    }

    static Error(error){
        this.ToastBase().fire({
            icon: "error",
            title: error
        })
    }
    static Success(msg){
        this.ToastBase().fire({
            icon: "success",
            title: msg
        })
    }
    static Info(msg){
        this.ToastBase().fire({
            icon: "info",
            title: msg
        })
    }
}