<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder {
    public function run(): void {
        $users = User::query()->orderBy('id')->get();
        $customers = Customer::query()->orderBy('id')->get();
        $products = Product::query()->orderBy('id')->get();
        $sales = [
            [['product' => 'Basmati Rice 5 kg', 'qty' => 2], ['product' => 'Soybean Oil 2 litre', 'qty' => 1], ['product' => 'Farm Eggs 12 pieces', 'qty' => 1]],
            [['product' => 'A4 Copy Paper 500 sheets', 'qty' => 3], ['product' => 'Hand Wash 250 ml', 'qty' => 4]],
            [['product' => 'Full Cream Milk 1 litre', 'qty' => 6], ['product' => 'Chocolate Biscuits snack pack', 'qty' => 8], ['product' => 'Mineral Water 1.5 litre', 'qty' => 12]],
            [['product' => 'Laundry Detergent 2 kg', 'qty' => 2], ['product' => 'Dishwashing Liquid 500 ml', 'qty' => 3], ['product' => 'Toothpaste 150 g', 'qty' => 2]],
            [['product' => 'Red Lentils 1 kg', 'qty' => 4], ['product' => 'Potatoes 2 kg', 'qty' => 3], ['product' => 'Fresh Bananas', 'qty' => 2]],
            [['product' => 'Instant Coffee 100 g', 'qty' => 2], ['product' => 'Roasted Peanuts 200 g', 'qty' => 5]],
        ];

        foreach ($sales as $index => $sale) {
            $user = $users[$index % $users->count()];
            $customer = $customers[$index % $customers->count()];
            $lineItems = [];
            $total = 0;

            foreach ($sale as $item) {
                $product = $products->firstWhere('name', $item['product']);
                $salePrice = (float) $product->price;
                $total += $salePrice * $item['qty'];
                $lineItems[] = [$product, $item['qty'], $salePrice];
            }

            $discount = round($total >= 1000 ? $total * 0.05 : 0, 2);
            $vat = round(($total - $discount) * 0.05, 2);
            $payable = round($total - $discount + $vat, 2);
            $invoice = Invoice::create([
                'total' => number_format($total, 2, '.', ''),
                'discount' => number_format($discount, 2, '.', ''),
                'vat' => number_format($vat, 2, '.', ''),
                'payable' => number_format($payable, 2, '.', ''),
                'user_id' => $user->id,
                'customer_id' => $customer->id,
            ]);

            foreach ($lineItems as [$product, $quantity, $salePrice]) {
                InvoiceProduct::create([
                    'invoice_id' => $invoice->id,
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'qty' => $quantity,
                    'sale_price' => number_format($salePrice, 2, '.', ''),
                ]);
            }
        }
    }
}