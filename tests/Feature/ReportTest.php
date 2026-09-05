<?php

namespace Tests\Feature;

use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private function invoiceOn(array $shop, string $date, float $payable): Invoice
    {
        $invoice = Invoice::create([
            'total'       => (string) $payable,
            'discount'    => '0',
            'vat'         => '0',
            'payable'     => (string) $payable,
            'user_id'     => $shop['user']->id,
            'customer_id' => $shop['customer']->id,
        ]);

        $invoice->created_at = Carbon::parse($date);
        $invoice->save();

        return $invoice;
    }

    /**
     * The report used to pass `$request->FormDate` as both ends of the range,
     * so the header claimed every report covered a single day.
     */
    public function test_the_sales_report_covers_the_range_it_was_asked_for(): void
    {
        $shop = $this->makeShop();

        $this->invoiceOn($shop, '2026-03-01 10:00', 100);
        $this->invoiceOn($shop, '2026-03-15 10:00', 200);
        $this->invoiceOn($shop, '2026-04-02 10:00', 400);

        $response = $this->signedInAs($shop['user'])->get('/sales-report/2026-03-01/2026-03-31');

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));

        // The filename carries both ends, so it is the cheapest observable
        // proof that the second date was not the first one again.
        $this->assertStringContainsString('2026-03-01-to-2026-03-31', $response->headers->get('content-disposition'));
    }

    public function test_a_backwards_date_range_is_refused(): void
    {
        $shop = $this->makeShop();

        $this->signedInAs($shop['user'])
            ->getJson('/sales-report/2026-04-30/2026-04-01')
            ->assertStatus(422)
            ->assertJson(['status' => 'failed']);
    }

    public function test_the_dashboard_counts_low_and_out_of_stock_separately(): void
    {
        $shop = $this->makeShop(stock: 3);
        // threshold is 5, so 3 is low but sellable.

        $summary = $this->signedInAs($shop['user'])->getJson('/summary')->json();

        $this->assertSame(1, $summary['low_stock']);
        $this->assertSame(0, $summary['out_of_stock']);

        $shop['product']->update(['stock' => 0]);

        $summary = $this->signedInAs($shop['user'])->getJson('/summary')->json();

        // Out of stock is its own state, not a subset of low.
        $this->assertSame(0, $summary['low_stock']);
        $this->assertSame(1, $summary['out_of_stock']);
    }

    public function test_the_sales_trend_fills_in_days_with_no_trade(): void
    {
        $shop = $this->makeShop();

        $this->invoiceOn($shop, Carbon::today()->toDateTimeString(), 500);

        $trend = $this->signedInAs($shop['user'])->getJson('/sales-trend')->json();

        $this->assertCount(14, $trend['labels']);
        $this->assertCount(14, $trend['takings']);
        // A quiet day is a zero, not a missing point that closes the gap up.
        $this->assertSame(0.0, (float) $trend['takings'][0]);
        $this->assertSame(500.0, (float) end($trend['takings']));
    }

    public function test_the_stock_report_is_a_pdf(): void
    {
        $shop = $this->makeShop();

        $response = $this->signedInAs($shop['user'])->get('/stock-report');

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }
}
