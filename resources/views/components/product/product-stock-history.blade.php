{{--
    The ledger behind the balance. The stock column answers "how many"; this
    answers "why that many", which is the question asked when the shelf and the
    screen disagree.
--}}
<div class="modal animated zoomIn" id="history-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">Stock history</h5>
                    <div class="cell-sub" id="historyProduct">&mdash;</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="max-height:60vh;overflow:auto">
                <table class="tidy">
                    <thead>
                    <tr>
                        <th>When</th>
                        <th>Reason</th>
                        <th class="num">Change</th>
                        <th class="num">Balance</th>
                    </tr>
                    </thead>
                    <tbody id="historyRows"></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-quiet" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    const MOVEMENT_LABELS = {
        purchase:      { text: 'Stock added',   tag: 'tag-ok' },
        sale:          { text: 'Sold',          tag: 'tag-mute' },
        sale_reversal: { text: 'Sale reversed', tag: 'tag-low' },
        adjustment:    { text: 'Adjustment',    tag: 'tag-mute' },
    };

    async function ShowStockHistory(id) {
        showLoader();
        let res = await axios.post("/product-stock-history", { id: id });
        hideLoader();

        if (!res.data || !res.data.product) {
            return errorToast('That history could not be loaded.');
        }

        document.getElementById('historyProduct').innerText = res.data.product.name;

        const rows = res.data.movements;
        const body = document.getElementById('historyRows');

        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="4"><div class="empty"><i class="bi bi-clock-history"></i><p>Nothing has moved yet.</p></div></td></tr>';
        } else {
            body.innerHTML = rows.map(row => {
                const meta = MOVEMENT_LABELS[row.reason] || { text: row.reason, tag: 'tag-mute' };
                const sign = row.quantity > 0 ? '+' : '';
                return `<tr>
                    <td>
                        <div class="cell-title">${new Date(row.created_at).toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' })}</div>
                        <div class="cell-sub">${new Date(row.created_at).toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' })}</div>
                    </td>
                    <td>
                        <span class="tag ${meta.tag}">${meta.text}</span>
                        ${row.note ? `<div class="cell-sub mt-1">${escapeHtml(row.note)}</div>` : ''}
                    </td>
                    <td class="num" style="color:${row.quantity > 0 ? 'var(--ok)' : 'var(--ink-soft)'}">${sign}${row.quantity}</td>
                    <td class="num">${row.balance_after}</td>
                </tr>`;
            }).join('');
        }

        $("#history-modal").modal('show');
    }
</script>
