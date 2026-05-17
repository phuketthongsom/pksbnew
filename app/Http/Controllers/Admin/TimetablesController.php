<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UploadTimetableImagesRequest;
use App\Http\Requests\Admin\UpdateTimetableCaptionRequest;
use App\Services\ImageUploadService;
use App\Services\TimetableRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TimetablesController extends Controller
{
    public function index(TimetableRepository $repo)
    {
        $routes = $repo->all();
        return view('admin.timetables.index', compact('routes'));
    }

    public function storeImages(UploadTimetableImagesRequest $request, string $key, TimetableRepository $repo, ImageUploadService $uploader)
    {
        abort_if(!$repo->get($key), 404);
        $data = $request->validated();

        foreach ($request->file('images') as $file) {
            $stored = $uploader->storeStripped($file, 'timetables/'.$key);
            $repo->addImage($key, 'storage/'.$stored, $data['caption'] ?? '');
        }
        return back()->with('status', 'Timetable image(s) added.');
    }

    public function updateCaption(UpdateTimetableCaptionRequest $request, string $key, string $imageId, TimetableRepository $repo)
    {
        $data = $request->validated();
        $ok = $repo->updateCaption($key, $imageId, $data['caption'] ?? []);
        abort_if(!$ok, 404);
        return back()->with('status', 'Caption updated.');
    }

    public function reorderImage(Request $request, string $key, string $imageId, TimetableRepository $repo)
    {
        $data = $request->validate(['direction' => 'required|in:up,down']);
        $repo->reorder($key, $imageId, $data['direction']);
        return back();
    }

    public function destroyImage(string $key, string $imageId, TimetableRepository $repo)
    {
        $removed = $repo->removeImage($key, $imageId);
        abort_if(!$removed, 404);
        Storage::disk('public')->delete(preg_replace('#^storage/#', '', $removed['path']));
        return back()->with('status', 'Image removed.');
    }
}
