<div class="modal animated zoomIn" id="delete-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center pt-4">
                <div class="tag tag-out mb-3"><i class="bi bi-trash3"></i> Permanent</div>
                <h5 class="mb-2">Delete this invoice?</h5>
                <p class="mb-0" style="color:var(--ink-soft)">
                    The sale is removed and everything on it goes back on the shelf,
                    recorded on each product's stock history as a reversal.
                </p>
                <input type="hidden" id="deleteID"/>
            </div>
            <div class="modal-footer justify-content-center border-0 pb-4">
                <button type="button" id="delete-modal-close" class="btn btn-quiet" data-bs-dismiss="modal">Keep it</button>
                <button onclick="itemDelete()" type="button" id="confirmDelete" class="btn btn-danger">Delete invoice</button>
            </div>
        </div>
    </div>
</div>

<script>
    async function itemDelete() {
        const id = document.getElementById('deleteID').value;

        const result = await apiPost("/invoice-delete", { inv_id: id });

        if (result) {
            document.getElementById('delete-modal-close').click();
            successToast(result.message || 'Invoice deleted.');
            await getList();
        }
    }
</script>
