<div class="modal animated zoomIn" id="create-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add a customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="save-form">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" id="customerName" placeholder="Sarah Mitchell">

                    <label class="form-label mt-3">Email</label>
                    <input type="email" class="form-control" id="customerEmail" placeholder="sarah@example.com">
                    <div class="form-hint">Must be unique &mdash; it is how a returning customer is matched.</div>

                    <label class="form-label mt-3">Mobile</label>
                    <input type="text" class="form-control" id="customerMobile" placeholder="+1 415-555-0142">
                </form>
            </div>
            <div class="modal-footer">
                <button id="modal-close" class="btn btn-quiet" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                <button onclick="Save()" id="save-btn" class="btn btn-accent">Save customer</button>
            </div>
        </div>
    </div>
</div>


<script>
    async function Save() {
        const name   = document.getElementById('customerName').value.trim();
        const email  = document.getElementById('customerEmail').value.trim();
        const mobile = document.getElementById('customerMobile').value.trim();

        if (!name)   { return errorToast("Give the customer a name."); }
        if (!email)  { return errorToast("An email address is required."); }
        if (!mobile) { return errorToast("A mobile number is required."); }

        const result = await apiPost("/create-customer", { name: name, email: email, mobile: mobile });

        // The modal is only dismissed once the server has accepted it, so a
        // rejected email leaves the form filled in and correctable.
        if (result) {
            document.getElementById('modal-close').click();
            successToast(result.message || 'Customer added.');
            document.getElementById("save-form").reset();
            await getList();
        }
    }
</script>
