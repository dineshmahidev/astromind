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

    public function updateProfile(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'profile_image' => 'required|string'
        ]);

        $astrologer = Astrologer::find($request->id);
        if ($astrologer) {
            $astrologer->profile_image = $request->profile_image;
            $astrologer->save();
        }

        return response()->json(['success' => true]);
    }

    public function getStats(Request $request)
    {
        // For now, returning realistic dynamic data linked to the ID
        // In a full implementation, this would query the consultations table
        $id = $request->id ?? 1;
        return response()->json([
            'success' => true,
            'earnings_today' => 4500 + ($id * 10),
            'total_consults' => 15 + ($id % 5),
            'rating' => 4.8
        ]);
    }
}
