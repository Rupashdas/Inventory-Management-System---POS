<?php

namespace Tests\Feature;

use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * What the invoice costs is decided by the server.
 *
 * The original endpoint read `total`, `vat` and `payable` straight out of the
 * request body, so anything that could POST could name its own price.
 */
class InvoicePricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_totals_are_computed_from_the_catalogue_not_the_request(): void
    {
        ['user' => $user, 'product' => $product, 'customer' => $customer] = $this->makeShop(stock: 10);

        // 4.00 each. The request claims otherwise in every field it can.
        $this->signedInAs($user)->postJson('/invoice-create', [
            'customer_id'      => $customer->id,
            'discount_percent' => 0,
            'products'         => [['product_id' => $product->id, 'qty' => 2]],
            'total'            => '1',
            'vat'              => '0',
            'discount'         => '0',
            'payable'          => '1',
        ])->assertStatus(201);

        $invoice = Invoice::first();

        $this->assertSame(8.0, (float) $invoice->total);
        $this->assertSame(0.4, (float) $invoice->vat);
        $this->assertSame(8.4, (float) $invoice->payable);
    }

    public function test_the_discount_percentage_is_applied_before_vat(): void
    {
        ['user' => $user, 'product' => $product, 'customer' => $customer] = $this->makeShop(stock: 10);

        $this->signedInAs($user)->postJson('/invoice-create', [
            'customer_id'      => $customer->id,
            'discount_percent' => 10,
            'products'         => [['product_id' => $product->id, 'qty' => 2]],
        ])->assertStatus(201);

        $invoice = Invoice::first();

        // 8.00 subtotal, 0.80 off, 7.20 net, 0.36 tax, 7.56 payable.
        $this->assertSame(0.8, (float) $invoice->discount);
        $this->assertSame(7.2, (float) $invoice->total);
        $this->assertSame(0.36, (float) $invoice->vat);
        $this->assertSame(7.56, (float) $invoice->payable);
    }

    public function test_a_discount_over_one_hundred_percent_is_rejected(): void
    {
        ['user' => $user, 'product' => $product, 'customer' => $customer] = $this->makeShop(stock: 10);

        $this->signedInAs($user)->postJson('/invoice-create', [
            'customer_id'      => $customer->id,
            'discount_percent' => 150,
            'products'         => [['product_id' => $product->id, 'qty' => 1]],
        ])->assertStatus(422);

        $this->assertSame(0, Invoice::count());
    }

    public function test_an_invoice_cannot_be_addressed_to_another_shops_customer(): void
    {
        ['user' => $user, 'product' => $product] = $this->makeShop('one@example.com');
        ['customer' => $strangersCustomer] = $this->makeShop('two@example.com');

        $this->signedInAs($user)->postJson('/invoice-create', [
            'customer_id' => $strangersCustomer->id,
            'products'    => [['product_id' => $product->id, 'qty' => 1]],
        ])->assertStatus(422)->assertJson(['status' => 'failed']);

        $this->assertSame(0, Invoice::count());
    }

    public function test_a_product_from_another_shop_cannot_be_sold(): void
    {
        ['user' => $user, 'customer' => $customer] = $this->makeShop('one@example.com');
        ['product' => $strangersProduct] = $this->makeShop('two@example.com');

        $this->signedInAs($user)->postJson('/invoice-create', [
            'customer_id' => $customer->id,
            'products'    => [['product_id' => $strangersProduct->id, 'qty' => 1]],
        ])->assertStatus(422);

        $this->assertSame(10, $strangersProduct->fresh()->stock);
    }
}
