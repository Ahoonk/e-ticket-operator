<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Opd;
use Illuminate\Http\Request;

class OpdController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Opd::orderByDesc('id')->get();

        return response()->json($items);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'lokasi' => ['nullable', 'string', 'max:255'],
        ]);

        $item = Opd::create($data);

        return response()->json($item, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Opd $opd)
    {
        return response()->json($opd);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Opd $opd)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'lokasi' => ['nullable', 'string', 'max:255'],
        ]);

        $opd->update($data);

        return response()->json($opd);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Opd $opd)
    {
        $opd->delete();

        return response()->json(['message' => 'deleted']);
    }
}
