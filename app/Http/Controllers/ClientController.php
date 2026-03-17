<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        return view('clients.index');
    }

    public function show($id)
    {
        return view('clients.show', ['clientId' => $id]);
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        // Handled by Livewire
        return redirect()->route('clients.index');
    }

    public function edit($id)
    {
        return view('clients.edit', ['clientId' => $id]);
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('clients.show', $id);
    }

    public function destroy($id)
    {
        return redirect()->route('clients.index');
    }
}
