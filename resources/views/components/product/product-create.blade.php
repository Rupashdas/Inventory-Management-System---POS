<div class="modal animated zoomIn" id="create-modal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Add a product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="save-form">
                    <div class="row g-3">

                        <div class="col-md-8">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" id="productName" placeholder="Cold Brew Coffee, 32 oz">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <select class="form-control form-select" id="productCategory">
                                <option value="">Choose one</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Price</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="productPrice" placeholder="6.49">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Unit</label>
                            <input type="text" class="form-control" id="productUnit" placeholder="bottle">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Opening stock</label>
                            <input type="number" min="0" class="form-control" id="productStock" value="0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Reorder at</label>
                            <input type="number" min="0" class="form-control" id="productThreshold" value="5">
                            {{-- Per product, because "low" means something different for
                                 envelopes than it does for desk lamps. --}}
                            <div class="form-hint">Flagged as low once stock falls to this.</div>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Image</label>
                            <input oninput="newImg.src=window.URL.createObjectURL(this.files[0])" type="file" accept="image/*" class="form-control" id="productImg">
                            <div class="form-hint">JPG, PNG or WebP, up to 2&nbsp;MB.</div>
                        </div>

                        <div class="col-12">
                            <img class="thumb" style="width:74px;height:74px" id="newImg" src="{{asset('images/default.jpg')}}" alt="Preview"/>
                        </div>

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button id="modal-close" class="btn btn-quiet" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                <button onclick="Save()" id="save-btn" class="btn btn-accent">Save product</button>
            </div>
        </div>
    </div>
</div>


<script>
    FillCategoryDropDown();

    async function FillCategoryDropDown() {
        let res = await axios.get("/list-category");
        res.data.forEach(function (item) {
            $("#productCategory").append(`<option value="${item['id']}">${escapeHtml(item['name'])}</option>`);
        });
    }

    async function Save() {
        const category  = document.getElementById('productCategory').value;
        const name      = document.getElementById('productName').value.trim();
        const price     = document.getElementById('productPrice').value;
        const unit      = document.getElementById('productUnit').value.trim();
        const stock     = document.getElementById('productStock').value;
        const threshold = document.getElementById('productThreshold').value;
        const img       = document.getElementById('productImg').files[0];

        if (!name)     { return errorToast("Give the product a name."); }
        if (!category) { return errorToast("Choose a category."); }
        if (price === '') { return errorToast("Set a price."); }
        if (!unit)     { return errorToast("Say what it is sold by — a box, a piece, a pound."); }
        if (!img)      { return errorToast("Add a product image."); }

        const formData = new FormData();
        formData.append('img', img);
        formData.append('name', name);
        formData.append('price', price);
        formData.append('unit', unit);
        formData.append('stock', stock || 0);
        formData.append('low_stock_threshold', threshold || 0);
        formData.append('category_id', category);

        const result = await apiPost("/create-product", formData, {
            headers: { 'content-type': 'multipart/form-data' }
        });

        // The modal stays open on failure so the half-filled form is still
        // there to correct, rather than being thrown away with the message.
        if (result) {
            document.getElementById('modal-close').click();
            successToast(result.message || 'Product created.');
            document.getElementById("save-form").reset();
            document.getElementById('newImg').src = "{{asset('images/default.jpg')}}";
            await getList();
        }
    }
</script>
