<div class="page">

    <div class="page-head">
        <div>
            <h1>Customers</h1>
            <p class="sub">Who you sell to. A customer with invoices cannot be removed.</p>
        </div>
        <div class="actions">
            <button data-bs-toggle="modal" data-bs-target="#create-modal" class="btn btn-accent">
                <i class="bi bi-person-plus me-1"></i> Add customer
            </button>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h2>Customer list</h2>
            <span class="hint" id="customerCount"></span>
        </div>
        <div class="panel-body tight">
            <table class="table tidy" id="tableData">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Mobile</th>
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
    let res = await axios.get("/list-customer");
    hideLoader();

    let tableList = $("#tableList");
    let tableData = $("#tableData");

    tableData.DataTable().destroy();
    tableList.empty();

    res.data.forEach(function (item) {
        let row = `<tr>
                    <td><div class="cell-title">${escapeHtml(item['name'])}</div></td>
                    <td class="cell-sub">${escapeHtml(item['email'])}</td>
                    <td class="cell-sub">${escapeHtml(item['mobile'])}</td>
                    <td class="num" style="white-space:nowrap">
                        <button title="Edit" data-id="${item['id']}" class="btn-icon editBtn"><i class="bi bi-pencil"></i></button>
                        <button title="Delete" data-id="${item['id']}" data-name="${escapeHtml(item['name'])}" class="btn-icon danger deleteBtn"><i class="bi bi-trash3"></i></button>
                    </td>
                 </tr>`;
        tableList.append(row);
    });

    $("#customerCount").text(res.data.length ? `${res.data.length} on the books` : '');

    $('.editBtn').on('click', async function () {
        await FillUpUpdateForm($(this).data('id'));
        $("#update-modal").modal('show');
    });

    $('.deleteBtn').on('click', function () {
        $("#deleteID").val($(this).data('id'));
        $("#deleteName").text($(this).data('name'));
        $("#delete-modal").modal('show');
    });

    new DataTable('#tableData', {
        order: [[0, 'asc']],
        lengthMenu: [10, 15, 25, 50],
        columnDefs: [{ orderable: false, targets: 3 }]
    });
}
</script>
