<div class="modal animated zoomIn" id="update-modal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">Edit product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="update-form">
                    <div class="row g-3">

                        <div class="col-md-8">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" id="productNameUpdate">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <select class="form-control form-select" id="productCategoryUpdate">
                                <option value="">Choose one</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Price</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="productPriceUpdate">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Unit</label>
                            <input type="text" class="form-control" id="productUnitUpdate">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Reorder at</label>
                            <input type="number" min="0" class="form-control" id="productThresholdUpdate">
                        </div>

                        <div class="col-12">
                            <div class="customer-slot">
                                <i class="bi bi-info-circle me-1"></i>
                                Stock is <strong id="currentStockLabel">—</strong> and is not edited here.
                                It moves through <strong>Add stock</strong> and through sales, so the
                                balance and its history stay in step.
                            </div>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Replace image</label>
                            <input oninput="oldImg.src=window.URL.createObjectURL(this.files[0])" type="file" accept="image/*" class="form-control" id="productImgUpdate">
                            <div class="form-hint">Leave empty to keep the current one.</div>
                        </div>

                        <div class="col-md-4">
                            <img class="thumb" style="width:74px;height:74px" id="oldImg" src="{{asset('images/default.jpg')}}" alt="Current image"/>
                        </div>

                        <input type="hidden" id="updateID">

                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button id="update-modal-close" class="btn btn-quiet" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                <button onclick="update()" id="update-btn" class="btn btn-accent">Save changes</button>
            </div>

        </div>
    </div>
</div>


<script>
    async function UpdateFillCategoryDropDown() {
        let res = await axios.get("/list-category");
        // Rebuilt rather than appended to: opening the form twice used to stack
        // a second copy of every category onto the list.
        const select = $("#productCategoryUpdate");
        select.empty().append('<option value="">Choose one</option>');
        res.data.forEach(function (item) {
            select.append(`<option value="${item['id']}">${escapeHtml(item['name'])}</option>`);
        });
    }

    async function FillUpUpdateForm(id) {
        document.getElementById('updateID').value = id;

        showLoader();
        await UpdateFillCategoryDropDown();
        let res = await axios.post("/product-by-id", { id: id });
        hideLoader();

        if (!res.data) {
            errorToast('That product could not be loaded.');
            return;
        }

        document.getElementById('productNameUpdate').value      = res.data['name'];
        document.getElementById('productPriceUpdate').value     = res.data['price'];
        document.getElementById('productUnitUpdate').value      = res.data['unit'];
        document.getElementById('productCategoryUpdate').value  = res.data['category_id'];
        document.getElementById('productThresholdUpdate').value = res.data['low_stock_threshold'];
        document.getElementById('currentStockLabel').innerText  = res.data['stock'] + ' ' + res.data['unit'];
        // The image path comes back with the record, so the form no longer has
        // to be handed one by whatever row was clicked.
        document.getElementById('oldImg').src = '/' + res.data['img_url'];
    }

    async function update() {
        const category  = document.getElementById('productCategoryUpdate').value;
        const name      = document.getElementById('productNameUpdate').value.trim();
        const price     = document.getElementById('productPriceUpdate').value;
        const unit      = document.getElementById('productUnitUpdate').value.trim();
        const threshold = document.getElementById('productThresholdUpdate').value;
        const id        = document.getElementById('updateID').value;
        const img       = document.getElementById('productImgUpdate').files[0];

        if (!name)     { return errorToast("Give the product a name."); }
        if (!category) { return errorToast("Choose a category."); }
        if (price === '') { return errorToast("Set a price."); }
        if (!unit)     { return errorToast("Say what it is sold by."); }

        const formData = new FormData();
        // Only appended when one was actually chosen -- an empty file input
        // used to be sent as the string "undefined" and fail validation.
        if (img) {
            formData.append('img', img);
        }
        formData.append('id', id);
        formData.append('name', name);
        formData.append('price', price);
        formData.append('unit', unit);
        formData.append('low_stock_threshold', threshold || 0);
        formData.append('category_id', category);

        const result = await apiPost("/update-product", formData, {
            headers: { 'content-type': 'multipart/form-data' }
        });

        if (result) {
            document.getElementById('update-modal-close').click();
            successToast(result.message || 'Product updated.');
            document.getElementById("update-form").reset();
            await getList();
        }
    }
</script>
