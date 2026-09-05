<div class="modal animated zoomIn" id="update-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="update-form">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" id="categoryNameUpdate">
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
        let res = await axios.post("/category-by-id", { id: id });
        hideLoader();

        if (!res.data) {
            return errorToast('That category could not be loaded.');
        }

        document.getElementById('categoryNameUpdate').value = res.data['name'];
    }

    async function Update() {
        const name = document.getElementById('categoryNameUpdate').value.trim();
        const id   = document.getElementById('updateID').value;

        if (!name) {
            return errorToast("Give the category a name.");
        }

        const result = await apiPost("/update-category", { name: name, id: id });

        if (result) {
            document.getElementById('update-modal-close').click();
            successToast(result.message || 'Category updated.');
            document.getElementById("update-form").reset();
            await getList();
        }
    }
</script>
