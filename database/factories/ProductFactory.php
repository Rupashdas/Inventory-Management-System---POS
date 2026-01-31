<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        // Ensure uploads directory exists
        $uploadsDir = public_path('uploads');
        if (!file_exists($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        // Prepare unique seed and dimensions
        $seed = uniqid();
        $width = 400;
        $height = 400;

        // Providers that return random images; include the seed to avoid returning the same image
        $providers = [
            // picsum accepts seed in path
            function ($seed, $w, $h) {return "https://picsum.photos/seed/{$seed}/{$w}/{$h}";},
            // loremflickr supports random query
            function ($seed, $w, $h) {return "https://loremflickr.com/{$w}/{$h}/product?random={$seed}";},
            // Unsplash Source with signature (may be rate-limited)
            function ($seed, $w, $h) {return "https://source.unsplash.com/{$w}x{$h}/?sig={$seed}";},
            // placeimg with a cache-busting param
            function ($seed, $w, $h) {return "https://placeimg.com/{$w}/{$h}/any?random={$seed}";},
        ];

        // Try providers in random order until we get a valid image
        shuffle($providers);
        $imageSaved = false;
        $filename = $seed . '.jpg';
        $img_url = 'uploads/' . $filename;

        foreach ($providers as $provider) {
            $imageSrc = $provider($seed, $width, $height);

            // Try to read headers to determine content type/extension
            $headers = @get_headers($imageSrc, 1);
            $ext = 'jpg';
            if ($headers && isset($headers['Content-Type'])) {
                $ct = is_array($headers['Content-Type']) ? end($headers['Content-Type']) : $headers['Content-Type'];
                if (stripos($ct, 'png') !== false) {
                    $ext = 'png';
                } elseif (stripos($ct, 'jpeg') !== false || stripos($ct, 'jpg') !== false) {
                    $ext = 'jpg';
                } elseif (stripos($ct, 'gif') !== false) {
                    $ext = 'gif';
                }

            }

            $filename = $seed . '.' . $ext;
            $img_url = 'uploads/' . $filename;

            $contents = @file_get_contents($imageSrc);
            if ($contents) {
                file_put_contents($uploadsDir . DIRECTORY_SEPARATOR . $filename, $contents);
                $imageSaved = true;
                break;
            }
        }

        if (!$imageSaved) {
            // Fallback: use GD if available to create a simple image, otherwise write a tiny PNG placeholder
            if (function_exists('imagecreatetruecolor')) {
                $im = imagecreatetruecolor($width, $height);
                $bg = imagecolorallocate($im, 200, 200, 200);
                imagefilledrectangle($im, 0, 0, $width - 1, $height - 1, $bg);
                $filename = $seed . '.jpg';
                $img_url = 'uploads/' . $filename;
                imagejpeg($im, $uploadsDir . DIRECTORY_SEPARATOR . $filename, 75);
                unset($im);
            } else {
                $placeholderPng = base64_decode(
                    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO8YhQkAAAAASUVORK5CYII='
                );
                $filename = $seed . '.png';
                $img_url = 'uploads/' . $filename;
                file_put_contents($uploadsDir . DIRECTORY_SEPARATOR . $filename, $placeholderPng);
            }
        }
        $random_user_id = User::inRandomOrder()->first()->id;
        $category = Category::where('user_id', $random_user_id)->inRandomOrder()->first();

        if (!$category) {
            $category = Category::factory()->create(['user_id' => $random_user_id]);
        }
        return [
            'user_id'     => $random_user_id,
            'category_id' => $category->id,
            'name'        => $this->faker->word(),
            'price'       => $this->faker->randomFloat(2, 10, 500),
            'unit'        => $this->faker->randomElement(['kg', 'pcs', 'litre', 'pack']),
            'img_url'     => $img_url,
        ];
    }
}
