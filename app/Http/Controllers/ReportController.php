<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller {

    function ReportPage() {
        return view('pages.dashboard.report-page');
    }

    /**
     * A sales report for a date range, as a PDF.
     *
     * Three things were wrong with the original and the first was the loudest:
     * the header printed `$request->FormDate` twice, so every report claimed to
     * cover a single day no matter what range was asked for. It also ran five
     * separate aggregate queries over the same rows, and it accepted whatever
     * came in as a date -- an unparseable string became 1 January 1970 and the
     * report came back empty with no explanation.
     */
    function SalesReport(Request $request) {

        $user_id = $request->header('userID');

        try {
            $from = Carbon::parse($request->FormDate)->startOfDay();
            $to   = Carbon::parse($request->ToDate)->endOfDay();
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Those dates could not be read.',
            ], 422);
        }

        if ($from->greaterThan($to)) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'The start date is after the end date.',
            ], 422);
        }

        $list = Invoice::where('user_id', $user_id)
            ->whereBetween('created_at', [$from, $to])
            ->with('customer')
            ->orderBy('created_at')
            ->get();

        // One pass over the rows already loaded, rather than four more trips to
        // the database for sums of the same set.
        $data = [
            'total'    => round($list->sum(fn ($i) => (float) $i->total), 2),
            'vat'      => round($list->sum(fn ($i) => (float) $i->vat), 2),
            'discount' => round($list->sum(fn ($i) => (float) $i->discount), 2),
            'payable'  => round($list->sum(fn ($i) => (float) $i->payable), 2),
            'list'     => $list,
            'orders'   => $list->count(),
            'FormDate' => $from->format('d M Y'),
            'ToDate'   => $to->format('d M Y'),
            'shopName' => optional(User::find($user_id))->firstName . ' ' . optional(User::find($user_id))->lastName,
            'printed'  => Carbon::now()->format('d M Y, H:i'),
        ];

        $pdf = Pdf::loadView('report.SalesReport', $data);

        return $pdf->download('sales-report-' . $from->format('Y-m-d') . '-to-' . $to->format('Y-m-d') . '.pdf');
    }

    /**
     * A stock valuation: what is on the shelf and what it is worth.
     *
     * The application could report what it had sold and not what it still held,
     * which is the question an inventory system exists to answer.
     */
    function StockReport(Request $request) {
        $user_id = $request->header('userID');

        $products = Product::where('user_id', $user_id)
            ->with('category:id,name')
            ->orderBy('name')
            ->get();

        $data = [
            'products'   => $products,
            'units'      => $products->sum('stock'),
            'value'      => round($products->sum(fn ($p) => $p->stock * (float) $p->price), 2),
            'lowCount'   => $products->filter(fn ($p) => $p->stock_state === 'low')->count(),
            'outCount'   => $products->filter(fn ($p) => $p->stock_state === 'out')->count(),
            'shopName'   => optional(User::find($user_id))->firstName . ' ' . optional(User::find($user_id))->lastName,
            'printed'    => Carbon::now()->format('d M Y, H:i'),
        ];

        $pdf = Pdf::loadView('report.StockReport', $data);

        return $pdf->download('stock-report-' . Carbon::now()->format('Y-m-d') . '.pdf');
    }
}
