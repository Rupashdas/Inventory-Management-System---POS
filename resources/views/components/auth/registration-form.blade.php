<div class="auth-card animated fadeIn" style="max-width:560px">
    <h1>Create an account</h1>
    <p class="sub">One account holds one shop's catalogue, customers and sales.</p>

    <form onsubmit="onRegistration(event)">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">First name</label>
                <input id="firstName" placeholder="John" class="form-control" type="text"/>
            </div>
            <div class="col-md-6">
                <label class="form-label">Last name</label>
                <input id="lastName" placeholder="Doe" class="form-control" type="text"/>
            </div>
            <div class="col-12">
                <label class="form-label">Email</label>
                <input id="email" placeholder="you@example.com" class="form-control" type="email" autocomplete="username"/>
            </div>
            <div class="col-md-6">
                <label class="form-label">Mobile</label>
                <input id="mobile" placeholder="+1 415-555-0142" class="form-control" type="tel"/>
            </div>
            <div class="col-md-6">
                <label class="form-label">Password</label>
                <input id="password" placeholder="At least 6 characters" class="form-control" type="password" autocomplete="new-password"/>
            </div>
        </div>

        <button type="submit" class="btn btn-accent w-100 mt-4 py-2">Create account</button>
    </form>

    <div class="auth-foot">
        Already have one? <a href="{{url('/userLogin')}}">Sign in</a>
    </div>
</div>

<script>

async function onRegistration(event) {
        event.preventDefault();

        const email     = document.getElementById('email').value.trim();
        const firstName = document.getElementById('firstName').value.trim();
        const lastName  = document.getElementById('lastName').value.trim();
        const mobile    = document.getElementById('mobile').value.trim();
        const password  = document.getElementById('password').value;

        // Each of these returns. The original showed the warning and then fell
        // straight through to the request anyway, so an empty form was still
        // posted and the real complaint came back from the server.
        if (!firstName) { return errorToast('Enter your first name.'); }
        if (!lastName)  { return errorToast('Enter your last name.'); }
        if (!email)     { return errorToast('Enter an email address.'); }
        if (!mobile)    { return errorToast('Enter a mobile number.'); }
        if (!password)  { return errorToast('Choose a password.'); }
        if (password.length < 6) { return errorToast('Passwords must be at least six characters.'); }

        showLoader();
        try {
            let res = await axios.post("/user-registration", {
                email: email,
                firstName: firstName,
                lastName: lastName,
                mobile: mobile,
                password: password
            });
            hideLoader();
            if (res.status === 201 && res.data['status'] === 'success') {
                successToast(res.data['message']);
                setTimeout(function () {
                    window.location.href = '/userLogin';
                }, 1500);
            } else {
                errorToast(res.data['message']);
            }
        } catch (error) {
            hideLoader();
            const data = error.response && error.response.data;
            if (data && data.errors) {
                // One toast, for the first problem. Looping the whole error bag
                // stacked five overlapping toasts on an empty form.
                const first = Object.values(data.errors)[0];
                errorToast(Array.isArray(first) ? first[0] : first);
            } else if (data && data.message) {
                errorToast(data.message);
            } else {
                errorToast("Something went wrong.");
            }
        }
    }
</script>
