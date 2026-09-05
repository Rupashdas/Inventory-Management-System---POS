<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller {

    public function dashboardPage() {
        return view('pages.dashboard.dashboard-page');
    }

    /**
     * The counters across the top of the dashboard.
     *
     * Two of these are new and they are the two a shopkeeper actually opens the
     * page for: how much came in today, and what is about to run out.
     */
    function summary(Request $request): array {

        $user_id = $request->header('userID');

        $todayPayable = Invoice::where('user_id', $user_id)
            ->whereDate('created_at', Carbon::today())
            ->sum('payable');

        return [
            'product'     => Product::where('user_id', $user_id)->count(),
            'category'    => Category::where('user_id', $user_id)->count(),
            'customer'    => Customer::where('user_id', $user_id)->count(),
            'invoice'     => Invoice::where('user_id', $user_id)->count(),
            'total'       => round(Invoice::where('user_id', $user_id)->sum('total'), 2),
            'vat'         => round(Invoice::where('user_id', $user_id)->sum('vat'), 2),
            'payable'     => round(Invoice::where('user_id', $user_id)->sum('payable'), 2),
            'today_sales' => round($todayPayable, 2),
            // whereColumn, not a fixed number: "low" is per product, so the
            // comparison has to be between the two columns.
            'low_stock'   => Product::where('user_id', $user_id)
                ->whereColumn('stock', '<=', 'low_stock_threshold')
                ->where('stock', '>', 0)
                ->count(),
            'out_of_stock' => Product::where('user_id', $user_id)->where('stock', '<=', 0)->count(),
        ];
    }

    /**
     * Takings per day for the last fourteen days.
     *
     * Days with no sales are filled in as zero here rather than left out, so
     * the chart shows a quiet Tuesday as a gap in trade instead of silently
     * closing it up and implying business was steady.
     */
    function salesTrend(Request $request): array {
        $user_id = $request->header('userID');
        $days = 14;

        $rows = Invoice::where('user_id', $user_id)
            ->where('created_at', '>=', Carbon::today()->subDays($days - 1))
            ->select(
                DB::raw('DATE(created_at) as day'),
                DB::raw('SUM(payable) as takings'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('day')
            ->pluck('takings', 'day');

        $orders = Invoice::where('user_id', $user_id)
            ->where('created_at', '>=', Carbon::today()->subDays($days - 1))
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as orders'))
            ->groupBy('day')
            ->pluck('orders', 'day');

        $labels = [];
        $takings = [];
        $orderCounts = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $key = $date->toDateString();

            $labels[] = $date->format('d M');
            $takings[] = round((float) ($rows[$key] ?? 0), 2);
            $orderCounts[] = (int) ($orders[$key] ?? 0);
        }

        return [
            'labels'  => $labels,
            'takings' => $takings,
            'orders'  => $orderCounts,
        ];
    }

    /**
     * The products at or below their own threshold, worst first.
     */
    function lowStock(Request $request) {
        $user_id = $request->header('userID');

        return Product::where('user_id', $user_id)
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->with('category:id,name')
            ->orderBy('stock')
            ->limit(10)
            ->get(['id', 'name', 'stock', 'low_stock_threshold', 'unit', 'category_id', 'img_url']);
    }

    /**
     * The five most recent invoices, for the activity panel.
     */
    function recentInvoices(Request $request) {
        $user_id = $request->header('userID');

        return Invoice::where('user_id', $user_id)
            ->with('customer:id,name')
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'payable', 'customer_id', 'created_at']);
    }

    /**
     * The products that actually sell, by units moved.
     */
    function topProducts(Request $request) {
        $user_id = $request->header('userID');

        return DB::table('invoice_products')
            ->join('products', 'products.id', '=', 'invoice_products.product_id')
            ->where('invoice_products.user_id', $user_id)
            ->select(
                'products.name',
                'products.unit',
                DB::raw('SUM(CAST(invoice_products.qty AS UNSIGNED)) as units'),
                DB::raw('SUM(CAST(invoice_products.sale_price AS DECIMAL(12,2))) as revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.unit')
            ->orderByDesc('units')
            ->limit(5)
            ->get();
    }
}
