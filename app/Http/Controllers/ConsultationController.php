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
     * Get messages between user and astrologer (mock).
     */
    public function getMessages(Request $request)
    {
        $astrologerId = $request->astrologer_id;

        return response()->json([
            'success' => true,
            'messages' => [
                ['id' => 1, 'sender' => 'astrologer', 'type' => 'text', 'content' => 'Vanakkam! How can I help you today?', 'time' => '10:00 AM'],
                ['id' => 2, 'sender' => 'user', 'type' => 'text', 'content' => 'I have a question about my career.', 'time' => '10:01 AM'],
            ]
        ]);
    }

    /**
     * Initiate a call request.
     */
    public function initiateCall(Request $request)
    {
        $type = $request->type; // 'audio' or 'video'
        $astrologerId = $request->astrologer_id;
        $channelName = 'call_' . $astrologerId . '_' . time();

        // In production, broadcast a Pusher event here to notify the astrologer
        // event(new CallInitiated($channelName, $type, $astrologerId));

        return response()->json([
            'success' => true,
            'channel' => $channelName,
            'type' => $type,
            'message' => 'Call request sent to astrologer. Please wait...',
        ]);
    }
    /**
     * Submit a question to an astrologer.
     */
    public function submitQuestion(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'astrologer_id' => 'required',
            'question' => 'required',
            'amount' => 'required|numeric',
        ]);

        $consultation = \App\Models\Consultation::create([
            'user_id' => $request->user_id,
            'astrologer_id' => $request->astrologer_id,
            'question' => $request->question,
            'amount_paid' => $request->amount,
            'status' => ($request->is_video_call || $request->is_audio_call) ? 'active' : 'pending',
            'is_video_call' => $request->is_video_call ?? false,
            'is_audio_call' => $request->is_audio_call ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Question sent successfully to the astrologer.',
            'consultation' => $consultation
        ]);
    }
}
