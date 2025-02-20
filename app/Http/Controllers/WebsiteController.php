<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebsiteController extends Controller
{


    public function index()
    {
        $websites = Website::where('user_id', Auth::id())->get();
        return view('websites.index', compact('websites'));
    }

    public function create()
    {
        return view('websites.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'url' => 'required|url|max:255',
            'notification' => 'boolean'
        ]);

        $website = new Website($validated);
        $website->user_id = Auth::id();
        $website->save();

        return redirect()->route('websites.index')
            ->with('success', 'Website hozzáadva a monitorozáshoz.');
    }

    public function edit(Website $website)
    {

        return view('websites.edit', compact('website'));
    }

    public function update(Request $request, Website $website)
    {


        $validated = $request->validate([
            'name' => 'required|max:255',
            'url' => 'required|url|max:255',
            'notification' => 'boolean'
        ]);

        $website->update($validated);

        return redirect()->route('websites.index')
            ->with('success', 'Website frissítve.');
    }

    public function destroy(Website $website)
    {

        $website->delete();

        return redirect()->route('websites.index')
            ->with('success', 'Website törölve a monitorozásból.');
    }
}
