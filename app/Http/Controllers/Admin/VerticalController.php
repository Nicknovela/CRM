<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class VerticalController extends Controller
{
    public function index()
    {
        return view('admin.verticals.index');
    }
}
