<div class="modal animated zoomIn" id="delete-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center pt-4">
                <div class="tag tag-out mb-3"><i class="bi bi-trash3"></i> Permanent</div>
                <h5 class="mb-2">Delete <span id="deleteName">this product</span>?</h5>
                <p class="mb-0" style="color:var(--ink-soft)">
                    Its image is removed too. A product that appears on an invoice
                    cannot be deleted &mdash; its sales history depends on it.
                </p>
                <input type="hidden" id="deleteID"/>
            </div>
            <div class="modal-footer justify-content-center border-0 pb-4">
                <button type="button" id="delete-modal-close" class="btn btn-quiet" data-bs-dismiss="modal">Keep it</button>
                <button onclick="itemDelete()" type="button" id="confirmDelete" class="btn btn-danger">Delete product</button>
            </div>
        </div>
    </div>
</div>

<script>
    async function itemDelete() {
        const id = document.getElementById('deleteID').value;

        // The path used to be posted alongside the id and handed straight to
        // File::delete on the server. The server now works it out from the
        // product row, so the browser has no say in which file goes.
        const result = await apiPost("/delete-product", { id: id });

        if (result) {
            document.getElementById('delete-modal-close').click();
            successToast(result.message || 'Product deleted.');
            await getList();
        }
    }
</script>
