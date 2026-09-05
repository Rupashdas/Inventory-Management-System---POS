<div class="page">

    <div class="page-head">
        <div>
            <h1>Dashboard</h1>
            <p class="sub">Today's trade, the last fortnight, and what needs reordering.</p>
        </div>
        <div class="actions">
            <a href="{{ url('/salePage') }}" class="btn btn-accent">
                <i class="bi bi-upc-scan me-1"></i> New sale
            </a>
            <a href="{{ url('/reportPage') }}" class="btn btn-quiet">
                <i class="bi bi-file-earmark-arrow-down me-1"></i> Reports
            </a>
        </div>
    </div>

    {{--
        The order here is the order the questions get asked in. Money taken
        today comes first because that is what the page is opened for; the
        catalogue counts, which change once a week, come last.
    --}}
    <div class="stat-grid">

        <div class="stat">
            <div>
                <div class="label">Taken today</div>
                <div class="value">$ <span id="today_sales">&mdash;</span></div>
                <div class="foot"><span id="invoice">&mdash;</span> invoices all time</div>
            </div>
            <span class="glyph"><i class="bi bi-cash-coin"></i></span>
        </div>

        <div class="stat">
            <div>
                <div class="label">Total collected</div>
                <div class="value">$ <span id="payable">&mdash;</span></div>
                <div class="foot">$ <span id="vat">&mdash;</span> of it sales tax</div>
            </div>
            <span class="glyph"><i class="bi bi-graph-up-arrow"></i></span>
        </div>

        <div class="stat" id="lowStockTile">
            <div>
                <div class="label">Running low</div>
                <div class="value"><span id="low_stock">&mdash;</span></div>
                <div class="foot">at or below their reorder point</div>
            </div>
            <span class="glyph"><i class="bi bi-exclamation-triangle"></i></span>
        </div>

        <div class="stat" id="outStockTile">
            <div>
                <div class="label">Out of stock</div>
                <div class="value"><span id="out_of_stock">&mdash;</span></div>
                <div class="foot">cannot be sold until restocked</div>
            </div>
            <span class="glyph"><i class="bi bi-slash-circle"></i></span>
        </div>

        <div class="stat">
            <div>
                <div class="label">Catalogue</div>
                <div class="value"><span id="product">&mdash;</span></div>
                <div class="foot"><span id="category">&mdash;</span> categories &middot; <span id="customer">&mdash;</span> customers</div>
            </div>
            <span class="glyph"><i class="bi bi-box-seam"></i></span>
        </div>

    </div>

    <div class="grid-2 mb-3">

        <div class="panel">
            <div class="panel-head">
                <h2>Takings, last 14 days</h2>
                <span class="hint" id="trendTotal"></span>
            </div>
            <div class="panel-body">
                <div class="chart-holder">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h2>Needs reordering</h2>
                <a href="{{ url('/productPage') }}" class="hint">All products &rarr;</a>
            </div>
            <div class="panel-body tight">
                <table class="tidy">
                    <tbody id="lowStockList"></tbody>
                </table>
            </div>
        </div>

    </div>

    <div class="grid-2">

        <div class="panel">
            <div class="panel-head">
                <h2>Best sellers</h2>
                <span class="hint">by units moved</span>
            </div>
            <div class="panel-body tight">
                <table class="tidy">
                    <tbody id="topProducts"></tbody>
                </table>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h2>Recent invoices</h2>
                <a href="{{ url('/invoicePage') }}" class="hint">All invoices &rarr;</a>
            </div>
            <div class="panel-body tight">
                <table class="tidy">
                    <tbody id="recentInvoices"></tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
    {{-- CURRENCY comes from config.js. Redeclaring it here with const threw a
         SyntaxError that stopped this whole script before a line of it ran. --}}
    const emptyRow = (icon, text) =>
        `<tr><td colspan="3"><div class="empty"><i class="bi ${icon}"></i><p>${text}</p></div></td></tr>`;

    (async () => {
        showLoader();
        try {
            // Five independent reads, so they go out together rather than
            // queueing behind each other for five round trips.
            const [summary, trend, low, top, recent] = await Promise.all([
                axios.get('/summary'),
                axios.get('/sales-trend'),
                axios.get('/low-stock'),
                axios.get('/top-products'),
                axios.get('/recent-invoices'),
            ]);

            paintSummary(summary.data);
            paintChart(trend.data);
            paintLowStock(low.data);
            paintTopProducts(top.data);
            paintRecent(recent.data);
        } catch (error) {
            errorToast('Could not load the dashboard.');
        } finally {
            hideLoader();
        }
    })();

    function paintSummary(data) {
        document.getElementById('product').innerText      = data.product;
        document.getElementById('category').innerText     = data.category;
        document.getElementById('customer').innerText     = data.customer;
        document.getElementById('invoice').innerText      = data.invoice;
        document.getElementById('vat').innerText          = money(data.vat);
        document.getElementById('payable').innerText      = money(data.payable);
        document.getElementById('today_sales').innerText  = money(data.today_sales);
        document.getElementById('low_stock').innerText    = data.low_stock;
        document.getElementById('out_of_stock').innerText = data.out_of_stock;

        // The two stock tiles only turn amber or red when there is actually
        // something wrong -- a permanently coloured warning stops being read.
        if (data.low_stock > 0) {
            document.getElementById('lowStockTile').classList.add('is-warn');
        }
        if (data.out_of_stock > 0) {
            document.getElementById('outStockTile').classList.add('is-bad');
        }
    }

    function paintChart(trend) {
        const fortnight = trend.takings.reduce((sum, n) => sum + n, 0);
        document.getElementById('trendTotal').innerText = CURRENCY + ' ' + money(fortnight) + ' in 14 days';

        const canvas = document.getElementById('salesChart');
        const fill = canvas.getContext('2d').createLinearGradient(0, 0, 0, 260);
        fill.addColorStop(0, 'rgba(81, 69, 205, 0.22)');
        fill.addColorStop(1, 'rgba(81, 69, 205, 0.01)');

        new Chart(canvas, {
            type: 'line',
            data: {
                labels: trend.labels,
                datasets: [{
                    label: 'Takings',
                    data: trend.takings,
                    borderColor: '#5145cd',
                    backgroundColor: fill,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.32,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: '#5145cd',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#191c2e',
                        padding: 10,
                        cornerRadius: 7,
                        displayColors: false,
                        callbacks: {
                            // The order count is the part that explains the
                            // takings, so it rides along in the tooltip.
                            label: (ctx) => {
                                const orders = trend.orders[ctx.dataIndex];
                                const noun = orders === 1 ? 'order' : 'orders';
                                return `${CURRENCY} ${money(ctx.parsed.y)} · ${orders} ${noun}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { color: '#8b91a8', font: { size: 11 }, maxRotation: 0, autoSkipPadding: 14 }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#eff1f5' },
                        border: { display: false },
                        ticks: {
                            color: '#8b91a8',
                            font: { size: 11 },
                            maxTicksLimit: 5,
                            callback: (v) => v >= 1000 ? (v / 1000) + 'k' : v
                        }
                    }
                }
            }
        });
    }

    function paintLowStock(items) {
        const body = document.getElementById('lowStockList');

        if (!items.length) {
            body.innerHTML = emptyRow('bi-check2-circle', 'Everything is above its reorder point.');
            return;
        }

        body.innerHTML = items.map(item => `
            <tr>
                <td style="width:44px"><img class="thumb" src="/${escapeHtml(item.img_url)}" alt=""/></td>
                <td>
                    <div class="cell-title">${escapeHtml(item.name)}</div>
                    <div class="cell-sub">${escapeHtml(item.category ? item.category.name : 'Uncategorised')} · reorder at ${item.low_stock_threshold}</div>
                </td>
                <td class="num">${stockTag(item.stock, item.low_stock_threshold)}</td>
            </tr>
        `).join('');
    }

    function paintTopProducts(items) {
        const body = document.getElementById('topProducts');

        if (!items.length) {
            body.innerHTML = emptyRow('bi-bar-chart', 'No sales recorded yet.');
            return;
        }

        body.innerHTML = items.map((item, index) => `
            <tr>
                <td style="width:34px" class="cell-sub">${index + 1}</td>
                <td>
                    <div class="cell-title">${escapeHtml(item.name)}</div>
                    <div class="cell-sub">${item.units} ${escapeHtml(item.unit)}${item.units == 1 ? '' : 's'} sold</div>
                </td>
                <td class="num">${CURRENCY} ${money(item.revenue)}</td>
            </tr>
        `).join('');
    }

    function paintRecent(items) {
        const body = document.getElementById('recentInvoices');

        if (!items.length) {
            body.innerHTML = emptyRow('bi-receipt', 'No invoices yet.');
            return;
        }

        body.innerHTML = items.map(item => `
            <tr>
                <td style="width:56px" class="cell-sub">#${item.id}</td>
                <td>
                    <div class="cell-title">${escapeHtml(item.customer ? item.customer.name : 'Walk-in')}</div>
                    <div class="cell-sub">${new Date(item.created_at).toLocaleString(undefined, { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })}</div>
                </td>
                <td class="num">${CURRENCY} ${money(item.payable)}</td>
            </tr>
        `).join('');
    }
</script>
