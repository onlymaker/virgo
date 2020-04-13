/**
 * Set cookie to root path, default expire time is 30 days
 * @param name
 * @param value
 * @param days
 */
function setCookie(name, value, days) {
    days = (value === null) ? 0 : (days || 30);
    let date = new Date();
    let time = !!days ? (date.getTime() + days * 24 * 60 * 60 * 1000) : (date.getTime() - 1);
    date.setTime(time);
    document.cookie = name + "=" + encodeURIComponent(value) + ";expires=" + date.toUTCString() + ";path=/";
}

function getCookie(name) {
    let data = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
    return !!data ? decodeURIComponent(data[2]) : '';
}

function deleteCookie(name) {
    setCookie(name, null);
}

function post(url, params, target) {
    let _form = document.createElement("form");
    _form.action = url;
    _form.method = "post";
    if (target) {
        _form.target = target;
    }
    _form.style.display = "none";
    for (let x in params) {
        let param = document.createElement("textarea");
        param.name = x;
        param.value = params[x];
        _form.appendChild(param);
    }
    document.body.appendChild(_form);
    _form.submit();
}

function buildQuery(data, nullable) {
    return (!!nullable) ?
        Object.keys(data)
            .map(k => encodeURIComponent(k) + '=' + encodeURIComponent(data[k]))
            .join('&') :
        Object.keys(data)
            .filter(key => {
                if (data[key]) {
                    return key;
                }
            })
            .map(k => encodeURIComponent(k) + '=' + encodeURIComponent(data[k]))
            .join('&');
}

/**
 * @param {Object} file: native object of <input type="file"/>
 * @return {Object} File: {name, type, size, lastModified, lastModifiedDate, ...}
 */
function getFile(file) {
    return file ? (file.files ? file.files[0] : {}) : {}
}

function isJsonObject(data) {
    return Object.prototype.toString.call(data) === "[object Object]";
}

function isJsonString(data) {
    try {
        return JSON.parse(data);
    } catch (e) {
        return false;
    }
}

function generalCallback() {
    for (let i = 0; i < arguments.length; i++) {
        let arg = arguments[i];
        console.log(arg);
        if (isJsonObject(arg)) {
            alert(JSON.stringify(arg));
        } else {
            alert(arg);
        }
    }
}
