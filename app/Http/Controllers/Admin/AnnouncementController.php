<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\User;
use App\Notifications\AnnouncementPublished;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'category' => (string) $request->query('category', ''),
        ];

        if (! array_key_exists($filters['category'], Announcement::CATEGORIES)) {
            $filters['category'] = '';
        }

        $query = Announcement::query()
            ->with('creator:id,username')
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $search = $filters['search'];
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->when($filters['category'] !== '', fn ($query) => $query->where('category', $filters['category']))
            ->orderByDesc('is_pinned')
            ->latest();
        $announcementCount = (clone $query)->count();

        return view('admin.announcements', [
            'announcements' => $query->paginate(15)->withQueryString(),
            'announcementCount' => $announcementCount,
            'filters' => $filters,
            'categories' => Announcement::CATEGORIES,
            'publishedCount' => Announcement::published()->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.announcement-create');
    }

    public function edit(Announcement $announcement): View
    {
        return view('admin.announcement-edit', [
            'announcement' => $announcement,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string', 'max:10000'],
            'category' => ['required', Rule::in(array_keys(Announcement::CATEGORIES))],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'publish_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:publish_date'],
            'is_pinned' => ['required', 'boolean'],
        ]);

        $announcement = new Announcement([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'category' => $validated['category'],
        ]);
        $announcement->creator()->associate($request->user());
        $announcement->published_at = $validated['status'] === 'published'
            ? Carbon::parse($validated['publish_date'] ?? now())->startOfDay()
            : null;
        $announcement->expires_at = filled($validated['expiry_date'] ?? null)
            ? Carbon::parse($validated['expiry_date'])->endOfDay()
            : null;
        $announcement->is_pinned = $validated['is_pinned'];
        $announcement->save();

        if ($announcement->published_at?->isPast()) {
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
            'title' => ['sometimes', 'required', 'string', 'max:180'],
            'body' => ['sometimes', 'required', 'string', 'max:10000'],
            'category' => ['sometimes', 'required', Rule::in(array_keys(Announcement::CATEGORIES))],
            'status' => ['sometimes', 'required', Rule::in(['draft', 'published'])],
            'publish_date' => ['sometimes', 'nullable', 'date'],
            'expiry_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:publish_date'],
            'is_pinned' => ['sometimes', 'required', 'boolean'],
        ]);

        if (array_key_exists('title', $validated) || array_key_exists('body', $validated)) {
            $announcement->fill([
                'title' => $validated['title'] ?? $announcement->title,
                'body' => $validated['body'] ?? $announcement->body,
            ]);
        }

        if (array_key_exists('status', $validated)) {
            $announcement->published_at = $validated['status'] === 'published'
                ? Carbon::parse($validated['publish_date'] ?? $announcement->published_at ?? now())->startOfDay()
                : null;
        } elseif (array_key_exists('publish_date', $validated) && $announcement->published_at) {
            $announcement->published_at = filled($validated['publish_date'])
                ? Carbon::parse($validated['publish_date'])->startOfDay()
                : now();
        }

        if (array_key_exists('category', $validated)) {
            $announcement->category = $validated['category'];
        }

        if (array_key_exists('expiry_date', $validated)) {
            $announcement->expires_at = filled($validated['expiry_date'])
                ? Carbon::parse($validated['expiry_date'])->endOfDay()
                : null;
        }

        if (array_key_exists('is_pinned', $validated)) {
            $announcement->is_pinned = $validated['is_pinned'];
        }

        $announcement->save();

        return redirect()->route('admin.announcements.index')->with('status', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return back()->with('status', 'Announcement deleted.');
    }
}
