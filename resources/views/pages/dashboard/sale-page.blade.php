@extends('layout.sidenav-layout')
@section('title', 'New sale')
@section('content')

    <div class="page">

        <div class="page-head">
            <div>
                <h1>New sale</h1>
                <p class="sub">Pick the customer, add what they are buying, take the money.</p>
            </div>
            <div class="actions">
                <a href="{{ url('/invoicePage') }}" class="btn btn-quiet">
                    <i class="bi bi-receipt me-1"></i> Invoices
                </a>
            </div>
        </div>

        {{--
            Three columns in the order the sale happens: the bill being built,
            the shelf, and the customer list. The bill is first and widest
            because it is the thing being watched while the other two are only
            being picked from.
        --}}
        <div class="till">

            <div class="panel">
                <div class="panel-head">
                    <h2>This sale</h2>
                    <span class="hint">{{ date('d M Y') }}</span>
                </div>
                <div class="panel-body">

                    <div class="customer-slot mb-3" id="customerSlot">
                        <span id="customerEmpty"><i class="bi bi-person-plus me-1"></i> No customer chosen yet</span>
                        <span id="customerFilled" class="d-none">
                            <strong id="CName"></strong><br/>
                            <span class="cell-sub" id="CEmail"></span>
                        </span>
                        <span class="d-none" id="CId"></span>
                    </div>

                    <table class="tidy" id="invoiceTable">
                        <thead>
                        <tr>
                            <th>Item</th>
                            <th class="num">Qty</th>
                            <th class="num">Line</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody id="invoiceList"></tbody>
                    </table>

                    <hr class="rule"/>

                    <label class="form-label">Discount (%)</label>
                    <input value="0" min="0" max="100" type="number" step="0.5" oninput="CalculateGrandTotal()" class="form-control" id="discountP"/>

                    <div class="mt-3">
                        <div class="till-total"><span>Subtotal</span> <strong>$ <span id="subtotal">0.00</span></strong></div>
                        <div class="till-total"><span>Discount</span> <strong>&minus; $ <span id="discount">0.00</span></strong></div>
                        <div class="till-total"><span>Sales tax (5%)</span> <strong>$ <span id="vat">0.00</span></strong></div>
                        <div class="till-total grand"><span>To pay</span> <strong>$ <span id="payable">0.00</span></strong></div>
                    </div>

                    <button onclick="createInvoice()" class="btn btn-accent w-100 mt-3 py-2" id="confirmBtn">
                        <i class="bi bi-check2-circle me-1"></i> Confirm sale
                    </button>
                    <div class="form-hint text-center mt-2">
                        Prices and tax are recalculated on the server from the catalogue.
                    </div>

                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <h2>Shelf</h2>
                    <span class="hint">Out-of-stock items cannot be added</span>
                </div>
                <div class="panel-body tight">
                    <table class="tidy" id="productTable">
                        <thead>
                        <tr>
                            <th></th>
                            <th>Product</th>
                            <th>Stock</th>
                            <th class="num"></th>
                        </tr>
                        </thead>
                        <tbody id="productList"></tbody>
                    </table>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <h2>Customer</h2>
                </div>
                <div class="panel-body tight">
                    <table class="tidy" id="customerTable">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th class="num"></th>
                        </tr>
                        </thead>
                        <tbody id="customerList"></tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>


    <div class="modal animated zoomIn" id="create-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add to the sale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="add-form">
                        <div class="customer-slot filled mb-3">
                            <strong id="PNameLabel">&mdash;</strong><br/>
                            <span class="cell-sub">$ <span id="PPriceLabel">0</span> each &middot; <span id="PStockLabel">0</span> in stock</span>
                        </div>

                        <label class="form-label">Quantity</label>
                        <input type="number" min="1" step="1" class="form-control" id="PQty" value="1">
                        <div class="form-hint" id="qtyHint"></div>

                        <input type="hidden" id="PId">
                        <input type="hidden" id="PName">
                        <input type="hidden" id="PPrice">
                        <input type="hidden" id="PStock">
                    </form>
                </div>
                <div class="modal-footer">
                    <button id="modal-close" class="btn btn-quiet" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                    <button onclick="add()" id="save-btn" class="btn btn-accent">Add to sale</button>
                </div>
            </div>
        </div>
    </div>


    <script>

        (async () => {
            showLoader();
            await CustomerList();
            await ProductList();
            hideLoader();
        })();

        let InvoiceItemList = [];
        // Kept alongside the basket so the shelf's remaining figures can be
        // shown net of what is already in this sale, rather than only being
        // caught by the server at Confirm.
        let StockById = {};

        function ShowInvoiceItem() {
            let invoiceList = $('#invoiceList');
            invoiceList.empty();

            if (!InvoiceItemList.length) {
                invoiceList.append('<tr><td colspan="4"><div class="empty"><i class="bi bi-basket"></i><p>Nothing added yet.</p></div></td></tr>');
            }

            InvoiceItemList.forEach(function (item, index) {
                let row = `<tr>
                        <td><div class="cell-title">${escapeHtml(item['product_name'])}</div></td>
                        <td class="num">${item['qty']}</td>
                        <td class="num">${money(item['sale_price'])}</td>
                        <td class="num"><button data-index="${index}" class="btn-icon danger remove" title="Remove"><i class="bi bi-x-lg"></i></button></td>
                     </tr>`;
                invoiceList.append(row);
            });

            CalculateGrandTotal();

            $('.remove').on('click', function () {
                removeItem($(this).data('index'));
            });
        }

        function removeItem(index) {
            InvoiceItemList.splice(index, 1);
            ShowInvoiceItem();
            ProductList();
        }

        /**
         * The figures shown while the sale is being built.
         *
         * These are a preview, not the invoice. The server recomputes all of it
         * from the catalogue when Confirm is pressed -- what the browser
         * displays here and what is charged are worked out independently, and
         * the server's answer is the one that counts.
         */
        function CalculateGrandTotal() {
            const subtotal = InvoiceItemList.reduce((sum, item) => sum + parseFloat(item['sale_price']), 0);

            let percent = parseFloat(document.getElementById('discountP').value);
            if (isNaN(percent) || percent < 0) { percent = 0; }
            if (percent > 100) { percent = 100; }

            const discount = subtotal * percent / 100;
            const total    = subtotal - discount;
            const vat      = total * 0.05;
            const payable  = total + vat;

            document.getElementById('subtotal').innerText = money(subtotal);
            document.getElementById('discount').innerText = money(discount);
            document.getElementById('vat').innerText      = money(vat);
            document.getElementById('payable').innerText  = money(payable);
        }

        function quantityAlreadyInBasket(productId) {
            return InvoiceItemList
                .filter(item => String(item['product_id']) === String(productId))
                .reduce((sum, item) => sum + Number(item['qty']), 0);
        }

        function add() {
            const id    = document.getElementById('PId').value;
            const name  = document.getElementById('PName').value;
            const price = parseFloat(document.getElementById('PPrice').value);
            const qty   = parseInt(document.getElementById('PQty').value, 10);
            const stock = Number(document.getElementById('PStock').value);

            if (!qty || qty < 1) {
                return errorToast("Enter a quantity of at least one.");
            }

            const remaining = stock - quantityAlreadyInBasket(id);
            if (qty > remaining) {
                // Caught here as well as on the server: the cashier finds out
                // while the customer is still deciding, not at Confirm.
                return errorToast(remaining > 0
                    ? `Only ${remaining} of ${name} left to sell.`
                    : `${name} is already fully allocated to this sale.`);
            }

            InvoiceItemList.push({
                product_name: name,
                product_id: id,
                qty: qty,
                sale_price: (price * qty).toFixed(2)
            });

            $('#create-modal').modal('hide');
            ShowInvoiceItem();
            ProductList();
        }

        function addModal(id, name, price, stock) {
            document.getElementById('PId').value    = id;
            document.getElementById('PName').value  = name;
            document.getElementById('PPrice').value = price;
            document.getElementById('PStock').value = stock;
            document.getElementById('PQty').value   = 1;

            const remaining = stock - quantityAlreadyInBasket(id);
            document.getElementById('PNameLabel').innerText  = name;
            document.getElementById('PPriceLabel').innerText = money(price);
            document.getElementById('PStockLabel').innerText = stock;
            document.getElementById('PQty').max = remaining;
            document.getElementById('qtyHint').innerText = remaining < stock
                ? `${remaining} still available — ${stock - remaining} already on this sale.`
                : '';

            $('#create-modal').modal('show');
        }

        async function CustomerList() {
            let res = await axios.get("/list-customer");
            let customerList = $("#customerList");

            $("#customerTable").DataTable().destroy();
            customerList.empty();

            if (!res.data.length) {
                customerList.append('<tr><td colspan="2"><div class="empty"><i class="bi bi-people"></i><p>No customers yet.</p></div></td></tr>');
            }

            res.data.forEach(function (item) {
                let row = `<tr>
                        <td>
                            <div class="cell-title">${escapeHtml(item['name'])}</div>
                            <div class="cell-sub">${escapeHtml(item['mobile'] || '')}</div>
                        </td>
                        <td class="num"><a data-name="${escapeHtml(item['name'])}" data-email="${escapeHtml(item['email'])}" data-id="${item['id']}" class="btn btn-quiet btn-mini addCustomer">Pick</a></td>
                     </tr>`;
                customerList.append(row);
            });

            $('.addCustomer').on('click', function () {
                $("#CName").text($(this).data('name'));
                $("#CEmail").text($(this).data('email'));
                $("#CId").text($(this).data('id'));
                $("#customerEmpty").addClass('d-none');
                $("#customerFilled").removeClass('d-none');
                $("#customerSlot").addClass('filled');
            });

            if (res.data.length) {
                new DataTable('#customerTable', {
                    order: [[0, 'asc']],
                    scrollCollapse: false,
                    info: false,
                    lengthChange: false,
                    columnDefs: [{ orderable: false, targets: 1 }]
                });
            }
        }

        async function ProductList() {
            let res = await axios.get("/list-product");
            let productList = $("#productList");

            $("#productTable").DataTable().destroy();
            productList.empty();

            StockById = {};

            if (!res.data.length) {
                productList.append('<tr><td colspan="4"><div class="empty"><i class="bi bi-box-seam"></i><p>No products yet.</p></div></td></tr>');
            }

            res.data.forEach(function (item) {
                const stock = Number(item['stock']);
                StockById[item['id']] = stock;

                const remaining = stock - quantityAlreadyInBasket(item['id']);
                const sellable = remaining > 0;

                let row = `<tr>
                        <td style="width:46px"><img class="thumb" src="/${escapeHtml(item['img_url'])}" alt=""/></td>
                        <td>
                            <div class="cell-title">${escapeHtml(item['name'])}</div>
                            <div class="cell-sub">$ ${money(item['price'])} per ${escapeHtml(item['unit'])}</div>
                        </td>
                        <td>${stockTag(remaining, Number(item['low_stock_threshold']))}</td>
                        <td class="num">
                            <a data-name="${escapeHtml(item['name'])}" data-price="${item['price']}" data-id="${item['id']}" data-stock="${stock}"
                               class="btn btn-mini ${sellable ? 'btn-accent addProduct' : 'btn-quiet disabled'}"
                               ${sellable ? '' : 'aria-disabled="true"'}>Add</a>
                        </td>
                     </tr>`;
                productList.append(row);
            });

            $('.addProduct').on('click', function () {
                addModal($(this).data('id'), $(this).data('name'), $(this).data('price'), $(this).data('stock'));
            });

            if (res.data.length) {
                new DataTable('#productTable', {
                    order: [[1, 'asc']],
                    scrollCollapse: false,
                    info: false,
                    lengthChange: false,
                    columnDefs: [{ orderable: false, targets: [0, 3] }]
                });
            }
        }

        async function createInvoice() {
            const customerId = document.getElementById('CId').innerText;

            if (!customerId) {
                return errorToast("Pick a customer first.");
            }
            if (!InvoiceItemList.length) {
                return errorToast("Add at least one product.");
            }

            // Only the facts of the order go up. The totals shown on screen are
            // not sent at all -- the server has the prices, so it does the
            // arithmetic and the browser cannot argue with the result.
            const result = await apiPost("/invoice-create", {
                customer_id: customerId,
                discount_percent: document.getElementById('discountP').value || 0,
                products: InvoiceItemList.map(item => ({
                    product_id: item['product_id'],
                    qty: item['qty']
                }))
            });

            if (result) {
                successToast(`Invoice #${result.invoice_id} — $ ${money(result.payable)}`);
                window.location.href = '/invoicePage';
            }
        }

        ShowInvoiceItem();

    </script>

@endsection
