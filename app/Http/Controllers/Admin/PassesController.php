<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePassRequest;
use App\Http\Requests\Admin\UpdatePassRequest;
use App\Services\ImageUploadService;
use App\Services\PassesRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PassesController extends Controller
{
    public function index(PassesRepository $repo)
    {
        $passes = $repo->all();
        return view('admin.passes.index', compact('passes'));
    }

    public function store(StorePassRequest $request, PassesRepository $repo)
    {
        $data = $request->validated();
        $repo->create([
            'price' => (int) $data['price'],
            'currency' => 'THB',
            'duration_days' => (int) $data['duration_days'],
            'cover' => null,
            'translations' => [
                'en' => [
                    'name' => $data['translations']['en']['name'],
                    'description' => '',
                ],
            ],
        ]);
        return redirect()->route('admin.passes.index')->with('status', 'Pass created.');
    }

    public function update(UpdatePassRequest $request, string $id, PassesRepository $repo, ImageUploadService $uploader)
    {
        abort_if(!$repo->find($id), 404);
        $data = $request->validated();

        $update = [
            'translations' => $data['translations'],
            'price' => (int) $data['price'],
            'duration_days' => (int) $data['duration_days'],
        ];

        if ($request->hasFile('cover')) {
            $stored = $uploader->storeStripped($request->file('cover'), 'passes');
            $update['cover'] = 'storage/'.$stored;
        } elseif (($data['cover_action'] ?? null) === 'clear') {
            $current = $repo->find($id);
            if ($current && !empty($current['cover']) && str_starts_with($current['cover'], 'storage/')) {
                Storage::disk('public')->delete(preg_replace('#^storage/#', '', $current['cover']));
            }
            $update['cover'] = null;
        }

        $repo->update($id, $update);
        return back()->with('status', 'Pass saved.');
    }

    public function reorder(Request $request, string $id, PassesRepository $repo)
    {
        $data = $request->validate(['direction' => 'required|in:up,down']);
        $repo->reorder($id, $data['direction']);
        return back();
    }

    public function destroy(string $id, PassesRepository $repo)
    {
        $current = $repo->find($id);
        abort_if(!$current, 404);
        if (!empty($current['cover']) && str_starts_with($current['cover'], 'storage/')) {
            Storage::disk('public')->delete(preg_replace('#^storage/#', '', $current['cover']));
        }
        $repo->delete($id);
        return redirect()->route('admin.passes.index')->with('status', 'Pass deleted.');
    }
}
