<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Services\HtmlSanitizer;
use App\Services\ImageUploadService;
use App\Services\PostsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostsController extends Controller
{
    public function index(PostsRepository $repo)
    {
        $posts = $repo->all(includeScheduled: true);
        return view('admin.posts.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.posts.form', ['post' => null]);
    }

    public function store(StorePostRequest $request, PostsRepository $repo, HtmlSanitizer $sanitizer, ImageUploadService $uploader)
    {
        $data = $request->validated();

        // SECURITY: sanitize all translation bodies before persisting.
        $data['translations'] = $sanitizer->cleanTranslationBodies($data['translations']);

        $created = $repo->create([
            'slug' => $data['slug'] ?: Str::slug($data['translations']['en']['title']),
            'area' => $data['area'],
            'route_recommendation' => $data['route_recommendation'],
            'reading_minutes' => (int) $data['reading_minutes'],
            'published_at' => $data['published_at'],
            'translations' => $data['translations'],
            'title' => $data['translations']['en']['title'],
            'excerpt' => $data['translations']['en']['excerpt'],
            'body' => $data['translations']['en']['body'],
            'nearest_stop' => $data['translations']['en']['nearest_stop'],
            'cover' => 'images/bus-mastercard.jpg',
            'gallery' => [],
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $i => $file) {
                $path = $uploader->storeStripped($file, 'destinations/'.$created['slug']);
                $rel = 'storage/'.$path;
                $repo->addPhoto($created['slug'], $rel);
                if ($i === 0) $repo->setCover($created['slug'], $rel);
            }
        }

        return redirect()->route('admin.posts.edit', $created['slug'])
            ->with('status', 'Post created.');
    }

    public function edit(string $slug, PostsRepository $repo)
    {
        $post = $repo->find($slug);
        abort_if(!$post, 404);
        return view('admin.posts.form', compact('post'));
    }

    public function update(UpdatePostRequest $request, string $slug, PostsRepository $repo, HtmlSanitizer $sanitizer, ImageUploadService $uploader)
    {
        $post = $repo->find($slug);
        abort_if(!$post, 404);

        // Translators get the same form, but only their translation fields stick.
        $isTranslatorOnly = !current_admin_can('posts.manage') && current_admin_can('translations.edit');

        $data = $request->validated();

        // SECURITY: sanitize all translation bodies before persisting.
        $data['translations'] = $sanitizer->cleanTranslationBodies($data['translations']);

        $update = [
            'translations' => $data['translations'],
            // Mirror English fields at top-level for legacy access
            'title' => $data['translations']['en']['title'],
            'excerpt' => $data['translations']['en']['excerpt'],
            'body' => $data['translations']['en']['body'],
            'nearest_stop' => $data['translations']['en']['nearest_stop'],
        ];

        if (!$isTranslatorOnly) {
            $update['area'] = $data['area'];
            $update['route_recommendation'] = $data['route_recommendation'];
            $update['reading_minutes'] = (int) $data['reading_minutes'];
            $update['published_at'] = $data['published_at'];
            $update['cover'] = $data['cover'] ?? $post['cover'];
        }

        $repo->update($slug, $update);

        if (!$isTranslatorOnly && $request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $path = $uploader->storeStripped($file, 'destinations/'.$slug);
                $rel = 'storage/'.$path;
                $repo->addPhoto($slug, $rel);
            }
        }

        return redirect()->route('admin.posts.edit', $slug)
            ->with('status', 'Post saved.');
    }

    public function destroy(string $slug, PostsRepository $repo)
    {
        $post = $repo->find($slug);
        abort_if(!$post, 404);
        Storage::disk('public')->deleteDirectory('destinations/'.$slug);
        $repo->delete($slug);
        return redirect()->route('admin.posts.index')->with('status', 'Post deleted.');
    }

    public function destroyPhoto(Request $request, string $slug, PostsRepository $repo)
    {
        $request->validate([
            'path' => ['required', 'string', 'starts_with:storage/destinations/'.$slug.'/'],
        ]);
        $rel = $request->input('path');
        $diskPath = preg_replace('#^storage/#', '', $rel);
        Storage::disk('public')->delete($diskPath);
        $repo->removePhoto($slug, $rel);

        // If we removed the current cover, fall back to the first remaining photo or default.
        $post = $repo->find($slug);
        if ($post && ($post['cover'] ?? null) === $rel) {
            $next = $post['gallery'][0] ?? 'images/bus-mastercard.jpg';
            $repo->setCover($slug, $next);
        }
        return back()->with('status', 'Photo removed.');
    }

    public function setCover(Request $request, string $slug, PostsRepository $repo)
    {
        $request->validate([
            'path' => ['required', 'string', 'starts_with:storage/destinations/'.$slug.'/'],
        ]);
        $repo->setCover($slug, $request->input('path'));
        return back()->with('status', 'Cover updated.');
    }

}
