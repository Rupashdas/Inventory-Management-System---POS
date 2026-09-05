<?php

namespace Tests\Feature;

use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Every row belongs to one shop, and one shop's requests cannot touch another's.
 *
 * The invoice delete was the hole: the child rows were scoped to the owner and
 * the parent was not, so `inv_id` alone was enough to delete anybody's sale.
 */
class OwnershipTest extends TestCase
{
    use RefreshDatabase;

    private function sellSomething(array $shop): Invoice
    {
        $this->signedInAs($shop['user'])->postJson('/invoice-create', [
            'customer_id' => $shop['customer']->id,
            'products'    => [['product_id' => $shop['product']->id, 'qty' => 2]],
        ])->assertStatus(201);

        return Invoice::where('user_id', $shop['user']->id)->firstOrFail();
    }

    public function test_one_shop_cannot_delete_another_shops_invoice(): void
    {
        $owner = $this->makeShop('owner@example.com');
        $stranger = $this->makeShop('stranger@example.com');

        $invoice = $this->sellSomething($owner);

        $this->signedInAs($stranger['user'])
            ->postJson('/invoice-delete', ['inv_id' => $invoice->id])
            ->assertStatus(404);

        $this->assertNotNull($invoice->fresh(), 'the invoice should still exist');
    }

    public function test_deleting_an_invoice_puts_its_stock_back(): void
    {
        $shop = $this->makeShop(stock: 10);

        $invoice = $this->sellSomething($shop);
        $this->assertSame(8, $shop['product']->fresh()->stock);

        $this->signedInAs($shop['user'])
            ->postJson('/invoice-delete', ['inv_id' => $invoice->id])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertSame(10, $shop['product']->fresh()->stock);
        $this->assertNull($invoice->fresh());
    }

    public function test_one_shop_cannot_read_another_shops_product_list(): void
    {
        $owner = $this->makeShop('owner@example.com');
        $stranger = $this->makeShop('stranger@example.com');

        $names = collect($this->signedInAs($stranger['user'])->getJson('/list-product')->json())
            ->pluck('id');

        $this->assertFalse($names->contains($owner['product']->id));
    }

    public function test_a_request_without_a_token_is_sent_to_the_login_page(): void
    {
        $this->get('/dashboard')->assertRedirect('/userLogin');
    }
}
