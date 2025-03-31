<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\_News;
use App\Http\Resources\_User;
use App\Models\News;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;

class NewsController extends Controller
{
    public function index(Request $request)
    {

        $news = fn() => _News::collection(
            News::orderByRelation($request->sort ?? [], ['id', 'asc'], App::getLocale())
                ->paginate($request->paginate ?? 10)
        );
        return Inertia::render('Admin/News/Index', compact('news'));
    }

    public function show(News $news)
    {
        $news->load('user');

        $newsResource = _News::init($news);

        return Inertia::render('Admin/News/Detail', [
            'news' => $newsResource,
        ]);
    }
    public function edit(News $news)
    {
        $news->load('user');

        $newsResource = new _News($news);

        return Inertia::render('Admin/News/Credit', [
            'news' => $newsResource,
        ]);
    }
    public function create()
    {
        return Inertia::render('Admin/News/Credit');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:novinka,blogpost,analyza',
            'is_active' => 'boolean',
        ]);


        $validated['user_id'] = auth()->id();

        $news = News::create($validated);

        return redirect()->route('admin.news.index')->with('success', 'News created successfully.');
    }
    public function update(Request $request, News $news)
    {
        // If only updating the is_active status from the index page
        if ($request->has('is_active') && count($request->all()) === 1) {
            $news->update([
                'is_active' => $request->boolean('is_active')
            ]);

            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect()->route('admin.news.index')->with('success', 'Status updated successfully.');
        }

        // Full update from the edit page
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:novinka,blogpost,analyza',
            'is_active' => 'boolean',
        ]);

        $news->update($validated);

        return redirect()->route('admin.news.index')->with('success', 'News updated successfully.');
    }
    public function destroy(News $news)
    {
        $news->delete();

        return redirect()->route('admin.news.index')->with('success', 'News deleted successfully.');
    }
}
