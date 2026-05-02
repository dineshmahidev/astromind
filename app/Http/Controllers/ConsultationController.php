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

            // Here you would also deduct amount from user wallet
            // $user = $consultation->user;
            // $user->wallet_balance -= $request->amount;
            // $user->save();
        }

        return response()->json(['success' => true]);
    }
}
