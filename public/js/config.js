/* --------------------------------------------------------------------------
   Shared front-end helpers.
   --------------------------------------------------------------------------
   Loaded in <head>, before any page's own script. That ordering matters: these
   used to live in a <script> at the foot of the layout, which is parsed after
   @yield('content'), so any page that called one at parse time rather than
   inside an awaited callback got "money is not defined".
   -------------------------------------------------------------------------- */

const CURRENCY = '$';

function showLoader() {
    document.getElementById('loader').classList.remove('d-none')
}

function hideLoader() {
    document.getElementById('loader').classList.add('d-none')
}

function successToast(msg) {
    Toastify({
        gravity: "top",
        position: "center",
        text: msg,
        className: "mt-3",
        style: {
            background: "#e4f6ec",
            color: "#16794a",
            boxShadow: "0 6px 20px rgba(25, 28, 46, 0.12)",
            borderRadius: "8px",
        }
    }).showToast();
}

function errorToast(msg) {
    Toastify({
        gravity: "top",
        position: "center",
        text: msg,
        className: "mt-3",
        // Long enough to read a sentence that names a product and a number,
        // rather than the default that suits "Error".
        duration: 5000,
        style: {
            background: "#fde8ea",
            color: "#a41d32",
            boxShadow: "0 6px 20px rgba(25, 28, 46, 0.12)",
            borderRadius: "8px",
        }
    }).showToast();
}

/**
 * Every screen talks to the same JSON API, so the "did it work, and what do I
 * tell the user" decision is made once here rather than re-implemented,
 * slightly differently, on each page.
 *
 * Returns the response body on success and null on failure, having already
 * shown the reason -- so callers read as `if (result) { ... }`.
 */
async function apiPost(url, payload, options = {}) {
    showLoader();
    try {
        const res = await axios.post(url, payload, options);
        const data = res.data;
        if (data && data.status === 'failed') {
            errorToast(data.message || 'That did not work.');
            return null;
        }
        return data;
    } catch (error) {
        const message = error.response && error.response.data && error.response.data.message;
        // The server's own message names the product that ran out or the field
        // that was wrong; the fallback is only for when there isn't one.
        errorToast(message || 'Something went wrong.');
        return null;
    } finally {
        hideLoader();
    }
}

function money(value) {
    const n = Number(value || 0);
    return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/**
 * Out of stock is its own state rather than "below the threshold", because a
 * product with none left cannot be sold at all and one that is merely low can.
 */
function stockTag(stock, threshold) {
    if (stock <= 0) {
        return '<span class="tag tag-out"><span class="dot"></span>Out of stock</span>';
    }
    if (stock <= threshold) {
        return `<span class="tag tag-low"><span class="dot"></span>${stock} left</span>`;
    }
    return `<span class="tag tag-ok"><span class="dot"></span>${stock} in stock</span>`;
}

/**
 * Product and customer names are user input and every list here builds its rows
 * as HTML strings, so they are escaped on the way in.
 */
function escapeHtml(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, c => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));
}
