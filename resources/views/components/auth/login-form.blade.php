<div class="auth-card animated fadeIn">
    <h1>Sign in</h1>
    <p class="sub">The till, the stock and the day's takings.</p>

    <form onsubmit="SubmitLogin(event)">
        <label class="form-label">Email</label>
        <input id="email" placeholder="you@example.com" class="form-control" type="email" autocomplete="username"/>

        <label class="form-label mt-3">Password</label>
        <input id="password" placeholder="••••••••" class="form-control" type="password" autocomplete="current-password"/>

        <button type="submit" class="btn btn-accent w-100 mt-4 py-2">Sign in</button>
    </form>

    <div class="auth-foot">
        <a href="{{url('/sendOtp')}}">Forgotten your password?</a>
        <div class="mt-2">No account yet? <a href="{{url('/userRegistration')}}">Create one</a></div>
    </div>
</div>


<script>

    async function SubmitLogin(event) {
        event.preventDefault();

        let email = document.getElementById('email').value.trim();
        let password = document.getElementById('password').value;

        if (email.length === 0) {
            errorToast("Enter your email address.");
            return;
        }

        if (password.length === 0) {
            errorToast("Enter your password.");
            return;
        }

        showLoader();

        try {
            let res = await axios.post("/user-login", { email: email, password: password });
            if (res.status === 200 && res.data['status'] === 'success') {
                window.location.href = "/dashboard";
            } else {
                errorToast(res.data['message']);
            }
        } catch (error) {
            if (error.response) {
                errorToast(error.response.data.message);
            } else {
                errorToast("Something went wrong.");
            }
        } finally {
            hideLoader();
        }
    }

</script>
