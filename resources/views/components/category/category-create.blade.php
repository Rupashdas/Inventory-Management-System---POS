<div class="modal animated zoomIn" id="create-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add a category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="save-form">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" id="categoryName" placeholder="Beverages">
                    <div class="form-hint">Products are filed under this on the catalogue and the stock report.</div>
                </form>
            </div>
            <div class="modal-footer">
                <button id="modal-close" class="btn btn-quiet" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                <button onclick="Save()" id="save-btn" class="btn btn-accent">Save category</button>
            </div>
        </div>
    </div>
</div>

<script>
    async function Save() {
        const name = document.getElementById('categoryName').value.trim();

        if (!name) {
            return errorToast("Give the category a name.");
        }

        const result = await apiPost("/create-category", { name: name });

        if (result) {
            document.getElementById('modal-close').click();
            successToast(result.message || 'Category added.');
            document.getElementById("save-form").reset();
            await getList();
        }
    }
</script>
