<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Encoders\GifEncoder;

/**
 * Pream's note for the team:
 * Single chokepoint for every public-facing image upload. We re-encode through
 * Intervention/Image which strips EXIF (incl. GPS), normalizes orientation
 * (rotates per the EXIF orientation flag, then drops it), and writes a
 * predictable filename to the `public` disk.
 *
 * Use storeStripped() instead of $file->store('...', 'public') anywhere a user
 * uploads a photo (destinations, timetables, future logos, etc.).
 */
class ImageUploadService
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new GdDriver());
    }

    /**
     * Re-encode the upload to strip EXIF and write under $directory on the
     * `public` disk. Returns the stored path relative to the disk root,
     * suitable for prepending "storage/" for use with asset().
     *
     * Also writes a `.webp` companion next to the original so the public
     * `<picture>` tags get a smaller payload on modern browsers — the public
     * Blade templates already check `file_exists(public_path($base.'.webp'))`
     * so the optimized version is picked up automatically.
     */
    public function storeStripped(UploadedFile $file, string $directory): string
    {
        $img = $this->manager->decode($file->getRealPath());

        // Honor EXIF orientation, then drop the metadata.
        $img->orient();

        // Pick output format & extension from the source mime
        [$ext, $encoder] = match (strtolower($file->getClientMimeType())) {
            'image/png'  => ['png',  fn ($i) => $i->encode(new PngEncoder())],
            'image/webp' => ['webp', fn ($i) => $i->encode(new WebpEncoder(quality: 82))],
            'image/gif'  => ['gif',  fn ($i) => $i->encode(new GifEncoder())],
            default      => ['jpg',  fn ($i) => $i->encode(new JpegEncoder(quality: 85))],
        };

        $base = trim($directory, '/').'/'.Str::random(40);
        $relPath = $base.'.'.$ext;
        Storage::disk('public')->put($relPath, (string) $encoder($img));

        // WebP companion (skipped if the source itself is already WebP).
        if ($ext !== 'webp') {
            try {
                Storage::disk('public')->put($base.'.webp', (string) $img->encode(new WebpEncoder(quality: 80)));
            } catch (\Throwable $e) {
                // Don't fail the upload if WebP encoding hiccups — the JPG/PNG
                // is the source of truth, the WebP is opportunistic.
                \Log::warning('WebP companion encoding failed', [
                    'path' => $relPath,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $relPath;
    }
}
