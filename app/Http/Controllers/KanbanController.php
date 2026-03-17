<?php

namespace App\Http\Controllers;

use App\Models\Vertical;

class KanbanController extends Controller
{
    public function __invoke()
    {
        $verticals = Vertical::active()->get();
        return view('kanban', compact('verticals'));
    }
}
