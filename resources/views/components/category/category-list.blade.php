<div class="page">

    <div class="page-head">
        <div>
            <h1>Categories</h1>
            <p class="sub">How the catalogue is grouped. A category in use cannot be removed.</p>
        </div>
        <div class="actions">
            <button data-bs-toggle="modal" data-bs-target="#create-modal" class="btn btn-accent">
                <i class="bi bi-plus-lg me-1"></i> Add category
            </button>
        </div>
    </div>

    <div class="panel" style="max-width:760px">
        <div class="panel-head">
            <h2>All categories</h2>
            <span class="hint" id="categoryCount"></span>
        </div>
        <div class="panel-body tight">
            <table class="table tidy" id="tableData">
                <thead>
                <tr>
                    <th>Name</th>
                    <th class="num">Products</th>
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
    let res = await axios.get("/list-category");
    hideLoader();

    let tableList = $("#tableList");
    let tableData = $("#tableData");

    tableData.DataTable().destroy();
    tableList.empty();

    res.data.forEach(function (item) {
        const count = Number(item['products_count'] || 0);

        let row = `<tr>
                    <td><div class="cell-title">${escapeHtml(item['name'])}</div></td>
                    <td class="num">
                        <span class="tag ${count ? 'tag-mute' : 'tag-low'}">${count ? count + (count === 1 ? ' product' : ' products') : 'empty'}</span>
                    </td>
                    <td class="num" style="white-space:nowrap">
                        <button title="Edit" data-id="${item['id']}" class="btn-icon editBtn"><i class="bi bi-pencil"></i></button>
                        <button title="Delete" data-id="${item['id']}" data-name="${escapeHtml(item['name'])}" class="btn-icon danger deleteBtn"><i class="bi bi-trash3"></i></button>
                    </td>
                 </tr>`;
        tableList.append(row);
    });

    $("#categoryCount").text(res.data.length ? `${res.data.length} categories` : '');

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
        columnDefs: [{ orderable: false, targets: 2 }]
    });
}
</script>
