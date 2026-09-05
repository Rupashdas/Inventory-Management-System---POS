{{--
    Restocking is a separate action from editing a product on purpose: it is an
    event with a quantity and a reason, not a field you overwrite. Typing a new
    balance into the edit form would leave the ledger unable to explain it.
--}}
<div class="modal animated zoomIn" id="restock-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="customer-slot filled mb-3">
                    <strong id="restockName">&mdash;</strong><br/>
                    currently <strong id="restockCurrent">0</strong> on the shelf
                </div>

                <label class="form-label">How many arrived</label>
                <input type="number" min="1" class="form-control" id="restockQty" placeholder="24">

                <label class="form-label mt-3">Note</label>
                <input type="text" class="form-control" id="restockNote" placeholder="Delivery from supplier">
                <div class="form-hint">Optional. It is kept on the stock history for this product.</div>

                <input type="hidden" id="restockID"/>
            </div>
            <div class="modal-footer">
                <button type="button" id="restock-modal-close" class="btn btn-quiet" data-bs-dismiss="modal">Cancel</button>
                <button onclick="submitRestock()" type="button" class="btn btn-accent">Add to stock</button>
            </div>
        </div>
    </div>
</div>

<script>
    async function submitRestock() {
        const id  = document.getElementById('restockID').value;
        const qty = document.getElementById('restockQty').value;

        if (!qty || Number(qty) < 1) {
            return errorToast("Enter how many arrived.");
        }

        const result = await apiPost("/restock-product", {
            id: id,
            quantity: qty,
            note: document.getElementById('restockNote').value
        });

        if (result) {
            document.getElementById('restock-modal-close').click();
            // The server's message carries the new balance, which is the thing
            // being confirmed -- worth showing rather than replacing with "OK".
            successToast(result.message);
            await getList();
        }
    }
</script>
