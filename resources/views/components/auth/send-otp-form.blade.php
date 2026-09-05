<div class="auth-card animated fadeIn">
    <h1>Reset your password</h1>
    <p class="sub">We will email a four-digit code to the address on your account.</p>

    <form onsubmit="VerifyEmail(event)">
        <label class="form-label">Email</label>
        <input id="email" placeholder="you@example.com" class="form-control" type="email" autocomplete="username"/>

        <button type="submit" class="btn btn-accent w-100 mt-4 py-2">Send the code</button>
    </form>

    <div class="auth-foot">
        <a href="{{url('/userLogin')}}">Back to sign in</a>
    </div>
</div>

<script>
   async function VerifyEmail(event) {
        event.preventDefault();

        let email = document.getElementById('email').value.trim();
        if (email.length === 0) {
            errorToast('Enter your email address.');
            return;
        }

        showLoader();
        try {
            let res = await axios.post('/send-otp', { email: email });
            if (res.status === 200 && res.data['status'] === 'success') {
                successToast(res.data['message']);
                sessionStorage.setItem('email', email);
                setTimeout(function () {
                    window.location.href = '/verifyOtp';
                }, 1000);
            } else {
                errorToast(res.data['message']);
            }
        } catch (error) {
            const message = error.response && error.response.data && error.response.data.message;
            errorToast(message || 'Something went wrong.');
        } finally {
            // In a finally block rather than on each branch: the original hid
            // the loader on success and on the caught error but not when the
            // redirect was pending, and the spinner outlived the page.
            hideLoader();
        }
    }
</script>
