<?php

namespace App\Services;

use App\Services\Concerns\JsonStore;
use Illuminate\Support\Str;

/**
 * Pream's note for the team:
 * Each route can have many timetable images (e.g. weekday + weekend, or a
 * "current schedule" + "high-season schedule"). Each image carries its own
 * caption so admins can label them individually.
 *
 * Storage:
 *   storage/app/timetables.json     — metadata (key → array of images)
 *   storage/app/public/timetables/{route}/{random}.{ext} — files
 *
 * Routes use the same keys as the GPS tracker (rawai|patong|dragon).
 *
 * Auto-migrates the old single-image shape on first read so existing uploads
 * don't disappear after the upgrade.
 */
class TimetableRepository
{
    use JsonStore;

    public const ROUTES = [
        'rawai'  => ['label' => 'Airport ⇄ Patong ⇄ Rawai', 'flat_fare' => '100 ฿'],
        'patong' => ['label' => 'Bus Terminal ⇄ Patong',    'flat_fare' => '50 ฿'],
        'dragon' => ['label' => 'Dragon Line',              'flat_fare' => 'Free'],
    ];

    public const CAPTION_LOCALES = ['en', 'th', 'zh', 'ru'];

    /**
     * Pluck the right caption for the given locale, falling back to English,
     * then to any non-empty caption. Accepts both legacy string captions and
     * the new array shape transparently.
     */
    public static function localizedCaption(array|string|null $caption, ?string $locale = null): string
    {
        if (is_string($caption)) return $caption;
        if (!is_array($caption)) return '';
        $locale = $locale ?: app()->getLocale();
        if (!empty($caption[$locale])) return $caption[$locale];
        if (!empty($caption['en'])) return $caption['en'];
        foreach ($caption as $value) {
            if (!empty($value)) return $value;
        }
        return '';
    }

    protected string $jsonPath;

    public function __construct()
    {
        $this->jsonPath = storage_path('app/timetables.json');
        if (!file_exists($this->jsonPath)) {
            $this->save([]);
        }
    }

    /** All routes with metadata + images, keyed by route slug. */
    public function all(): array
    {
        $stored = $this->load();
        $out = [];
        foreach (self::ROUTES as $key => $meta) {
            $out[$key] = array_merge($meta, [
                'key' => $key,
                'images' => $stored[$key]['images'] ?? [],
                'updated_at' => $stored[$key]['updated_at'] ?? null,
            ]);
        }
        return $out;
    }

    public function get(string $key): ?array
    {
        return $this->all()[$key] ?? null;
    }

    /**
     * Append a new image to the route gallery.
     * $caption is a string (used as the English caption with empty TH/ZH/RU)
     * or a per-locale array.
     * @return array The image record { id, path, caption, uploaded_at }
     */
    public function addImage(string $key, string $relativePath, array|string $caption = ''): ?array
    {
        if (!isset(self::ROUTES[$key])) return null;
        $stored = $this->load();
        $images = $stored[$key]['images'] ?? [];

        // Normalize to per-locale shape so the storage format is uniform.
        $captionMap = [];
        foreach (self::CAPTION_LOCALES as $loc) {
            if (is_array($caption)) {
                $captionMap[$loc] = (string) ($caption[$loc] ?? '');
            } else {
                $captionMap[$loc] = $loc === 'en' ? (string) $caption : '';
            }
        }

        $record = [
            'id' => (string) Str::uuid(),
            'path' => $relativePath,
            'caption' => $captionMap,
            'uploaded_at' => date('Y-m-d H:i:s'),
        ];
        $images[] = $record;
        $stored[$key] = [
            'images' => $images,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->save($stored);
        return $record;
    }

    /**
     * Replace the entire per-locale caption map for one image.
     * $captions is e.g. ['en' => '...', 'th' => '...', 'zh' => '', 'ru' => ''].
     * Empty strings are kept (so editors can intentionally blank a locale).
     */
    public function updateCaption(string $key, string $imageId, array $captions): bool
    {
        $stored = $this->load();
        $images = $stored[$key]['images'] ?? [];
        foreach ($images as &$img) {
            if (($img['id'] ?? null) === $imageId) {
                $clean = [];
                foreach (self::CAPTION_LOCALES as $loc) {
                    $clean[$loc] = (string) ($captions[$loc] ?? '');
                }
                $img['caption'] = $clean;
                $stored[$key]['images'] = $images;
                $stored[$key]['updated_at'] = date('Y-m-d H:i:s');
                $this->save($stored);
                return true;
            }
        }
        return false;
    }

    /**
     * Move an image up or down in the gallery order.
     * $direction: 'up' or 'down'.
     */
    public function reorder(string $key, string $imageId, string $direction): bool
    {
        $stored = $this->load();
        $images = array_values($stored[$key]['images'] ?? []);
        $count = count($images);
        $idx = null;
        foreach ($images as $i => $img) {
            if (($img['id'] ?? null) === $imageId) { $idx = $i; break; }
        }
        if ($idx === null) return false;

        $swap = $direction === 'up' ? $idx - 1 : $idx + 1;
        if ($swap < 0 || $swap >= $count) return false;

        [$images[$idx], $images[$swap]] = [$images[$swap], $images[$idx]];
        $stored[$key]['images'] = $images;
        $stored[$key]['updated_at'] = date('Y-m-d H:i:s');
        $this->save($stored);
        return true;
    }

    /** Returns the removed image record (so the route handler can delete the file). */
    public function removeImage(string $key, string $imageId): ?array
    {
        $stored = $this->load();
        $images = $stored[$key]['images'] ?? [];
        $removed = null;
        $kept = [];
        foreach ($images as $img) {
            if (($img['id'] ?? null) === $imageId) {
                $removed = $img;
            } else {
                $kept[] = $img;
            }
        }
        if (!$removed) return null;
        $stored[$key]['images'] = $kept;
        $stored[$key]['updated_at'] = date('Y-m-d H:i:s');
        $this->save($stored);
        return $removed;
    }

    /** Returns all images that were on this route (so the route handler can delete files). */
    public function clearImages(string $key): array
    {
        $stored = $this->load();
        $removed = $stored[$key]['images'] ?? [];
        $stored[$key] = ['images' => [], 'updated_at' => date('Y-m-d H:i:s')];
        $this->save($stored);
        return $removed;
    }

    // ----- internals -----

    protected function load(): array
    {
        return $this->migrateShape($this->jsonRead($this->jsonPath));
    }

    /** Auto-migrate the legacy single-image shape: { image, caption } → { images: [...] }. */
    protected function migrateShape(array $data): array
    {
        $changed = false;
        foreach ($data as $key => $row) {
            if (!is_array($row)) continue;
            // Already new shape
            if (array_key_exists('images', $row)) continue;
            // Old shape with image+caption — wrap it
            if (!empty($row['image'])) {
                $data[$key] = [
                    'images' => [[
                        'id' => (string) Str::uuid(),
                        'path' => $row['image'],
                        'caption' => $row['caption'] ?? '',
                        'uploaded_at' => $row['updated_at'] ?? date('Y-m-d H:i:s'),
                    ]],
                    'updated_at' => $row['updated_at'] ?? date('Y-m-d H:i:s'),
                ];
                $changed = true;
            } else {
                $data[$key] = ['images' => [], 'updated_at' => $row['updated_at'] ?? null];
                $changed = true;
            }
        }
        if ($changed) {
            // Persist the migrated shape so subsequent loads are clean.
            $this->save($data);
        }
        return $data;
    }

    protected function save(array $data): void
    {
        $this->jsonWrite($this->jsonPath, $data);
    }
}
