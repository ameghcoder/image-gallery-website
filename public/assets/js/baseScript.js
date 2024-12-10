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

/**
 * Fetch data from an API. For only get request and post where you don't want to send extra headers otherwise you can use fetchDataVersion2() functiono
 * 
 * @param {string} url - The URL of the API endpoint.
 * @returns {Promise<object>} - A promise that resolves to the JSON data from the API response.
 */
function fetchDataFromAPI(url) {
    return new Promise((resolve, reject) => {
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => resolve(data))
            .catch(error => reject(error));
    });
}

/**
 * Sends an XMLHttpRequest to the specified URL with the given method and data.
 * @param {string} url - The URL to send the request to.
 * @param {string} method - The HTTP method to use (GET, POST, etc.).
 * @param {Object|null} data - The data to send with the request (for non-GET requests).
 * @param {string|null} btnId - The ID of the button to update during the request.
 * @param {boolean} validation - Whether to include a CSRF token in the request headers.
 * @returns {Promise} - A promise that resolves with the response data or rejects with an error.
 */

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

/**
 * Generates a unique id everytime the function loads
 * @param {number} length lenght of the unique id
 * @returns {string} return the unique id string
 */
function generateUniqueId(length = 16) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
}

/**
 * if you want to show a spinner above the screen for waiting a response you can use this simply using loader() function and add enable for enabling the loader and disable for disabling the loader
 * @param {string} status enable or disable
 */
function loader(status) {
    let loader = $id("screen-loader");
    if (status === "enable" || status === "on" || status) {
        loader.classList.add("flex");
        loader.classList.remove("hidden");
    } else {
        loader.classList.add("hidden");
        loader.classList.remove("flex");
    }
}
/**
 * This function helps you to remove the value from array by value name
 * @param {array} array Array from where you want to remove the value by value not index
 * @param {string} value Value that you want to remove from Array
 */
function removeElementFromArrayByValue(array, value) {
    const index = array.indexOf(value);
    if (index !== -1) {
        array.splice(index, 1);
    }
}

/**
 * This function helps you to check an element is HTMLElement or not
 * @param {HTMLElement} element provide a element to check it is a HTML Element or not
 * @returns true if provided element is HTMLElement, false otherwise
 */

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

/**
 * Checks if a specific key exists in an array of objects, or in a provided object.
 *
 * @param {string} keyToCheck - The key to check for existence.
 * @param {Array<Object>|Object} source - An array of objects or a single object to check for the key. If not provided, defaults to the `searchParams` array.
 * @returns {boolean} - Returns `true` if the key exists, otherwise `false`.
 *
 * @example
 * const searchParams = [
 *   { key: 'query', value: 'eyeglasses' },
 *   { key: 'filters', value: 'on' }
 * ];
 * 
 * // Check in the default array
 * console.log(keyExists('query')); // true
 * console.log(keyExists('nonexistentKey')); // false
 *
 * // Check in a provided array of objects
 * const otherParams = [
 *   { key: 'category', value: 'glasses' },
 *   { key: 'size', value: 'large' }
 * ];
 * console.log(keyExists('size', otherParams)); // true
 *
 * // Check in a provided single object
 * const singleObject = { key: 'color', value: 'blue' };
 * console.log(keyExists('color', singleObject)); // true
 */
function keyExists(keyToCheck, source) {
    if (Array.isArray(source)) {
        return source.some(param => param.key === keyToCheck);
    } else if (typeof source === 'object' && source !== null) {
        return source.key === keyToCheck;
    }
    return false;
}

const storage = {
    /**
     * Create or Update: Adds a new item to localStorage or updates an existing one.
     * @param {string} key - The identifier for the item in localStorage.
     * @param {Object} value - The data you want to store, which can be an object, array, etc.
     */
    setItem: (key, value) => {
        // Check if the item already exists in localStorage
        const existingValue = localStorage.getItem(key);

        if (existingValue) {
            // If the item exists, merge the existing data with the new data
            const newValue = { ...JSON.parse(existingValue), ...value };
            // Store the merged data back into localStorage
            localStorage.setItem(key, JSON.stringify(newValue));
        } else {
            // If the item doesn't exist, create a new entry in localStorage
            localStorage.setItem(key, JSON.stringify(value));
        }
    },

    /**
     * Read: Retrieves an item from localStorage by its key.
     * @param {string} key - The identifier for the item you want to retrieve.
     * @returns {Object|null} - The parsed JavaScript object if found, otherwise null.
     */
    getItem: (key) => {
        const value = localStorage.getItem(key);
        return value ? JSON.parse(value) : null;
    },

    /**
     * Delete: Removes an item from localStorage by its key.
     * @param {string} key - The identifier for the item you want to remove.
     */
    removeItem: (key) => {
        localStorage.removeItem(key);
    },

    /**
     * Clear: Removes all items from localStorage.
     * Use this with caution as it will delete everything stored in localStorage.
     */
    clear: () => {
        localStorage.clear();
    }
};

const checkInputValue = (inputField, validatedValue) => {
    if(!validatedValue){
        inputField.setAttribute("style", "border: 1px solid red")
    } else{
        inputField.removeAttribute("style");
    }

}

class Validator {
    /*
    * Validate email format using a regular expression.
    * It checks for a valid structure like 'example@domain.com'.
    */
    static validateEmail(email) {   
        const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        return emailPattern.test(email);
    }

    /*
    * Validate password with multiple criteria:
    * - At least one uppercase letter
    * - At least one lowercase letter
    * - At least one digit
    * - At least one special character (e.g., @$!%*?&)
    * - Minimum length of 8 characters, maximum length of 20.
    */
    static validatePassword(password) {
        const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,20}$/;
        return passwordPattern.test(password);
    }

    /*
    * Validate mobile number:
    * - Must be exactly 10 digits
    * - Must start with 7, 8, or 9 (common for Indian numbers)
    */
    static validateMobile(mobile) {
        const mobilePattern = /^[789]\d{9}$/;
        return mobilePattern.test(mobile);
    }

    /*
    * Validate a string to ensure it doesn't contain any numbers
    * or special characters, and has a minimum length.
    * Default minimum length is 3 characters.
    */
    static validateString(str, minLength = 3) {
        const stringPattern = /^[a-zA-Z\s]+$/;
        return stringPattern.test(str) && str.length >= minLength;
    }

    static validateOtp(str){
        const otpPattern = /^\d{6}$/;
        return otpPattern.test(str);
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

// basic function initialization
document.addEventListener('DOMContentLoaded', () => {
    // Select the element
    const divEmpty = document.querySelectorAll('.empty-element');

    // Check if the element's text content is empty or contains only whitespace
    divEmpty.forEach(div => {
        if (div.textContent.trim() === '') {
            // Remove any whitespace nodes
            div.innerHTML = '';
        }
    })
})
