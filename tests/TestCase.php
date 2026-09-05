<?php

namespace Tests;

use App\Helper\JWTToken;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Sign a request as this user.
     *
     * The application does not use Laravel's auth guard: TokenVerificationMiddleware
     * reads a JWT out of a cookie and puts the user's id into a request header.
     * So `actingAs()` would prove nothing -- the request has to carry the same
     * cookie a browser would, through the same EncryptCookies middleware.
     *
     * The raw JWT goes in: Laravel's own test helper applies the cookie value
     * prefix and encrypts it on the way into the request, so encrypting it here
     * too would hand EncryptCookies a doubly-wrapped value it cannot read.
     *
     * withCredentials() is what makes postJson/getJson send cookies at all --
     * without it they deliberately send none, and every authenticated JSON test
     * lands on the login redirect instead of the endpoint.
     */
    protected function signedInAs(User $user): static
    {
        return $this->withCredentials()
            ->withCookie('token', JWTToken::createToken($user->email, $user->id));
    }

    /**
     * A genuinely valid 1x1 PNG on disk, wrapped as an upload.
     *
     * Not UploadedFile::fake()->image(), which draws the placeholder with GD --
     * an extension the suite should not require just to check that a filename
     * is sanitised. getimagesize(), which the `image` rule uses, is core.
     */
    protected function fakeImage(string $name): UploadedFile
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        );

        $path = tempnam(sys_get_temp_dir(), 'pos') . '.png';
        file_put_contents($path, $png);

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    protected function makeUser(string $email = 'shop@example.com'): User
    {
        return User::create([
            'firstName' => 'Test',
            'lastName'  => 'Owner',
            'email'     => $email,
            'mobile'    => '+1 415-555-' . random_int(1000, 9999),
            'password'  => Hash::make('password'),
            'otp'       => '0',
        ]);
    }

    /**
     * A user with one category, one customer and one product in stock -- the
     * smallest arrangement in which a sale can be rung up.
     */
    protected function makeShop(string $email = 'shop@example.com', int $stock = 10): array
    {
        $user = $this->makeUser($email);

        $category = Category::create(['name' => 'Beverages', 'user_id' => $user->id]);

        $product = Product::create([
            'user_id'             => $user->id,
            'category_id'         => $category->id,
            'name'                => 'Green Tea Bags, 40 ct',
            'price'               => '4.00',
            'unit'                => 'box',
            'stock'               => $stock,
            'low_stock_threshold' => 5,
            'img_url'             => 'uploads/tea.jpg',
        ]);

        $customer = Customer::create([
            'name'    => 'Sarah Mitchell',
            'email'   => 'sarah+' . $user->id . '@example.com',
            'mobile'  => '+1 415-555-0142',
            'user_id' => $user->id,
        ]);

        return compact('user', 'category', 'product', 'customer');
    }
}
