<div class="auth-card animated fadeIn">
    <h1>Enter the code</h1>
    <p class="sub">Four digits, sent to <strong id="sentTo">your inbox</strong>.</p>

    <form onsubmit="VerifyOtp(event)">
        <label class="form-label">Code</label>
        <input id="otp" placeholder="0000" class="form-control" type="text" inputmode="numeric" maxlength="4"
               autocomplete="one-time-code" style="letter-spacing:8px;font-size:20px;text-align:center"/>

        <button type="submit" class="btn btn-accent w-100 mt-4 py-2">Continue</button>
    </form>

    <div class="auth-foot">
        <a href="{{url('/sendOtp')}}">Send it again</a>
    </div>
</div>

<script>
    // The address is already in session storage from the previous step, so the
    // page can say where the code went instead of "your inbox".
    (function () {
        const email = sessionStorage.getItem('email');
        if (email) {
            document.getElementById('sentTo').innerText = email;
        }
    })();

   async function VerifyOtp(event) {
        event.preventDefault();

        let otp = document.getElementById('otp').value.trim();
        if (otp.length !== 4) {
           errorToast('The code is four digits.');
           return;
        }

        showLoader();
        try {
            let res = await axios.post('/verify-otp', {
                otp: otp,
                email: sessionStorage.getItem('email')
            });

            if (res.status === 200 && res.data['status'] === 'success') {
                successToast(res.data['message']);
                sessionStorage.clear();
                setTimeout(() => {
                    window.location.href = '/resetPassword';
                }, 1000);
            } else {
                errorToast(res.data['message']);
            }
        } catch (error) {
            // The original caught and discarded this, so a wrong code left the
            // loader spinning and said nothing at all.
            const message = error.response && error.response.data && error.response.data.message;
            errorToast(message || 'That code was not accepted.');
        } finally {
            hideLoader();
        }
    }
</script>
