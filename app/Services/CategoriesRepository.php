<?php

namespace App\Services;

use App\Services\Concerns\JsonStore;
use Illuminate\Support\Str;

class CategoriesRepository
{
    use JsonStore;

    protected string $jsonPath;

    public function __construct()
    {
        $this->jsonPath = storage_path('app/categories.json');
        if (!file_exists($this->jsonPath)) {
            $this->save([]);
        }
    }

    /** Return all categories as an array. */
    public function all(): array
    {
        return $this->load();
    }

    /** Find a single category by slug, or null if not found. */
    public function find(string $slug): ?array
    {
        foreach ($this->load() as $cat) {
            if (($cat['slug'] ?? null) === $slug) {
                return $cat;
            }
        }
        return null;
    }

    /** Create a new category. Returns the created record. */
    public function create(array $data): array
    {
        $cats = $this->load();
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name'] ?? 'category', $cats);
        $data['hero_image'] = $data['hero_image'] ?? null;
        $cats[] = $data;
        $this->save($cats);
        return $data;
    }

    /** Update a category by slug. Returns the updated record, or null if not found. */
    public function update(string $slug, array $data): ?array
    {
        $cats = $this->load();
        foreach ($cats as $i => $cat) {
            if (($cat['slug'] ?? null) === $slug) {
                $cats[$i] = array_merge($cat, $data);
                $this->save($cats);
                return $cats[$i];
            }
        }
        return null;
    }

    /** Delete a category by slug. Returns true if deleted, false if not found. */
    public function delete(string $slug): bool
    {
        $cats = $this->load();
        $filtered = array_values(array_filter($cats, fn ($c) => ($c['slug'] ?? null) !== $slug));
        if (count($filtered) === count($cats)) {
            return false;
        }
        $this->save($filtered);
        return true;
    }

    /** Set the hero image path for a category. */
    public function setHeroImage(string $slug, string $path): ?array
    {
        return $this->update($slug, ['hero_image' => $path]);
    }

    // --- internals ---

    protected function load(): array
    {
        return $this->jsonRead($this->jsonPath);
    }

    protected function save(array $data): void
    {
        $this->jsonWrite($this->jsonPath, $data);
    }

    protected function uniqueSlug(string $base, array $existing): string
    {
        $slug = Str::slug($base) ?: 'category';
        $original = $slug;
        $i = 2;
        $taken = array_column($existing, 'slug');
        while (in_array($slug, $taken, true)) {
            $slug = $original . '-' . $i;
            $i++;
        }
        return $slug;
    }
}
