<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Deleting a product deletes its own image and nothing else.
 *
 * The original read `file_path` out of the POST body and handed it to
 * File::delete(), so the request chose which file on the server disappeared.
 */
class ProductFileSafetyTest extends TestCase
{
    use RefreshDatabase;

    private string $bystander;

    protected function setUp(): void
    {
        parent::setUp();

        // A file outside the uploads directory that nothing should ever touch.
        $this->bystander = storage_path('app/important.txt');
        @mkdir(dirname($this->bystander), 0755, true);
        file_put_contents($this->bystander, 'do not delete me');
    }

    protected function tearDown(): void
    {
        @unlink($this->bystander);
        parent::tearDown();
    }

    public function test_a_supplied_path_cannot_make_the_server_delete_another_file(): void
    {
        ['user' => $user, 'product' => $product] = $this->makeShop();

        $this->signedInAs($user)->postJson('/delete-product', [
            'id'        => $product->id,
            // The parameter the old endpoint trusted.
            'file_path' => '../storage/app/important.txt',
        ])->assertOk();

        $this->assertFileExists($this->bystander);
        $this->assertNull($product->fresh());
    }

    public function test_a_product_that_appears_on_an_invoice_cannot_be_deleted(): void
    {
        ['user' => $user, 'product' => $product, 'customer' => $customer] = $this->makeShop(stock: 5);

        $this->signedInAs($user)->postJson('/invoice-create', [
            'customer_id' => $customer->id,
            'products'    => [['product_id' => $product->id, 'qty' => 1]],
        ])->assertStatus(201);

        $this->signedInAs($user)->postJson('/delete-product', ['id' => $product->id])
            ->assertStatus(409)
            ->assertJson(['status' => 'failed']);

        $this->assertNotNull($product->fresh());
    }

    public function test_an_uploaded_image_is_stored_under_a_generated_name(): void
    {
        ['user' => $user, 'category' => $category] = $this->makeShop();

        $response = $this->signedInAs($user)->post('/create-product', [
            'name'        => 'Gel Pen, 0.5mm',
            'price'       => '35',
            'unit'        => 'piece',
            'category_id' => $category->id,
            'stock'       => 12,
            // A hostile original filename. It must not survive into the path.
            'img'         => $this->fakeImage('../../evil.png'),
        ]);

        $response->assertStatus(201);

        $product = Product::where('name', 'Gel Pen, 0.5mm')->firstOrFail();

        $this->assertStringStartsWith('uploads/', $product->img_url);
        $this->assertStringNotContainsString('..', $product->img_url);
        $this->assertStringNotContainsString('evil', $product->img_url);

        @unlink(public_path($product->img_url));
    }

    public function test_opening_stock_is_recorded_as_a_movement(): void
    {
        ['user' => $user, 'category' => $category] = $this->makeShop();

        $this->signedInAs($user)->post('/create-product', [
            'name'        => 'Kraft Envelope, A4',
            'price'       => '12',
            'unit'        => 'piece',
            'category_id' => $category->id,
            'stock'       => 50,
            'img'         => $this->fakeImage('envelope.png'),
        ])->assertStatus(201);

        $product = Product::where('name', 'Kraft Envelope, A4')->firstOrFail();

        $this->assertSame(50, $product->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id'    => $product->id,
            'reason'        => 'purchase',
            'quantity'      => 50,
            'balance_after' => 50,
        ]);

        @unlink(public_path($product->img_url));
    }
}
