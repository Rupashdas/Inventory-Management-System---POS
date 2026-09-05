<div class="page">

    <div class="page-head">
        <div>
            <h1>Your profile</h1>
            <p class="sub">The name shown in the header, and the details on your account.</p>
        </div>
    </div>

    <div class="panel" style="max-width:720px">
        <div class="panel-head">
            <h2>Account details</h2>
        </div>
        <div class="panel-body">
            <form onsubmit="onUpdate(event)">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">First name</label>
                        <input id="firstName" class="form-control" type="text"/>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last name</label>
                        <input id="lastName" class="form-control" type="text"/>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input id="email" class="form-control" type="email" readonly/>
                        <div class="form-hint">Your sign-in address. It cannot be changed here.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mobile</label>
                        <input id="mobile" class="form-control" type="tel"/>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">New password</label>
                        <input id="password" placeholder="••••••••" class="form-control" type="password" autocomplete="new-password"/>
                        <div class="form-hint">Leave blank to keep the one you have.</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-accent mt-4">Save changes</button>
            </form>
        </div>
    </div>

</div>

<script>
    getProfile();

    async function getProfile() {
        showLoader();
        try {
            let res = await axios.get('/user-profile');
            if (res.status === 200 && res.data.status === 'success') {
                const data = res.data.data;
                document.getElementById('email').value     = data.email;
                document.getElementById('firstName').value = data.firstName;
                document.getElementById('lastName').value  = data.lastName;
                document.getElementById('mobile').value    = data.mobile;
                // Deliberately not filled from the response. The endpoint sends
                // an empty string for it, and this field means "change it to
                // this", not "here is the current one".
                document.getElementById('password').value  = '';
            }
        } catch (err) {
            const message = err.response && err.response.data && err.response.data.message;
            errorToast(message || 'Your profile could not be loaded.');
        } finally {
            hideLoader();
        }
    }

    async function onUpdate(event) {
        event.preventDefault();

        const firstName = document.getElementById('firstName').value.trim();
        const lastName  = document.getElementById('lastName').value.trim();
        const mobile    = document.getElementById('mobile').value.trim();
        const password  = document.getElementById('password').value;

        if (!firstName) { return errorToast('Enter your first name.'); }
        if (!lastName)  { return errorToast('Enter your last name.'); }
        if (!mobile)    { return errorToast('Enter a mobile number.'); }
        if (password && password.length < 6) {
            return errorToast('Passwords must be at least six characters.');
        }

        const result = await apiPost("/user-update", {
            firstName: firstName,
            lastName: lastName,
            mobile: mobile,
            password: password
        });

        if (result) {
            successToast(result.message || 'Profile updated.');
            await getProfile();
            // The header carries the name, so it has to be redrawn -- editing
            // it used to leave the old name in the corner until a hard reload.
            setTimeout(() => window.location.reload(), 900);
        }
    }
</script>
