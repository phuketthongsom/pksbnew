<?php

namespace App\Http\Controllers;

use App\Services\PostsRepository;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(PostsRepository $repo)
    {
        $posts = collect($repo->all())->map(fn ($p) => $repo->localized($p));
        return view('pages.blog.index', compact('posts'));
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
