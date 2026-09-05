<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Selling moves stock, and the ledger explains every move.
 */
class StockOnSaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_sale_takes_the_quantity_off_the_shelf(): void
    {
        ['user' => $user, 'product' => $product, 'customer' => $customer] = $this->makeShop(stock: 10);

        $response = $this->signedInAs($user)->postJson('/invoice-create', [
            'customer_id'      => $customer->id,
            'discount_percent' => 0,
            'products'         => [['product_id' => $product->id, 'qty' => 3]],
        ]);

        $response->assertStatus(201)->assertJson(['status' => 'success']);

        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_a_sale_cannot_take_more_than_is_in_stock(): void
    {
        ['user' => $user, 'product' => $product, 'customer' => $customer] = $this->makeShop(stock: 2);

        $response = $this->signedInAs($user)->postJson('/invoice-create', [
            'customer_id' => $customer->id,
            'products'    => [['product_id' => $product->id, 'qty' => 3]],
        ]);

        $response->assertStatus(422)->assertJson(['status' => 'failed']);

        // The message has to name the product and the number left; that is the
        // difference between an error a cashier can act on and one they cannot.
        $this->assertStringContainsString('Green Tea Bags', $response->json('message'));
        $this->assertStringContainsString('2 left', $response->json('message'));

        // And nothing was written: no invoice, no stock change.
        $this->assertSame(2, $product->fresh()->stock);
        $this->assertSame(0, Invoice::count());
    }

    /**
     * Two lines for the same product are one order for the sum of them. Checked
     * separately, each half passes and the shelf goes negative.
     */
    public function test_repeated_lines_for_one_product_are_totalled_before_the_stock_check(): void
    {
        ['user' => $user, 'product' => $product, 'customer' => $customer] = $this->makeShop(stock: 4);

        $response = $this->signedInAs($user)->postJson('/invoice-create', [
            'customer_id' => $customer->id,
            'products'    => [
                ['product_id' => $product->id, 'qty' => 3],
                ['product_id' => $product->id, 'qty' => 3],
            ],
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('6 requested', $response->json('message'));
        $this->assertSame(4, $product->fresh()->stock);
    }

    public function test_a_sale_writes_a_movement_recording_the_balance_afterwards(): void
    {
        ['user' => $user, 'product' => $product, 'customer' => $customer] = $this->makeShop(stock: 10);

        $this->signedInAs($user)->postJson('/invoice-create', [
            'customer_id' => $customer->id,
            'products'    => [['product_id' => $product->id, 'qty' => 4]],
        ])->assertStatus(201);

        $movement = StockMovement::where('product_id', $product->id)->latest('id')->first();

        $this->assertNotNull($movement);
        $this->assertSame('sale', $movement->reason);
        $this->assertSame(-4, $movement->quantity);
        $this->assertSame(6, $movement->balance_after);
    }

    public function test_restocking_adds_to_the_shelf_and_to_the_ledger(): void
    {
        ['user' => $user, 'product' => $product] = $this->makeShop(stock: 2);

        $this->signedInAs($user)->postJson('/restock-product', [
            'id'       => $product->id,
            'quantity' => 20,
            'note'     => 'Delivery',
        ])->assertOk()->assertJson(['status' => 'success', 'stock' => 22]);

        $this->assertSame(22, $product->fresh()->stock);

        $movement = StockMovement::where('product_id', $product->id)->latest('id')->first();
        $this->assertSame('purchase', $movement->reason);
        $this->assertSame(20, $movement->quantity);
        $this->assertSame(22, $movement->balance_after);
        $this->assertSame('Delivery', $movement->note);
    }
}
