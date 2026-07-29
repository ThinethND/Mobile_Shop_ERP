<?php

namespace App\Http\Controllers;

use App\Models\CustomerGalleryImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use RuntimeException;
use Throwable;

class CustomerGalleryController extends Controller
{
    public function index()
    {
        if (!Schema::hasTable('customer_gallery_images')) {
            return Inertia::render('OtherCMS/OurCustomers/index', [
                'images' => [],
            ]);
        }

        return Inertia::render('OtherCMS/OurCustomers/index', [
            'images' => $this->galleryImages()
                ->map(fn (CustomerGalleryImage $image) => $this->imagePayload($image))
                ->values(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'customer_images' => ['required', 'array', 'min:1', 'max:6'],
            'customer_images.*' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $storedImages = [];

        try {
            foreach ($request->file('customer_images', []) as $index => $file) {
                $storedImages[] = [
                    'image_path' => $this->storeWatermarkedCustomerImage($file),
                    'sort_order' => $index + 1,
                ];
            }
        } catch (Throwable $throwable) {
            foreach ($storedImages as $storedImage) {
                if (!empty($storedImage['image_path']) && Storage::disk('public')->exists($storedImage['image_path'])) {
                    Storage::disk('public')->delete($storedImage['image_path']);
                }
            }

            report($throwable);

            throw ValidationException::withMessages([
                'customer_images' => 'Unable to process one or more images. Please try again with clear JPG, PNG, or WEBP files.',
            ]);
        }

        $existingImages = CustomerGalleryImage::query()->get();

        foreach ($existingImages as $image) {
            if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
        }

        CustomerGalleryImage::query()->delete();

        foreach ($storedImages as $storedImage) {
            CustomerGalleryImage::create([
                'image_path' => $storedImage['image_path'],
                'sort_order' => $storedImage['sort_order'],
                'is_active' => true,
            ]);
        }

        return redirect()
            ->route('customer-gallery.index')
            ->with('success', count($validated['customer_images']) . ' customer photos saved.');
    }

    public function publicImages(): JsonResponse
    {
        if (!Schema::hasTable('customer_gallery_images')) {
            return response()
                ->json(['images' => []])
                ->header('Cache-Control', 'no-store');
        }

        return response()
            ->json([
                'images' => $this->galleryImages()
                    ->map(fn (CustomerGalleryImage $image) => $this->imagePayload($image))
                    ->values(),
            ])
            ->header('Cache-Control', 'no-store');
    }

    public function image(CustomerGalleryImage $customerGalleryImage)
    {
        abort_unless($customerGalleryImage->is_active, 404);
        abort_unless($customerGalleryImage->image_path, 404);
        abort_unless(Storage::disk('public')->exists($customerGalleryImage->image_path), 404);

        $path = Storage::disk('public')->path($customerGalleryImage->image_path);
        $mimeType = Storage::disk('public')->mimeType($customerGalleryImage->image_path) ?: 'image/jpeg';

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    protected function galleryImages()
    {
        return CustomerGalleryImage::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->take(6)
            ->get();
    }

    protected function imagePayload(CustomerGalleryImage $image): array
    {
        return [
            'id' => $image->id,
            'image_url' => route('frontend.home.customer-gallery.image', [
                'customerGalleryImage' => $image->id,
            ]),
            'sort_order' => $image->sort_order,
        ];
    }

    protected function storeWatermarkedCustomerImage($file): string
    {
        $sourcePath = $file->getRealPath();

        if (!is_string($sourcePath) || $sourcePath === '') {
            throw new RuntimeException('Unable to access uploaded image.');
        }

        $inputMimeType = $file->getMimeType() ?: 'image/jpeg';
        $outputFormat = $this->normalizeOutputFormat($inputMimeType);
        $image = $this->createImageResource($sourcePath, $inputMimeType);

        if (! $image) {
            throw new RuntimeException('Unsupported image type.');
        }

        try {
            $this->applyCustomerGalleryWatermark($image);

            $relativePath = 'customer-gallery/' . Str::uuid() . '.' . $this->extensionForOutputFormat($outputFormat);
            Storage::disk('public')->put($relativePath, $this->encodeImageResource($image, $outputFormat));

            return $relativePath;
        } finally {
            imagedestroy($image);
        }
    }

    protected function normalizeOutputFormat(string $mimeType): string
    {
        return match (mb_strtolower($mimeType)) {
            'image/png' => 'png',
            'image/webp' => function_exists('imagewebp') ? 'webp' : 'jpeg',
            default => 'jpeg',
        };
    }

    protected function extensionForOutputFormat(string $outputFormat): string
    {
        return match ($outputFormat) {
            'png' => 'png',
            'webp' => 'webp',
            default => 'jpg',
        };
    }

    protected function createImageResource(string $sourcePath, string $mimeType)
    {
        return match (mb_strtolower($mimeType)) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($sourcePath) : null,
            default => null,
        };
    }

    protected function encodeImageResource($image, string $outputFormat): string
    {
        ob_start();

        $encoded = match ($outputFormat) {
            'png' => imagepng($image, null, 6),
            'webp' => imagewebp($image, null, 90),
            default => imagejpeg($image, null, 90),
        };

        $binary = ob_get_clean();

        if (! $encoded || ! is_string($binary) || $binary === '') {
            throw new RuntimeException('Unable to save processed image.');
        }

        return $binary;
    }

    protected function applyCustomerGalleryWatermark($image): void
    {
        $watermarkPath = public_path('images/bluelogodardeze.png');

        if (!is_file($watermarkPath)) {
            return;
        }

        $watermark = imagecreatefrompng($watermarkPath);

        if (! $watermark) {
            return;
        }

        try {
            $preparedWatermark = $this->prepareWatermarkOverlay(
                $watermark,
                imagesx($image),
                imagesy($image)
            );

            if (! $preparedWatermark) {
                return;
            }

            try {
                imagealphablending($image, true);
                imagesavealpha($image, true);

                $x = (int) round((imagesx($image) - imagesx($preparedWatermark)) / 2);
                $y = (int) round((imagesy($image) - imagesy($preparedWatermark)) / 2);

                imagecopy(
                    $image,
                    $preparedWatermark,
                    max(0, $x),
                    max(0, $y),
                    0,
                    0,
                    imagesx($preparedWatermark),
                    imagesy($preparedWatermark)
                );
            } finally {
                imagedestroy($preparedWatermark);
            }
        } finally {
            imagedestroy($watermark);
        }
    }

    protected function prepareWatermarkOverlay($watermark, int $imageWidth, int $imageHeight)
    {
        $watermarkWidth = imagesx($watermark);
        $watermarkHeight = imagesy($watermark);

        if ($watermarkWidth < 1 || $watermarkHeight < 1 || $imageWidth < 1 || $imageHeight < 1) {
            return null;
        }

        $scale = min(
            ($imageWidth * 0.52) / $watermarkWidth,
            ($imageHeight * 0.16) / $watermarkHeight,
            1
        );

        $targetWidth = max(1, (int) round($watermarkWidth * $scale));
        $targetHeight = max(1, (int) round($watermarkHeight * $scale));

        $resizedWatermark = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($resizedWatermark, false);
        imagesavealpha($resizedWatermark, true);
        imagefill($resizedWatermark, 0, 0, imagecolorallocatealpha($resizedWatermark, 255, 255, 255, 127));

        imagecopyresampled(
            $resizedWatermark,
            $watermark,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $watermarkWidth,
            $watermarkHeight
        );

        $overlay = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($overlay, false);
        imagesavealpha($overlay, true);
        imagefill($overlay, 0, 0, imagecolorallocatealpha($overlay, 255, 255, 255, 127));

        $globalOpacity = 0.22;

        for ($y = 0; $y < $targetHeight; $y++) {
            for ($x = 0; $x < $targetWidth; $x++) {
                $rgba = imagecolorat($resizedWatermark, $x, $y);

                $alpha = ($rgba >> 24) & 0x7F;
                $red = ($rgba >> 16) & 0xFF;
                $green = ($rgba >> 8) & 0xFF;
                $blue = $rgba & 0xFF;

                $existingOpacity = 1 - ($alpha / 127);
                $darkness = 1 - (($red + $green + $blue) / 765);
                $visibleStrength = $darkness * $existingOpacity * $globalOpacity;

                if ($visibleStrength <= 0.01) {
                    continue;
                }

                $targetAlpha = 127 - (int) round(127 * min(1, $visibleStrength));
                $trueColor = ($targetAlpha << 24) | ($red << 16) | ($green << 8) | $blue;

                imagesetpixel($overlay, $x, $y, $trueColor);
            }
        }

        imagedestroy($resizedWatermark);

        return $overlay;
    }
}
