<div class="auth-card animated fadeIn">
    <h1>Set a new password</h1>
    <p class="sub">Six characters or more.</p>

    <form onsubmit="ResetPass(event)">
        <label class="form-label">New password</label>
        <input id="password" placeholder="••••••••" class="form-control" type="password" autocomplete="new-password"/>

        <label class="form-label mt-3">Confirm it</label>
        <input id="cpassword" placeholder="••••••••" class="form-control" type="password" autocomplete="new-password"/>

        <button type="submit" class="btn btn-accent w-100 mt-4 py-2">Save password</button>
    </form>
</div>

<script>
  // Takes `event` as an argument. The original declared no parameter and called
  // event.preventDefault() anyway, leaning on the global window.event -- which
  // Chrome provides and Firefox does not, so the form did a full page submit
  // there instead of posting.
  async function ResetPass(event) {
        event.preventDefault();

        let password = document.getElementById('password').value;
        let cpassword = document.getElementById('cpassword').value;

        if (password.length === 0) {
            errorToast('Choose a password.');
            return;
        }
        if (password.length < 6) {
            errorToast('Passwords must be at least six characters.');
            return;
        }
        if (cpassword.length === 0) {
            errorToast('Type the password a second time.');
            return;
        }
        if (password !== cpassword) {
            errorToast('The two passwords do not match.');
            return;
        }

        showLoader();
        try {
            let res = await axios.post("/reset-password", { newPassword: password });
            if (res.status === 200 && res.data['status'] === 'success') {
                successToast(res.data['message']);
                setTimeout(function () {
                    window.location.href = "/userLogin";
                }, 1000);
            } else {
                errorToast(res.data['message']);
            }
        } catch (error) {
            const message = error.response && error.response.data && error.response.data.message;
            errorToast(message || 'The password could not be changed.');
        } finally {
            hideLoader();
        }
    }
</script>
