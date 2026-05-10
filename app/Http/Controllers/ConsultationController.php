<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\AgoraTokenService;

class ConsultationController extends Controller
{
    /**
     * Generate a real Agora RTC Token for audio/video call.
     */
    public function generateCallToken(Request $request)
    {
        $channelName = $request->channel ?? 'astromind_' . time();
        $uid = $request->uid ?? 0;
        $role = AgoraTokenService::ROLE_PUBLISHER;
        $expireTs = time() + 3600; // Token valid for 1 hour

        $token = AgoraTokenService::buildTokenWithUid(
            env('AGORA_APP_ID'),
            env('AGORA_APP_CERTIFICATE'),
            $channelName,
            $uid,
            $role,
            $expireTs
        );

        return response()->json([
            'success'  => true,
            'appId'    => env('AGORA_APP_ID'),
            'channel'  => $channelName,
            'token'    => $token,
            'uid'      => $uid,
            'expires'  => $expireTs,
        ]);
    }

    /**
     * Upload an image file for chat.
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB limit
        ]);

        $path = $request->file('image')->store('chat_media', 'public');
        $url = Storage::url($path);

        return response()->json([
            'success' => true,
            'url' => config('app.url') . $url
        ]);
    }

    /**
     * Upload a voice message file.
     */
    public function uploadVoiceMessage(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,m4a,aac,ogg|max:10240',
            'astrologer_id' => 'required|string',
        ]);

        $path = $request->file('audio')->store('voice_messages', 'public');
        $url = Storage::url($path);

        return response()->json([
            'success' => true,
            'url' => config('app.url') . $url,
            'duration' => $request->duration ?? 0,
        ]);
    }

    /**
     * Save a message to the database.
     */
    public function saveMessage(Request $request)
    {
        $request->validate([
            'sender_id' => 'required',
            'receiver_id' => 'required',
            'content' => 'required',
            'type' => 'required|string',
        ]);

        $message = \App\Models\Message::create([
            'consultation_id' => $request->consultation_id,
            'sender_id' => $request->sender_id,
            'receiver_id' => $request->receiver_id,
            'type' => $request->type,
            'content' => $request->content,
            'duration' => $request->duration,
        ]);

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function getConsultationMessages(Request $request)
    {
        $consultationId = $request->consultation_id;
        $messages = \App\Models\Message::where('consultation_id', $consultationId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['success' => true, 'messages' => $messages]);
    }

    /**
     * Get full consultation detail with user astro profile for astrologer.
     */
    public function getConsultationDetail(Request $request)
    {
        $consultationId = $request->consultation_id;
        $consultation = \App\Models\Consultation::with(['user', 'messages'])->find($consultationId);

        if (!$consultation) {
            return response()->json(['success' => false, 'message' => 'Consultation not found']);
        }

        $user = $consultation->user;
        $astroProfile = null;
        $kattam = null;
        $dashaTimeline = null;

        if ($user && $user->dob && $user->tob) {
            // Parse DOB and TOB
            try {
                $dob = \Carbon\Carbon::parse($user->dob);
                $tob = \Carbon\Carbon::parse($user->tob);

                $astroProfile = [
                    'name' => $user->name,
                    'dob' => $user->dob,
                    'tob' => $user->tob,
                    'rasi' => $user->rasi ?? 'Unknown',
                    'nakshatra' => $user->nakshatra ?? 'Unknown',
                    'padam' => $user->padam ?? 'Unknown',
                    'age' => $dob->age,
                ];

                // Generate Full Details using AstrologyService
                $astroService = app(\App\Services\AstrologyService::class);
                try {
                    $fullDetails = $astroService->getFullDetails(
                        $dob->day, $dob->month, $dob->year,
                        $tob->hour, $tob->minute, 0,
                        'en', 11.3410, 77.7172
                    );
                    
                    // Transform 'chart' into 12-house array for UI
                    $kattam = array_fill(0, 12, []);
                    if (isset($fullDetails['chart'])) {
                        foreach ($fullDetails['chart'] as $planet => $info) {
                            $rasiIdx = $info['rasi'];
                            if ($rasiIdx >= 0 && $rasiIdx < 12) {
                                $kattam[$rasiIdx][] = $planet;
                            }
                        }
                    }
                    
                    // Identify Current Dasha and Bhukti
                    $now = now();
                    $currentDasha = null;
                    $currentBhukti = null;
                    
                    if (isset($fullDetails['dasha'])) {
                        foreach ($fullDetails['dasha'] as $d) {
                            $dStart = \Carbon\Carbon::parse($d['start']);
                            $dEnd = \Carbon\Carbon::parse($d['end']);
                            
                            if ($now->between($dStart, $dEnd)) {
                                $currentDasha = $d['planet'];
                                if (isset($d['bhuktis'])) {
                                    foreach ($d['bhuktis'] as $b) {
                                        $bStart = \Carbon\Carbon::parse($b['start']);
                                        $bEnd = \Carbon\Carbon::parse($b['end']);
                                        if ($now->between($bStart, $bEnd)) {
                                            $currentBhukti = $b['planet'];
                                            break;
                                        }
                                    }
                                }
                                break;
                            }
                        }
                    }

                    $astroProfile['current_dasha'] = $currentDasha;
                    $astroProfile['current_bhukti'] = $currentBhukti;
                    $astroProfile['lagnam'] = $fullDetails['summary']['lagna'] ?? null;
                    $astroProfile['tithi'] = $fullDetails['summary']['tithi_name'] ?? null;
                    $astroProfile['yoga'] = $fullDetails['summary']['yoga'] ?? null;
                    $astroProfile['karana'] = $fullDetails['summary']['karana'] ?? null;
                    
                    $dashaTimeline = $fullDetails['dasha'] ?? [];
                } catch (\Throwable $e) {
                    \Log::error('Astro detail generation error: ' . $e->getMessage());
                }
            } catch (\Throwable $e) {
                \Log::error('Astro profile error: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'consultation' => [
                'id' => $consultation->id,
                'question' => $consultation->question,
                'status' => $consultation->status,
                'call_type' => $consultation->call_type,
                'amount_paid' => $consultation->amount_paid,
                'created_at' => $consultation->created_at,
                'user_avatar' => $consultation->user->avatar ?? null,
                'expert_avatar' => $consultation->expert->avatar ?? null,
            ],
            'user_profile' => $astroProfile,
            'kattam' => $kattam,
            'dasha_timeline' => $dashaTimeline,
            'messages' => $consultation->messages,
        ]);
    }

    /**
     * Get chat history between two users.
     */
    public function getChatHistory(Request $request)
    {
        $userId = $request->user_id;
        $astrologerId = $request->astrologer_id;

        $messages = \App\Models\Message::where(function($q) use ($userId, $astrologerId) {
            $q->where('sender_id', $userId)->where('receiver_id', $astrologerId);
        })->orWhere(function($q) use ($userId, $astrologerId) {
            $q->where('sender_id', $astrologerId)->where('receiver_id', $userId);
        })->orderBy('created_at', 'asc')->get();

        return response()->json(['success' => true, 'messages' => $messages]);
    }

    /**
     * End a consultation and save duration/amount.
     */
    public function endConsultation(Request $request)
    {
        $request->validate([
            'consultation_id' => 'required',
            'duration' => 'required|integer',
            'amount' => 'required|numeric',
        ]);

        $consultation = \App\Models\Consultation::find($request->consultation_id);
        if ($consultation) {
            $consultation->duration = $request->duration;
            $consultation->amount_paid = $request->amount;
            $consultation->status = 'closed';
            $consultation->end_time = now();
            $consultation->save();

            // Credit Expert Wallet (Expert gets 80%, Admin keeps 20%)
            $expertAmount = $request->amount * 0.80;
            $expert = \App\Models\Astrologer::find($consultation->astrologer_id);
            if ($expert) {
                $expert->increment('wallet_balance', $expertAmount);
                
                \App\Models\ExpertTransaction::create([
                    'astrologer_id' => $expert->id,
                    'amount' => $expertAmount,
                    'type' => 'credit',
                    'description' => "Consultation with " . ($consultation->user->name ?? 'User') . " (#" . $consultation->id . ")",
                    'status' => 'completed'
                ]);
            }

            // Deduct amount from user wallet
            $user = \App\Models\User::find($consultation->user_id);
            if ($user) {
                $user->decrement('wallet_balance', $request->amount);
            }
        }

        return response()->json(['success' => true]);
    }
    /**
     * Start a new consultation (Chat/Call).
     */
    public function startConsultation(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'astrologer_id' => 'required',
            'question' => 'required',
            'amount' => 'required',
        ]);

        // Create Consultation
        $consultation = \App\Models\Consultation::create([
            'user_id' => $request->user_id,
            'astrologer_id' => $request->astrologer_id,
            'status' => 'open',
            'start_time' => now(),
            'is_video_call' => $request->is_video_call ?? false,
            'is_audio_call' => $request->is_audio_call ?? false,
            'call_type' => $request->is_video_call ? 'video' : ($request->is_audio_call ? 'audio' : 'chat'),
            'amount_paid' => $request->amount,
            'question' => $request->question
        ]);

        // Save Question as first message
        \App\Models\Message::create([
            'consultation_id' => $consultation->id,
            'sender_id' => $request->user_id,
            'receiver_id' => $request->astrologer_id,
            'type' => 'text',
            'content' => $request->question
        ]);

        // Credit Expert Wallet immediately (Expert gets 80%, Admin keeps 20%)
        $expertAmount = $request->amount * 0.80;
        $expert = \App\Models\Astrologer::find($request->astrologer_id);
        if ($expert) {
            $expert->increment('wallet_balance', $expertAmount);
            
            \App\Models\ExpertTransaction::create([
                'astrologer_id' => $expert->id,
                'amount' => $expertAmount,
                'type' => 'credit',
                'description' => "Consultation fee from " . (\App\Models\User::find($request->user_id)->name ?? 'User') . " (#" . $consultation->id . ")",
                'status' => 'completed'
            ]);
        }

        return response()->json([
            'success' => true, 
            'consultation' => $consultation->load('user'),
            'expert_credited' => $expertAmount
        ]);
    }
    public function finishConsultation(Request $request)
    {
        $request->validate([
            'consultation_id' => 'required',
            'rating' => 'integer',
            'review' => 'string'
        ]);

        $consultation = \App\Models\Consultation::find($request->consultation_id);
        if ($consultation && $consultation->status !== 'closed') {
            $consultation->status = 'closed';
            $consultation->rating = $request->rating;
            $consultation->review = $request->review;
            $consultation->save();

            // Calculate Split (20% Commission for Admin, 80% for Expert)
            $totalAmount = $consultation->amount_paid ?? 0;
            $adminCommission = $totalAmount * 0.20;
            $expertAmount = $totalAmount - $adminCommission;

            // Update Consultation Record with commission details
            $consultation->admin_commission = $adminCommission;
            $consultation->expert_amount = $expertAmount;
            $consultation->save();

            $expert = \App\Models\Astrologer::find($consultation->astrologer_id);
            if ($expert) {
                \Log::info("Consultation #{$consultation->id}: Total ₹{$totalAmount}, Expert gets ₹{$expertAmount}, Admin Commission ₹{$adminCommission}");
                $expert->increment('wallet_balance', $expertAmount);
                
                \App\Models\ExpertTransaction::create([
                    'astrologer_id' => $expert->id,
                    'amount' => $expertAmount,
                    'type' => 'credit',
                    'description' => "Consultation completed (#{$consultation->id}) - 80% Share",
                    'status' => 'completed'
                ]);
            } else {
                \Log::warning("Expert not found for consultation: " . $consultation->id . " (astrologer_id: " . $consultation->astrologer_id . ")");
            }
        }

        return response()->json(['success' => true]);
    }

    public function markAsRead(Request $request)
    {
        $consultationId = $request->consultation_id;
        $userId = $request->user_id;

        \App\Models\Message::where('consultation_id', $consultationId)
            ->where('receiver_id', $userId)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        return response()->json(['success' => true]);
    }

    public function getUserHistory(Request $request)
    {
        $userId = $request->user_id;
        
        $consultations = \App\Models\Consultation::where('user_id', $userId)
            ->with('expert')
            ->orderBy('created_at', 'desc')
            ->get();

        $transactions = \App\Models\Transaction::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'consultations' => $consultations,
            'transactions' => $transactions
        ]);
    }
}
