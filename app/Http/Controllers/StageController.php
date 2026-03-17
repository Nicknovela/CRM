<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use App\Models\Vertical;
use Illuminate\Http\Request;

class StageController extends Controller
{
    public function byVertical(Vertical $vertical)
    {
        return response()->json(
            $vertical->stages()->ordered()->get(['id', 'name', 'color', 'is_won', 'is_lost'])
        );
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:stages,id']);

        foreach ($validated['ids'] as $position => $id) {
            Stage::where('id', $id)->update(['position' => $position + 1]);
        }

        return response()->json(['ok' => true]);
    }
}
