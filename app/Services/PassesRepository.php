<?php

namespace App\Services;

use App\Services\Concerns\JsonStore;
use Illuminate\Support\Str;

/**
 * Pream's note for the team:
 * Day-pass catalog. Each pass has a price, a duration, and per-locale name +
 * description. Optional cover image (uploaded via admin) — when present, the
 * card uses the uploaded design; when absent, the public view falls back to
 * the airport-bus photo with a "1 Day Pass" text overlay (current behaviour).
 *
 * Same JSON-store + LOCK_EX pattern as PostsRepository / TimetableRepository.
 * Auto-seeds the original 4 passes (299 / 699 / 1290 / 3500) on first read so
 * nothing breaks when you ship this without running a migration.
 */
class PassesRepository
{
    use JsonStore;

    public const LOCALES = ['en', 'th', 'zh', 'ru'];

    protected string $jsonPath;

    public function __construct()
    {
        $this->jsonPath = storage_path('app/passes.json');
        $this->seedIfMissing();
    }

    /** All passes, sorted by sort_order then duration_days ascending. */
    public function all(): array
    {
        $passes = $this->load();
        usort($passes, function ($a, $b) {
            $sa = $a['sort_order'] ?? 999;
            $sb = $b['sort_order'] ?? 999;
            if ($sa === $sb) return ($a['duration_days'] ?? 0) <=> ($b['duration_days'] ?? 0);
            return $sa <=> $sb;
        });
        return $passes;
    }

    public function find(string $id): ?array
    {
        foreach ($this->load() as $p) {
            if (($p['id'] ?? null) === $id) return $p;
        }
        return null;
    }

    public function create(array $data): array
    {
        $passes = $this->load();
        $data['id'] = (string) Str::uuid();
        $data['sort_order'] = $data['sort_order'] ?? (count($passes) + 1) * 10;
        $data['cover'] = $data['cover'] ?? null;
        $data['translations'] = $this->normalizeTranslations($data['translations'] ?? []);
        $passes[] = $data;
        $this->save($passes);
        return $data;
    }

    public function update(string $id, array $data): ?array
    {
        $passes = $this->load();
        foreach ($passes as $i => $p) {
            if (($p['id'] ?? null) === $id) {
                if (isset($data['translations'])) {
                    $data['translations'] = $this->normalizeTranslations($data['translations']);
                }
                $passes[$i] = array_merge($p, $data);
                $this->save($passes);
                return $passes[$i];
            }
        }
        return null;
    }

    public function delete(string $id): bool
    {
        $passes = $this->load();
        $kept = array_values(array_filter($passes, fn ($p) => ($p['id'] ?? null) !== $id));
        if (count($kept) === count($passes)) return false;
        $this->save($kept);
        return true;
    }

    public function setCover(string $id, ?string $coverPath): ?array
    {
        return $this->update($id, ['cover' => $coverPath]);
    }

    public function reorder(string $id, string $direction): bool
    {
        $passes = $this->all();
        $idx = null;
        foreach ($passes as $i => $p) {
            if (($p['id'] ?? null) === $id) { $idx = $i; break; }
        }
        if ($idx === null) return false;
        $swap = $direction === 'up' ? $idx - 1 : $idx + 1;
        if ($swap < 0 || $swap >= count($passes)) return false;

        // Rewrite sort_order across the whole list to preserve the new ordering.
        [$passes[$idx], $passes[$swap]] = [$passes[$swap], $passes[$idx]];
        foreach ($passes as $i => &$p) {
            $p['sort_order'] = ($i + 1) * 10;
        }
        $this->save($passes);
        return true;
    }

    /**
     * Resolve the right name + description for the given locale, with the same
     * en-fallback behaviour as PostsRepository::localized().
     */
    public function localized(array $pass, ?string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();
        $tx = $pass['translations'] ?? [];
        $pass['name'] = $tx[$locale]['name'] ?? $tx['en']['name'] ?? ($pass['name'] ?? '');
        $pass['description'] = $tx[$locale]['description'] ?? $tx['en']['description'] ?? ($pass['description'] ?? '');
        return $pass;
    }

    // ----- internals -----

    protected function load(): array
    {
        return $this->jsonRead($this->jsonPath);
    }

    protected function save(array $data): void
    {
        $this->jsonWrite($this->jsonPath, $data);
    }

    protected function normalizeTranslations(array $tx): array
    {
        $out = [];
        foreach (self::LOCALES as $loc) {
            $out[$loc] = [
                'name' => (string) ($tx[$loc]['name'] ?? ''),
                'description' => (string) ($tx[$loc]['description'] ?? ''),
            ];
        }
        return $out;
    }

    protected function seedIfMissing(): void
    {
        if (file_exists($this->jsonPath)) return;
        $seed = [
            ['1 Day Pass',  299,  1,  'Unlimited rides for one day',   null,  10],
            ['3 Day Pass',  699,  3,  'Unlimited rides for 3 days',    null,  20],
            ['7 Day Pass',  1290, 7,  'A full week of unlimited travel', null, 30],
            ['30 Day Pass', 3500, 30, 'A month of freedom',           'images/pass-30days.png', 40],
        ];
        $rows = [];
        foreach ($seed as [$name, $price, $days, $desc, $cover, $sort]) {
            $rows[] = [
                'id' => (string) Str::uuid(),
                'price' => $price,
                'currency' => 'THB',
                'duration_days' => $days,
                'cover' => $cover,
                'sort_order' => $sort,
                'translations' => $this->normalizeTranslations([
                    'en' => ['name' => $name, 'description' => $desc],
                ]),
            ];
        }
        $this->save($rows);
    }
}
