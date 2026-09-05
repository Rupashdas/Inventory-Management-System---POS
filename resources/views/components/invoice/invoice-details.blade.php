<div class="modal animated zoomIn" id="details-modal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Invoice <span id="invNo"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div id="invoice" class="modal-body">
                <div class="receipt">

                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="label-sm">Billed to</div>
                            <div class="cell-title" id="CName"></div>
                            <div class="cell-sub" id="CEmail"></div>
                            <div class="cell-sub" id="CMobile"></div>
                        </div>
                        <div class="text-end">
                            <div class="brand-mark mb-1">
                                <span class="brand-dot"><i class="bi bi-shop"></i></span>
                                {{ config('app.name') }}
                            </div>
                            {{-- The invoice's own date. This used to print
                                 date('Y-m-d') -- today -- on every receipt,
                                 however old the sale was. --}}
                            <div class="cell-sub" id="invDate"></div>
                        </div>
                    </div>

                    <table class="tidy">
                        <thead>
                        <tr>
                            <th>Item</th>
                            <th class="num">Qty</th>
                            <th class="num">Line total</th>
                        </tr>
                        </thead>
                        <tbody id="invoiceList"></tbody>
                    </table>

                    <div class="mt-3" style="max-width:320px;margin-left:auto">
                        <div class="till-total"><span>Total</span> <strong>$ <span id="total"></span></strong></div>
                        <div class="till-total"><span>Discount</span> <strong>&minus; $ <span id="discount"></span></strong></div>
                        <div class="till-total"><span>Sales tax</span> <strong>$ <span id="vat"></span></strong></div>
                        <div class="till-total grand"><span>Paid</span> <strong>$ <span id="payable"></span></strong></div>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-quiet" data-bs-dismiss="modal">Close</button>
                <button onclick="PrintPage()" class="btn btn-accent"><i class="bi bi-printer me-1"></i> Print</button>
            </div>
        </div>
    </div>
</div>

<style>
    .label-sm {
        font-size: 10.5px;
        font-weight: 600;
        letter-spacing: 0.7px;
        text-transform: uppercase;
        color: var(--ink-faint);
    }

    /* Printing swaps a cloned copy of the receipt in beside the app and hides
       everything else. The previous version overwrote document.body with the
       receipt's markup, which detached every event handler on the page and
       needed a full reload afterwards to put the application back. */
    #print-root { display: none; }

    @media print {
        html.printing body > *:not(#print-root) { display: none !important; }
        html.printing #print-root { display: block !important; padding: 20px; }
        html.printing .modal-backdrop { display: none !important; }
    }
</style>

<script>
    async function InvoiceDetails(cus_id, inv_id) {
        showLoader();
        let res = await axios.post("/invoice-details", { cus_id: cus_id, inv_id: inv_id });
        hideLoader();

        const invoice  = res.data['invoice'];
        const customer = res.data['customer'];

        if (!invoice) {
            return errorToast('That invoice could not be loaded.');
        }

        document.getElementById('invNo').innerText   = '#' + invoice['id'];
        document.getElementById('CName').innerText   = customer ? customer['name'] : 'Walk-in';
        document.getElementById('CEmail').innerText  = customer ? customer['email'] : '';
        document.getElementById('CMobile').innerText = customer ? customer['mobile'] : '';
        document.getElementById('invDate').innerText = new Date(invoice['created_at'])
            .toLocaleString(undefined, { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

        document.getElementById('total').innerText    = money(invoice['total']);
        document.getElementById('payable').innerText  = money(invoice['payable']);
        document.getElementById('vat').innerText      = money(invoice['vat']);
        document.getElementById('discount').innerText = money(invoice['discount']);

        let invoiceList = $('#invoiceList');
        invoiceList.empty();

        res.data['product'].forEach(function (item) {
            // A product deleted after the sale leaves the line without one;
            // the invoice still has to render.
            const name = item['product'] ? item['product']['name'] : 'Removed product';
            invoiceList.append(`<tr>
                        <td>${escapeHtml(name)}</td>
                        <td class="num">${item['qty']}</td>
                        <td class="num">${money(item['sale_price'])}</td>
                     </tr>`);
        });

        $("#details-modal").modal('show');
    }

    function PrintPage() {
        const root = document.createElement('div');
        root.id = 'print-root';
        root.innerHTML = document.getElementById('invoice').innerHTML;
        document.body.appendChild(root);
        document.documentElement.classList.add('printing');

        window.print();

        document.documentElement.classList.remove('printing');
        root.remove();
    }
</script>
