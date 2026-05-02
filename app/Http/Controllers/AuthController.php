<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Services\AstrologyService;

class AuthController extends Controller
{
    protected $astrologyService;

    public function __construct(AstrologyService $astrologyService)
    {
        $this->astrologyService = $astrologyService;
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'dob' => 'required|string', 
            'tob' => 'required|string', 
        ]);

        $dobParts = explode('-', $request->dob);
        $tobParts = explode(':', $request->tob);
        
        $astro = $this->astrologyService->getDetails(
            $dobParts[2], 
            $dobParts[1], 
            $dobParts[0], 
            $tobParts[0], 
            $tobParts[1], 
            0
        );

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'avatar' => $request->avatar ?? 'avatar1',
            'dob' => $request->dob,
            'tob' => $request->tob,
            'rasi' => $astro['rasi'],
            'nakshatra' => $astro['nakshatra'],
            'padam' => $astro['padam'],
            'wallet_balance' => 0.00
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'user' => $user
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => $user
        ]);
    }
}
