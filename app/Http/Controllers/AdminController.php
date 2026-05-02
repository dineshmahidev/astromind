<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Plan;
use App\Models\Consultation;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'users' => User::where('role', 'user')->count(),
            'astrologers' => User::where('role', 'astrologer')->count(),
            'revenue' => Consultation::sum('amount_paid'),
            'pending_q' => Consultation::where('status', 'pending')->count(),
        ];

        $recent_users = User::latest()->take(5)->get();
        return view('admin.dashboard', compact('stats', 'recent_users'));
    }

    public function publicAstrologers()
    {
        $astrologers = User::where('role', 'astrologer')->latest()->get();
        return view('public.astrologers', compact('astrologers'));
    }

    public function users()
    {
        $users = User::with('astrologer')->latest()->paginate(10);
        return view('admin.users', compact('users'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'wallet_balance' => $request->wallet_balance ?? 0.00,
        ]);

        return back()->with('success', 'User created successfully');
    }

    public function deleteUser($id)
    {
        User::destroy($id);
        return back()->with('success', 'User deleted successfully');
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'wallet_balance' => $request->wallet_balance,
            'is_premium' => $request->has('is_premium'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'User updated successfully');
    }

    public function astrologers()
    {
        $astrologers = \App\Models\Astrologer::with('user')->latest()->get();
        return view('admin.astrologers', compact('astrologers'));
    }

    public function storeAstrologer(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'specialization' => 'required',
            'experience' => 'required|numeric',
            'phone' => 'required',
            'city' => 'required',
            'price_per_minute' => 'required|numeric',
        ]);

        try {
            \DB::beginTransaction();

            // 1. Create User account for login
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'role' => 'astrologer',
            ]);

            // 2. Handle Profile Image Upload
            $profileImagePath = null;
            if ($request->hasFile('profile_image')) {
                $path = $request->file('profile_image')->store('astrologers', 'public');
                $profileImagePath = asset('storage/' . $path);
            } else {
                // Fallback to consistent vector based on ID
                $profileImagePath = 'https://i.pravatar.cc/200?u=' . $user->id;
            }

            // 3. Create detailed Astrologer profile
            \App\Models\Astrologer::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'specialization' => $request->specialization,
                'experience' => $request->experience,
                'languages' => $request->languages ?? 'English, Tamil',
                'bio' => $request->bio ?? 'Professional Astrologer',
                'city' => $request->city,
                'price_per_minute' => $request->price_per_minute,
                'profile_image' => $profileImagePath,
                'is_online' => true,
            ]);

            \DB::commit();
            return back()->with('success', 'Astrologer profile and login credentials created successfully.');
            
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create astrologer: ' . $e->getMessage()])->withInput();
        }
    }

    public function updateAstrologer(Request $request, $id)
    {
        $astrologer = \App\Models\Astrologer::findOrFail($id);
        
        $data = $request->only([
            'name', 'specialization', 'experience', 'price_per_minute', 
            'languages', 'bio', 'city'
        ]);
        
        $data['is_online'] = $request->has('is_online');

        $astrologer->update($data);

        // Update linked user details (Email & Password) if provided
        $user = User::find($astrologer->user_id);
        if ($user) {
            $userUpdateData = [];
            if ($request->filled('email')) {
                $userUpdateData['email'] = $request->email;
            }
            if ($request->filled('password')) {
                $userUpdateData['password'] = Hash::make($request->password);
            }
            if ($request->filled('name')) {
                $userUpdateData['name'] = $request->name;
            }
            
            // Force role to astrologer to ensure dashboard access
            $userUpdateData['role'] = 'astrologer';
            
            if (!empty($userUpdateData)) {
                $user->update($userUpdateData);
            }
        }

        return back()->with('success', 'Astrologer profile updated successfully.');
    }

    public function plans()
    {
        $plans = Plan::all();
        return view('admin.plans', compact('plans'));
    }

    public function storePlan(Request $request)
    {
        Plan::create($request->except('_token'));
        return back()->with('success', 'Plan created successfully');
    }

    public function consultations()
    {
        $consultations = Consultation::with(['user', 'astrologer'])->latest()->get();
        return view('admin.consultations', compact('consultations'));
    }

    public function payments()
    {
        $transactions = \App\Models\Transaction::with('user')->latest()->paginate(20);
        return view('admin.payments', compact('transactions'));
    }

    public function settings()
    {
        $settings = \App\Models\Setting::all()->pluck('value', 'key');
        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        foreach ($request->except('_token') as $key => $value) {
            \App\Models\Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        return back()->with('success', 'Settings updated successfully');
    }
}
