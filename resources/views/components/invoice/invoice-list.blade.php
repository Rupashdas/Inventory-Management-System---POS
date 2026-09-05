<div class="page">

    <div class="page-head">
        <div>
            <h1>Invoices</h1>
            <p class="sub">Every sale recorded, newest first. Deleting one puts its stock back.</p>
        </div>
        <div class="actions">
            <a href="{{url('/salePage')}}" class="btn btn-accent">
                <i class="bi bi-upc-scan me-1"></i> New sale
            </a>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h2>All invoices</h2>
            <span class="hint" id="invoiceTotals"></span>
        </div>
        <div class="panel-body tight">
            <table class="table tidy" id="tableData">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Customer</th>
                    <th class="num">Total</th>
                    <th class="num">Discount</th>
                    <th class="num">Tax</th>
                    <th class="num">Payable</th>
                    <th>Date</th>
                    <th class="num">Actions</th>
                </tr>
                </thead>
                <tbody id="tableList"></tbody>
            </table>
        </div>
    </div>

</div>

<script>
getList();

async function getList() {
    showLoader();
    let res = await axios.get("/invoice-select");
    hideLoader();

    let tableList = $("#tableList");
    let tableData = $("#tableData");

    tableData.DataTable().destroy();
    tableList.empty();

    let collected = 0;

    res.data.forEach(function (item) {
        collected += Number(item['payable']);

        // A customer can be deleted while their invoices remain, so the name is
        // not assumed to be there -- reading .name off nothing used to blank
        // the whole table.
        const customer = item['customer'];

        let row = `<tr>
                    <td class="cell-sub">#${item['id']}</td>
                    <td>
                        <div class="cell-title">${escapeHtml(customer ? customer['name'] : 'Walk-in')}</div>
                        <div class="cell-sub">${escapeHtml(customer ? customer['mobile'] : '')}</div>
                    </td>
                    <td class="num">${money(item['total'])}</td>
                    <td class="num">${money(item['discount'])}</td>
                    <td class="num">${money(item['vat'])}</td>
                    <td class="num"><strong>${money(item['payable'])}</strong></td>
                    <td class="cell-sub">${new Date(item['created_at']).toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' })}</td>
                    <td class="num" style="white-space:nowrap">
                        <button title="View" data-id="${item['id']}" data-cus="${customer ? customer['id'] : ''}" class="btn-icon viewBtn"><i class="bi bi-eye"></i></button>
                        <button title="Delete" data-id="${item['id']}" class="btn-icon danger deleteBtn"><i class="bi bi-trash3"></i></button>
                    </td>
                 </tr>`;
        tableList.append(row);
    });

    $("#invoiceTotals").text(res.data.length
        ? `${res.data.length} invoices · $ ${money(collected)} collected`
        : '');

    $('.viewBtn').on('click', async function () {
        await InvoiceDetails($(this).data('cus'), $(this).data('id'));
    });

    $('.deleteBtn').on('click', function () {
        document.getElementById('deleteID').value = $(this).data('id');
        $("#delete-modal").modal('show');
    });

    new DataTable('#tableData', {
        order: [[0, 'desc']],
        lengthMenu: [10, 15, 25, 50],
        columnDefs: [{ orderable: false, targets: 7 }]
    });
}
</script>
