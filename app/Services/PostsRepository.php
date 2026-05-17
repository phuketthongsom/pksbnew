<?php

namespace App\Services;

use App\Services\Concerns\JsonStore;
use Illuminate\Support\Str;

class PostsRepository
{
    use JsonStore;

    public const LOCALES = ['en', 'th', 'zh', 'ru'];
    public const TRANSLATABLE = ['title', 'excerpt', 'body', 'nearest_stop'];

    /** Path to the JSON store inside storage/app/. */
    protected string $jsonPath;

    public function __construct()
    {
        $this->jsonPath = storage_path('app/posts.json');
        $this->seedIfMissing();
    }

    /**
     * All posts, sorted newest first.
     * Pass $includeScheduled=true (admin) to also return future-dated posts.
     */
    public function all(bool $includeScheduled = false): array
    {
        $posts = $this->load();
        if (!$includeScheduled) {
            $today = date('Y-m-d');
            $posts = array_values(array_filter($posts, fn ($p) => ($p['published_at'] ?? '') <= $today));
        }
        usort($posts, fn ($a, $b) => strcmp($b['published_at'] ?? '', $a['published_at'] ?? ''));
        return $posts;
    }

    public function find(string $slug): ?array
    {
        foreach ($this->load() as $p) {
            if (($p['slug'] ?? null) === $slug) return $p;
        }
        return null;
    }

    /** Create a post. Returns the created post. */
    public function create(array $data): array
    {
        $posts = $this->load();
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['title'] ?? 'post', $posts);
        $data['gallery'] = $data['gallery'] ?? [];
        $data['published_at'] = $data['published_at'] ?? date('Y-m-d');
        $posts[] = $data;
        $this->save($posts);
        return $data;
    }

    /** Update by slug. */
    public function update(string $slug, array $data): ?array
    {
        $posts = $this->load();
        foreach ($posts as $i => $p) {
            if (($p['slug'] ?? null) === $slug) {
                $posts[$i] = array_merge($p, $data);
                $this->save($posts);
                return $posts[$i];
            }
        }
        return null;
    }

    public function delete(string $slug): bool
    {
        $posts = $this->load();
        $filtered = array_values(array_filter($posts, fn ($p) => ($p['slug'] ?? null) !== $slug));
        if (count($filtered) === count($posts)) return false;
        $this->save($filtered);
        return true;
    }

    public function addPhoto(string $slug, string $relativePath): ?array
    {
        $post = $this->find($slug);
        if (!$post) return null;
        $gallery = $post['gallery'] ?? [];
        $gallery[] = $relativePath;
        return $this->update($slug, ['gallery' => array_values(array_unique($gallery))]);
    }

    public function removePhoto(string $slug, string $relativePath): ?array
    {
        $post = $this->find($slug);
        if (!$post) return null;
        $gallery = array_values(array_filter($post['gallery'] ?? [], fn ($g) => $g !== $relativePath));
        return $this->update($slug, ['gallery' => $gallery]);
    }

    public function setCover(string $slug, string $relativePath): ?array
    {
        return $this->update($slug, ['cover' => $relativePath]);
    }

    /**
     * Return $post with the translatable fields resolved for $locale.
     * Falls back to English, then to the top-level field, then to ''.
     */
    public function localized(array $post, ?string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();
        $tx = $post['translations'] ?? [];
        foreach (self::TRANSLATABLE as $f) {
            $value = $tx[$locale][$f]
                ?? $tx['en'][$f]
                ?? ($post[$f] ?? '');
            $post[$f] = $value;
        }
        return $post;
    }

    // --- internals ---

    protected function load(): array
    {
        return $this->jsonRead($this->jsonPath);
    }

    protected function save(array $posts): void
    {
        $this->jsonWrite($this->jsonPath, $posts);
    }

    protected function uniqueSlug(string $base, array $existing): string
    {
        $slug = Str::slug($base) ?: 'post';
        $original = $slug;
        $i = 2;
        $taken = array_column($existing, 'slug');
        while (in_array($slug, $taken, true)) {
            $slug = $original.'-'.$i;
            $i++;
        }
        return $slug;
    }

    /** First-run: copy posts from database/seed/posts.php into the JSON store. */
    protected function seedIfMissing(): void
    {
        if (file_exists($this->jsonPath)) return;
        $seedPath = base_path('database/seed/posts.php');
        if (!file_exists($seedPath)) return;
        $seed = require $seedPath;
        if (!is_array($seed) || empty($seed)) return;
        foreach ($seed as &$p) {
            $p['gallery'] = $p['gallery'] ?? [];
        }
        $this->save($seed);
    }
}
