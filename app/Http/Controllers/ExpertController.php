<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consultation;
use App\Models\User;
use App\Models\Astrologer;
use Illuminate\Support\Facades\Auth;
use App\Services\AstrologyService;

class ExpertController extends Controller
{
    protected $astrologyService;

    public function __construct(AstrologyService $astrologyService)
    {
        $this->astrologyService = $astrologyService;
    }

    public function dashboard()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'astrologer') {
            return redirect('/')->with('error', 'Unauthorized access.');
        }

        $expert = Astrologer::where('user_id', $user->id)->first();

        if (!$expert) {
            return redirect('/')->with('error', 'Expert profile not found.');
        }

        $consultations = Consultation::where('astrologer_id', $expert->id)
            ->with('user')
            ->latest()
            ->take(10)
            ->get();

        // Enrich consultations with Astrology data
        foreach ($consultations as $consult) {
            if ($consult->user->dob && $consult->user->tob) {
                $consult->astro_data = $this->astrologyService->getHoroscope([
                    'dob' => $consult->user->dob,
                    'tob' => $consult->user->tob
                ]);
            }
        }

        $stats = [
            'total_sessions' => Consultation::where('astrologer_id', $expert->id)->count(),
            'pending' => Consultation::where('astrologer_id', $expert->id)->where('status', 'pending')->count(),
            'completed' => Consultation::where('astrologer_id', $expert->id)->where('status', 'completed')->count(),
            'revenue' => Consultation::where('astrologer_id', $expert->id)->sum('amount_paid')
        ];

        return view('expert.dashboard', compact('expert', 'consultations', 'stats'));
    }

    public function consultations()
    {
        $expert = Astrologer::where('user_id', Auth::id())->first();
        $consultations = Consultation::where('astrologer_id', $expert->id)->with('user')->latest()->paginate(15);
        
        foreach ($consultations as $consult) {
            if ($consult->user->dob && $consult->user->tob) {
                $consult->astro_data = $this->astrologyService->getHoroscope([
                    'dob' => $consult->user->dob,
                    'tob' => $consult->user->tob
                ]);
            }
        }

        return view('expert.consultations', compact('consultations'));
    }

    public function profile()
    {
        $expert = Astrologer::where('user_id', Auth::id())->first();
        return view('expert.profile', compact('expert'));
    }

    public function updateProfile(Request $request)
    {
        $expert = Astrologer::where('user_id', Auth::id())->first();
        
        $request->validate([
            'name' => 'required',
            'specialization' => 'required',
            'experience' => 'required|numeric',
            'city' => 'required',
            'price_per_minute' => 'required|numeric'
        ]);

        $data = $request->only(['name', 'specialization', 'experience', 'languages', 'bio', 'city', 'price_per_minute']);
        $data['is_online'] = $request->has('is_online');

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('astrologers', 'public');
            $data['profile_image'] = asset('storage/' . $path);
        }

        $expert->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }
}
