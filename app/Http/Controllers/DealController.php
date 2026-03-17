<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Services\DealService;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function __construct(private DealService $dealService) {}

    public function index()
    {
        return view('deals.index');
    }

    public function show(Deal $deal)
    {
        return view('deals.show', compact('deal'));
    }

    public function create()
    {
        return view('deals.create');
    }

    public function store(Request $request)
    {
        return redirect()->route('deals.index');
    }

    public function edit(Deal $deal)
    {
        return view('deals.edit', compact('deal'));
    }

    public function update(Request $request, Deal $deal)
    {
        return redirect()->route('deals.show', $deal);
    }

    public function destroy(Deal $deal)
    {
        $deal->delete();
        return redirect()->route('deals.index');
    }

    public function forceDestroy(Deal $deal)
    {
        $this->dealService->forceDelete($deal);
        return redirect()->route('deals.index');
    }

    public function restore(Deal $deal)
    {
        $this->dealService->restore($deal);
        return redirect()->route('deals.show', $deal->id);
    }

    public function move(Request $request, Deal $deal)
    {
        $validated = $request->validate(['stage_id' => 'required|exists:stages,id']);
        $this->dealService->moveToStage($deal, $validated['stage_id']);
        return response()->json(['ok' => true]);
    }
}
