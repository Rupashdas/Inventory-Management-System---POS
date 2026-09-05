<div class="modal animated zoomIn" id="update-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="update-form">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" id="customerNameUpdate">

                    <label class="form-label mt-3">Email</label>
                    <input type="email" class="form-control" id="customerEmailUpdate">

                    <label class="form-label mt-3">Mobile</label>
                    <input type="text" class="form-control" id="customerMobileUpdate">

                    <input type="hidden" id="updateID">
                </form>
            </div>
            <div class="modal-footer">
                <button id="update-modal-close" class="btn btn-quiet" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                <button onclick="Update()" id="update-btn" class="btn btn-accent">Save changes</button>
            </div>
        </div>
    </div>
</div>


<script>
    async function FillUpUpdateForm(id) {
        document.getElementById('updateID').value = id;

        showLoader();
        let res = await axios.post("/customer-by-id", { id: id });
        hideLoader();

        if (!res.data) {
            return errorToast('That customer could not be loaded.');
        }

        document.getElementById('customerNameUpdate').value   = res.data['name'];
        document.getElementById('customerEmailUpdate').value  = res.data['email'];
        document.getElementById('customerMobileUpdate').value = res.data['mobile'];
    }

    async function Update() {
        const name   = document.getElementById('customerNameUpdate').value.trim();
        const email  = document.getElementById('customerEmailUpdate').value.trim();
        const mobile = document.getElementById('customerMobileUpdate').value.trim();
        const id     = document.getElementById('updateID').value;

        if (!name)   { return errorToast("Give the customer a name."); }
        if (!email)  { return errorToast("An email address is required."); }
        if (!mobile) { return errorToast("A mobile number is required."); }

        const result = await apiPost("/update-customer", { name: name, email: email, mobile: mobile, id: id });

        if (result) {
            document.getElementById('update-modal-close').click();
            successToast(result.message || 'Customer updated.');
            document.getElementById("update-form").reset();
            await getList();
        }
    }
</script>
