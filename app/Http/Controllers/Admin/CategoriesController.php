<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CategoriesRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoriesController extends Controller
{
    public function index(CategoriesRepository $repo)
    {
        $categories = $repo->all();
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request, CategoriesRepository $repo)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'tagline'   => 'nullable|string|max:200',
            'accent'    => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{3,6}$/'],
            'thumbnail' => 'nullable|image|max:5120',
        ]);

        $cat = $repo->create([
            'name'       => $data['name'],
            'tagline'    => $data['tagline'] ?? '',
            'accent'     => $data['accent'] ?? '#01aaa8',
            'icon'       => 'camera',
            'hero_image' => null,
        ]);

        if ($request->hasFile('thumbnail')) {
            $path = $this->saveThumbnail($request, $cat['slug']);
            $repo->update($cat['slug'], ['hero_image' => $path]);
        }

        return back()->with('status', 'Category created.');
    }

    public function update(Request $request, string $slug, CategoriesRepository $repo)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'tagline'   => 'nullable|string|max:200',
            'accent'    => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{3,6}$/'],
            'thumbnail' => 'nullable|image|max:5120',
        ]);

        $updates = [
            'name'    => $data['name'],
            'tagline' => $data['tagline'] ?? '',
            'accent'  => $data['accent'] ?? '#01aaa8',
        ];

        if ($request->hasFile('thumbnail')) {
            $updates['hero_image'] = $this->saveThumbnail($request, $slug);
        }

        $repo->update($slug, $updates);

        return back()->with('status', 'Category updated.');
    }

    public function destroy(string $slug, CategoriesRepository $repo)
    {
        $repo->delete($slug);
        // Clean up thumbnail folder if it exists
        Storage::disk('public')->deleteDirectory("categories/{$slug}");
        return back()->with('status', 'Category deleted.');
    }

    // -----------------------------------------------------------------------

    private function saveThumbnail(Request $request, string $slug): string
    {
        $dir = "categories/{$slug}";
        // Remove old thumbnails in that folder
        Storage::disk('public')->deleteDirectory($dir);

        $file = $request->file('thumbnail');
        $ext  = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
        $name = "thumbnail.{$ext}";
        $file->storeAs($dir, $name, 'public');

        // Return the web-accessible path
        return 'storage/' . $dir . '/' . $name;
    }
}
