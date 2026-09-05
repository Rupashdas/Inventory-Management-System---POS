@extends('layout.sidenav-layout')
@section('title', 'Reports')
@section('content')

    <div class="page">

        <div class="page-head">
            <div>
                <h1>Reports</h1>
                <p class="sub">Downloadable PDFs of what sold and what is still on the shelf.</p>
            </div>
        </div>

        <div class="grid-3">

            <div class="panel">
                <div class="panel-head">
                    <h2>Sales report</h2>
                    <span class="hint">PDF</span>
                </div>
                <div class="panel-body">

                    <p class="mb-3" style="color:var(--ink-soft);font-size:13px">
                        Every invoice between two dates, with totals, discount and sales tax.
                    </p>

                    <label class="form-label">From</label>
                    <input id="FormDate" type="date" class="form-control"/>

                    <label class="form-label mt-3">To</label>
                    <input id="ToDate" type="date" class="form-control"/>

                    {{-- Typing two dates for "this month" is the same three
                         clicks every time; these do it. --}}
                    <div class="d-flex gap-2 mt-3 flex-wrap">
                        <button onclick="setRange(7)" class="btn btn-quiet btn-mini">Last 7 days</button>
                        <button onclick="setRange(30)" class="btn btn-quiet btn-mini">Last 30 days</button>
                        <button onclick="setThisMonth()" class="btn btn-quiet btn-mini">This month</button>
                    </div>

                    <button onclick="SalesReport()" class="btn btn-accent w-100 mt-3">
                        <i class="bi bi-download me-1"></i> Download sales report
                    </button>

                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <h2>Stock on hand</h2>
                    <span class="hint">PDF</span>
                </div>
                <div class="panel-body">

                    <p class="mb-3" style="color:var(--ink-soft);font-size:13px">
                        The whole catalogue with quantities, retail value and which
                        lines are low or out. No date range &mdash; it is a snapshot
                        of right now.
                    </p>

                    <a href="{{ url('/stock-report') }}" class="btn btn-accent w-100">
                        <i class="bi bi-download me-1"></i> Download stock report
                    </a>

                </div>
            </div>

        </div>
    </div>

    {{--
        This script used to sit after @endsection. Blade throws away anything a
        child template puts outside a section, so it never reached the page and
        the Download button called a function that did not exist.
    --}}
    <script>
        function asInputDate(date) {
            // Built from the local date parts rather than toISOString(), which
            // converts to UTC first and hands back yesterday for anyone east of
            // Greenwich -- here, every evening after 6pm.
            const pad = (n) => String(n).padStart(2, '0');
            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
        }

        function setRange(days) {
            const to = new Date();
            const from = new Date();
            from.setDate(from.getDate() - (days - 1));
            document.getElementById('FormDate').value = asInputDate(from);
            document.getElementById('ToDate').value = asInputDate(to);
        }

        function setThisMonth() {
            const now = new Date();
            document.getElementById('FormDate').value = asInputDate(new Date(now.getFullYear(), now.getMonth(), 1));
            document.getElementById('ToDate').value = asInputDate(now);
        }

        function SalesReport() {
            const from = document.getElementById('FormDate').value;
            const to = document.getElementById('ToDate').value;

            if (!from || !to) {
                return errorToast("Pick both dates first.");
            }
            if (from > to) {
                return errorToast("The start date is after the end date.");
            }

            window.open('/sales-report/' + from + '/' + to);
        }

        setRange(30);
    </script>

@endsection
