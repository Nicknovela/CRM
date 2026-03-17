<?php

namespace App\Http\Controllers;

use App\Models\Vertical;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $verticals = Vertical::active()->with(['stages' => fn ($q) => $q->ordered()])->get();
        return view('dashboard', compact('verticals'));
    }
}
