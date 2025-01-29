<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class NewsController extends Controller
{
    public function index()
    {
        return Inertia::render('Editor/News/Index', [
            'articles' => NewsArticle::latest()->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Editor/News/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' =>'required|string|max:255',
            'content' =>'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date'
        ]);

        $path = $request->file('image')->store('images/news', 'public');

        NewsArticle::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image_path' => $path,
            'is_published' => $validated['is_published'],
            'published_at' => $validated('is_published') ? now() : $validated['published_at']
        ]);

        return redirect()->route('editor.news.index');
    }

    public function edit(NewsArticle $article)
    {
        return Inertia::render('Editor/News/Edit', [
            'article' => $article
        ]);
    }

    public function update(Request $request, NewsArticle $article)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date'
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($article->image_path);
            $path = $request->file('image')->store('images/news', 'public');
            $article->image_path = $path;
        }

        $article->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_published' => $validated['is_published'],
            'published_at' => $validated['is_published'] ? now() : $validated['published_at']
        ]);

        return redirect()->route('editor.news.index');
    }

    public function destroy(NewsArticle $article)
    {
        Storage::disk('public')->delete($article->image_path);
        $article->delete();
        return redirect()->route('editor.news.index');
    }
}
