function showLoader() {
    document.getElementById('loader').classList.remove('d-none')
}
function hideLoader() {
    document.getElementById('loader').classList.add('d-none')
}

function successToast(msg) {
    Toastify({
        gravity: "top", // `top` or `bottom`
        position: "center", // `left`, `center` or `right`
        text: msg,
        className: "mt-3",
        style: {
            background: "#d4edda",
            color: "#155724",
        }
    }).showToast();
}

function errorToast(msg) {
    Toastify({
        gravity: "top", // `top` or `bottom`
        position: "center", // `left`, `center` or `right`
        text: msg,
        className: "mt-3",
        style: {
            background: "#f8d7da",
            color: "#721c24",
        }
    }).showToast();
}
