<?php

namespace App\Http\Controllers;

use App\Services\CategoriesRepository;
use App\Services\PostsRepository;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(PostsRepository $repo, CategoriesRepository $catRepo)
    {
        $posts = collect($repo->all())->map(fn ($p) => $repo->localized($p));
        $categories = $catRepo->all();
        $grouped = $posts->groupBy(fn ($p) => $p['category'] ?? '');
        return view('pages.blog.index', compact('posts', 'categories', 'grouped'));
    }

    public function category(string $cat, PostsRepository $repo, CategoriesRepository $catRepo)
    {
        $activeCategory = $catRepo->find($cat);
        abort_if(!$activeCategory, 404);

        $posts = collect($repo->all())->map(fn ($p) => $repo->localized($p));
        $categories = $catRepo->all();
        $grouped = $posts->groupBy(fn ($p) => $p['category'] ?? '');
        return view('pages.blog.index', compact('posts', 'categories', 'grouped', 'activeCategory'));
    }

    public function show(string $slug, PostsRepository $repo, Request $request)
    {
        $post = $repo->find($slug);
        abort_if(!$post, 404);

        // Hide scheduled posts from the public unless an admin is previewing.
        $isFuture = ($post['published_at'] ?? '') > date('Y-m-d');
        if ($isFuture && !$request->session()->get('admin_user_id')) {
            abort(404);
        }

        $post = $repo->localized($post);
        $related = collect($repo->all())
            ->where('slug', '!=', $slug)
            ->shuffle()
            ->take(3)
            ->map(fn ($p) => $repo->localized($p))
            ->values();

        return view('pages.blog.show', compact('post', 'related'));
    }
}
