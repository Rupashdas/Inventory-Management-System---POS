<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The bug that started all of this.
     *
     * `password` was VARCHAR(50) and a bcrypt hash is 60 characters. On SQLite,
     * where the application was built, the length is not enforced and nothing
     * ever went wrong. On MySQL every registration failed.
     */
    public function test_a_bcrypt_hash_fits_in_the_password_column(): void
    {
        $response = $this->postJson('/user-registration', [
            'firstName' => 'Sarah',
            'lastName'  => 'Mitchell',
            'email'     => 'sarah@example.com',
            'mobile'    => '+1 415-555-0142',
            'password'  => 'correct horse battery staple',
        ]);

        $response->assertStatus(201)->assertJson(['status' => 'success']);

        $user = User::where('email', 'sarah@example.com')->firstOrFail();

        $this->assertSame(60, strlen($user->password));
        $this->assertTrue(Hash::check('correct horse battery staple', $user->password));
    }

    public function test_signing_in_with_the_right_password_returns_a_token_cookie(): void
    {
        $this->makeUser('owner@example.com');

        $response = $this->postJson('/user-login', [
            'email'    => 'owner@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()->assertJson(['status' => 'success']);
        $response->assertCookie('token');
    }

    public function test_signing_in_with_the_wrong_password_is_refused(): void
    {
        $this->makeUser('owner@example.com');

        $this->postJson('/user-login', [
            'email'    => 'owner@example.com',
            'password' => 'not-the-password',
        ])->assertStatus(401)->assertJson(['status' => 'failed']);
    }

    public function test_the_profile_endpoint_never_returns_the_password(): void
    {
        $user = $this->makeUser('owner@example.com');

        $response = $this->signedInAs($user)->getJson('/user-profile');

        $response->assertOk();
        $this->assertSame('', $response->json('data.password'));
        $this->assertStringNotContainsString('$2y$', $response->getContent());
    }
}
