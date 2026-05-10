<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AstrologyService;
use Illuminate\Support\Facades\DB;

class AIController extends Controller
{
    protected $astrologyService;

    public function __construct(AstrologyService $astrologyService)
    {
        $this->astrologyService = $astrologyService;
    }

    public function chat(Request $request)
    {
        $message = strtolower($request->message);
        $userContext = $request->user_context; 
        $lang = $request->lang ?? 'en';

        $name = $userContext['name'] ?? 'User';
        $rasi = $userContext['rasi'] ?? 'Unknown';
        $nakshatra = $userContext['nakshatra'] ?? 'Unknown';
        $rasiIdx = $userContext['rasi_idx'] ?? null;
        $userId = $userContext['id'] ?? null;

        // Fallback: If rasi_idx is missing but birth details exist, calculate it on the fly
        if ($rasiIdx === null && isset($userContext['day'])) {
            $calc = $this->astrologyService->getDetails(
                (int)$userContext['day'], (int)$userContext['month'], (int)$userContext['year'],
                (int)$userContext['hour'], (int)$userContext['minute'], 0
            );
            $rasiIdx = $calc['rasi_idx'];
            $rasi = $calc['rasi'];
            $nakshatra = $calc['nakshatra'];
        }

        $dob = $userContext['dob'] ?? ($userContext['date'] ?? 'Unknown');
        $tob = $userContext['tob'] ?? ($userContext['time'] ?? 'Unknown');
        $pob = $userContext['pob'] ?? ($userContext['place'] ?? 'Unknown');

        if ($rasiIdx !== null) {
            $calcData = $this->astrologyService->getHoroscope($rasiIdx, 'daily', $lang);
            $rasi = $calcData['sign'];
        }

        $grokKey = DB::table('settings')->where('key', 'grok_api_key')->first();
        $reply = null;
        $source = 'fallback';

        if ($grokKey && !empty($grokKey->value)) {
            try {
                $client = new \GuzzleHttp\Client();
                $systemPrompt = "You are AstroMind, a professional Vedic Astrologer. Give answers that are **brief, direct, and highly relevant**. " .
                               "Context: Name: {$name}, DOB: {$dob}, TOB: {$tob}, POB: {$pob}, Rasi: {$rasi}, Nakshatra: {$nakshatra}. " .
                               "Always reply in " . ($lang == 'ta' ? 'Tamil (தமிழ்)' : ($lang == 'hi' ? 'Hindi (हिन्दी)' : 'English')) . ".";
                
                $res = $client->post('https://api.groq.com/openai/v1/chat/completions', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $grokKey->value,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'model' => 'llama-3.3-70b-versatile',
                        'messages' => [
                            ['role' => 'system', 'content' => $systemPrompt],
                            ['role' => 'user', 'content' => $message]
                        ],
                        'temperature' => 0.7,
                        'max_tokens' => 1024
                    ],
                    'timeout' => 20
                ]);
                
                $aiResult = json_decode($res->getBody()->getContents(), true);
                $reply = $aiResult['choices'][0]['message']['content'] ?? null;
                $source = 'groq';
            } catch (\Exception $e) {
                \Log::error('Groq AI Failed: ' . $e->getMessage());
            }
        }

        if (!$reply) {
            if (str_contains($message, 'horoscope') || str_contains($message, 'day today') || str_contains($message, 'ராசிபலன்') || str_contains($message, 'राशिफल')) {
                if ($rasiIdx !== null) {
                    $data = $this->astrologyService->getHoroscope($rasiIdx, 'daily', $lang);
                    $reply = $data['prediction'] . " " . $data['transit_analysis'];
                    $reply .= ($lang == 'ta') ? " இன்று உங்கள் அதிர்ஷ்ட நிறம்: " : (($lang == 'hi') ? " आज आपका शुभ रंग है: " : " Your lucky color today is: ");
                    $reply .= $data['remedies']['color'] . ".";
                } else {
                    $reply = ($lang == 'ta') ? "மன்னிக்கவும், உங்கள் ராசி விவரங்கள் கிடைக்கவில்லை." : (($lang == 'hi') ? "क्षमा करें, आपकी राशि का विवरण नहीं मिला।" : "Sorry, I couldn't find your Rasi details to calculate your horoscope.");
                }
            } 
            else if (str_contains($message, 'nakshatra') || str_contains($message, 'star') || str_contains($message, 'நட்சத்திரம்') || str_contains($message, 'नक्षत्र')) {
                $reply = ($lang == 'ta') 
                    ? "உங்கள் நட்சத்திரம் {$nakshatra}. இது மிகவும் சக்திவாய்ந்த நட்சத்திரம். இன்று நீங்கள் மந்திரிப்பதைத் தவிர்க்கவும்." 
                    : (($lang == 'hi') ? "आपका नक्षत्र {$nakshatra} है। यह बहुत ही प्रभावशाली नक्षत्र है। आज आपको सावधानी बरतने की सलाह दी जाती है।" : "Your Nakshatra is {$nakshatra}. It is a highly significant star in your birth chart. Today's planetary alignment suggests caution in financial decisions.");
            }
            else if (str_contains($message, 'business') || str_contains($message, 'travel') || str_contains($message, 'வணிக') || str_contains($message, 'व्यापार')) {
                if ($rasiIdx !== null) {
                    $data = $this->astrologyService->getHoroscope($rasiIdx, 'daily', $lang);
                    $reply = ($lang == 'ta') 
                        ? "வணிகத்திற்கு இன்று நல்ல நேரம்: {$data['timings']['rahu']} தவிர்த்து மற்ற நேரங்கள்." 
                        : (($lang == 'hi') ? "व्यापार के लिए आज का शुभ समय: राहु काल ({$data['timings']['rahu']}) को छोड़कर।" : "Good time for business/travel: Avoid Rahu Kaalam ({$data['timings']['rahu']}) for better results.");
                }
            }
            else if (str_contains($message, 'remedies') || str_contains($message, 'color') || str_contains($message, 'பரிகாரம்') || str_contains($message, 'उपचार') || str_contains($message, 'நிறம்') || str_contains($message, 'रंग')) {
                if ($rasiIdx !== null) {
                    $data = $this->astrologyService->getHoroscope($rasiIdx, 'daily', $lang);
                    $reply = ($lang == 'ta')
                        ? "உங்களுக்கான இன்றைய பரிகாரம்: {$data['remedies']['mantra']} மந்திரத்தை உச்சரிக்கவும். மேலும் {$data['remedies']['ritual']}."
                        : (($lang == 'hi') ? "आज के लिए अनुशंसित उपाय: '{$data['remedies']['mantra']}' का जाप करें और {$data['remedies']['ritual']}।" : "Recommended remedy for today: Recite '{$data['remedies']['mantra']}' and {$data['remedies']['ritual']}.");
                }
            }
            else {
                $responses = [
                    'en' => "Hello {$name}, based on your {$rasi} rasi, the celestial bodies are aligning to bring positive energy your way. Feel free to ask more specific questions about your career, love, or health.",
                    'ta' => "வணக்கம் {$name}, உங்கள் {$rasi} ராசியின்படி, இன்று உங்களுக்கு நேர்மறையான ஆற்றல் கிடைக்கும். உங்கள் தொழில், காதல் அல்லது ஆரோக்கியம் பற்றி மேலும் கேட்கலாம்.",
                    'hi' => "नमस्ते {$name}, आपकी {$rasi} राशि के आधार पर, आज आपको सकारात्मक ऊर्जा मिलेगी। आप अपने करियर, प्यार या स्वास्थ्य के बारे में और भी सवाल पूछ सकते हैं।"
                ];
                $reply = $responses[$lang] ?? $responses['en'];
            }
        }

        DB::table('ai_chat_logs')->insert([
            'user_id' => $userId,
            'message' => $request->message,
            'reply' => $reply,
            'lang' => $lang,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'reply' => $reply,
            'source' => $source
        ]);
    }

    public function syncQuota(Request $request)
    {
        $userId = $request->user_id;
        $usedSeconds = $request->used_seconds;
        $date = now()->toDateString();

        if (!$userId) return response()->json(['success' => false, 'message' => 'User ID missing']);

        DB::table('user_ai_quotas')->updateOrInsert(
            ['user_id' => $userId, 'date' => $date],
            ['used_seconds' => $usedSeconds, 'updated_at' => now()]
        );

        return response()->json(['success' => true]);
    }
}
