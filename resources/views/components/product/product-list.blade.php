<div class="page">

    <div class="page-head">
        <div>
            <h1>Products &amp; stock</h1>
            <p class="sub">What is on the shelf, what it costs, and what is about to run out.</p>
        </div>
        <div class="actions">
            <a href="{{ url('/stock-report') }}" class="btn btn-quiet">
                <i class="bi bi-file-earmark-arrow-down me-1"></i> Stock report
            </a>
            <button data-bs-toggle="modal" data-bs-target="#create-modal" class="btn btn-accent">
                <i class="bi bi-plus-lg me-1"></i> Add product
            </button>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h2>Catalogue</h2>
            <span class="hint" id="stockValue"></span>
        </div>
        <div class="panel-body tight">
            <table class="table tidy" id="tableData">
                <thead>
                <tr>
                    {{-- One <th> per body cell: DataTables counts them, and a
                         colspan here leaves it reading a column that is not there. --}}
                    <th></th>
                    <th>Product</th>
                    <th>Category</th>
                    <th class="num">Price</th>
                    <th>Stock</th>
                    <th class="num">Value</th>
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
    let res = await axios.get("/list-product");
    hideLoader();

    let tableList = $("#tableList");
    let tableData = $("#tableData");

    tableData.DataTable().destroy();
    tableList.empty();

    let totalValue = 0;
    let units = 0;

    res.data.forEach(function (item) {
        const stock = Number(item['stock']);
        const price = Number(item['price']);
        const lineValue = stock * price;
        totalValue += lineValue;
        units += stock;

        let row = `<tr>
                    <td style="width:52px"><img class="thumb" alt="" src="/${escapeHtml(item['img_url'])}"></td>
                    <td>
                        <div class="cell-title">${escapeHtml(item['name'])}</div>
                        <div class="cell-sub">per ${escapeHtml(item['unit'])}</div>
                    </td>
                    <td><span class="tag tag-mute">${escapeHtml(item['category'] ? item['category']['name'] : 'Uncategorised')}</span></td>
                    <td class="num">${money(price)}</td>
                    <td>${stockTag(stock, Number(item['low_stock_threshold']))}</td>
                    <td class="num">${money(lineValue)}</td>
                    <td class="num" style="white-space:nowrap">
                        <button title="Add stock" data-id="${item['id']}" data-name="${escapeHtml(item['name'])}" data-stock="${stock}" class="btn-icon restockBtn"><i class="bi bi-plus-slash-minus"></i></button>
                        <button title="Stock history" data-id="${item['id']}" class="btn-icon historyBtn"><i class="bi bi-clock-history"></i></button>
                        <button title="Edit" data-id="${item['id']}" class="btn-icon editBtn"><i class="bi bi-pencil"></i></button>
                        <button title="Delete" data-id="${item['id']}" data-name="${escapeHtml(item['name'])}" class="btn-icon danger deleteBtn"><i class="bi bi-trash3"></i></button>
                    </td>
                 </tr>`;
        tableList.append(row);
    });

    // The number the owner cares about is the money sitting on the shelf, not
    // the row count, so it goes in the panel header rather than a tooltip.
    $("#stockValue").text(res.data.length
        ? units.toLocaleString() + ' units on hand · $ ' + money(totalValue) + ' at retail'
        : '');

    $('.editBtn').on('click', async function () {
        await FillUpUpdateForm($(this).data('id'));
        $("#update-modal").modal('show');
    });

    $('.deleteBtn').on('click', function () {
        $("#deleteID").val($(this).data('id'));
        $("#deleteName").text($(this).data('name'));
        $("#delete-modal").modal('show');
    });

    $('.restockBtn').on('click', function () {
        $("#restockID").val($(this).data('id'));
        $("#restockName").text($(this).data('name'));
        $("#restockCurrent").text($(this).data('stock'));
        $("#restockQty").val('');
        $("#restockNote").val('');
        $("#restock-modal").modal('show');
    });

    $('.historyBtn').on('click', async function () {
        await ShowStockHistory($(this).data('id'));
    });

    new DataTable('#tableData', {
        order: [[1, 'asc']],
        lengthMenu: [10, 15, 25, 50],
        columnDefs: [{ orderable: false, targets: [0, 6] }]
    });
}
</script>
