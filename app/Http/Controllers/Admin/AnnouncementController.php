<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\User;
use App\Notifications\AnnouncementPublished;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        return view('admin.announcements', [
            'announcements' => Announcement::query()
                ->with('creator:id,username')
                ->latest()
                ->paginate(15),
            'publishedCount' => Announcement::published()->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string', 'max:10000'],
            'status' => ['required', Rule::in(['draft', 'published'])],
        ]);

        $announcement = new Announcement([
            'title' => $validated['title'],
            'body' => $validated['body'],
        ]);
        $announcement->creator()->associate($request->user());
        $announcement->published_at = $validated['status'] === 'published' ? now() : null;
        $announcement->save();

        if ($validated['status'] === 'published') {
            User::query()->where('id', '!=', $request->user()->id)
                ->each(function (User $user) use ($announcement): void {
                    $user->notify(new AnnouncementPublished($announcement));
                });
        }

        return redirect()->route('admin.announcements.index')->with('status', 'Announcement created.');
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['draft', 'published'])],
        ]);

        $announcement->published_at = $validated['status'] === 'published'
            ? ($announcement->published_at ?? now())
            : null;
        $announcement->save();

        return back()->with('status', 'Announcement status updated.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return back()->with('status', 'Announcement deleted.');
    }
}
