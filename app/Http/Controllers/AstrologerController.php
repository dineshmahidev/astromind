<?php

namespace App\Http\Controllers;

use App\Models\Astrologer;
use Illuminate\Http\Request;

class AstrologerController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Astrologer::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'specialization' => 'required|string',
            'experience' => 'required|integer',
            'languages' => 'required|string',
            'bio' => 'required|string',
            'price_per_minute' => 'required|numeric',
            'profile_image' => 'nullable|string'
        ]);

        $astrologer = Astrologer::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Astrologer added successfully',
            'data' => $astrologer
        ]);
    }
}
