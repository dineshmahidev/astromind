<?php
namespace App\Services;

class MarriageService
{
    private $rasis = [
        'en' => ['Mesha','Vrishabha','Mithuna','Karka','Simha','Kanni','Tula','Vrischika','Dhanusu','Makara','Kumbha','Meena'],
        'ta' => ['மேஷம்','ரிஷபம்','மிதுனம்','கடகம்','சிம்மம்','கன்னி','துலாம்','விருச்சிகம்','தனுசு','மகரம்','கும்பம்','மீனம்'],
        'hi' => ['मेष','वृषभ','मिथुन','कर्क','सिंह','कन्या','तुला','वृश्चिक','धनु','मकर','कुंभ','मीन']
    ];

    private $rasiLords = ['Mars','Venus','Mercury','Moon','Sun','Mercury','Venus','Mars','Jupiter','Saturn','Saturn','Jupiter'];

    public function predict(array $pp, int $lagnaIdx, string $gender = 'male', string $lang = 'en'): array
    {
        $seventhRasi  = ($lagnaIdx + 6) % 12;
        $seventhLord  = $this->rasiLords[$seventhRasi];
        $seventhLordH = $this->house($pp[$seventhLord] ?? 0, $lagnaIdx);
        $venusH       = $this->house($pp['Venus']   ?? 0, $lagnaIdx);
        $jupiterH     = $this->house($pp['Jupiter'] ?? 0, $lagnaIdx);
        $saturnH      = $this->house($pp['Saturn']  ?? 0, $lagnaIdx);
        $rahuH        = $this->house($pp['Rahu']    ?? 0, $lagnaIdx);
        $ketuH        = $this->house(fmod(($pp['Rahu'] ?? 0) + 180, 360), $lagnaIdx);
        $marsH        = $this->house($pp['Mars']    ?? 0, $lagnaIdx);
        $fifthLord    = $this->rasiLords[($lagnaIdx + 4) % 12];
        $fifthLordH   = $this->house($pp[$fifthLord] ?? 0, $lagnaIdx);

        // Promises Logic (Remains mostly internal, but reasons are translated)
        $promised = []; $denied = []; $delayed = [];
        $pLord = $this->transP($seventhLord, $lang);

        if ($lang == 'ta') {
            if (in_array($venusH, [1,7])) $promised[] = "சுக்கிரன் {$venusH}-ம் வீட்டில் உள்ளார் - திருமண யோகம் வலுவாக உள்ளது.";
            if (in_array($jupiterH, [1,7])) $promised[] = "குரு பகவான் {$jupiterH}-ம் வீட்டில் இருப்பது திருமணத்திற்கு மங்களகரமானது.";
            if (!in_array($seventhLordH, [6,8,12])) $promised[] = "7-ம் அதிபதி {$pLord} {$seventhLordH}-ம் வீட்டில் அமர்ந்து திருமணத்தை உறுதி செய்கிறார்.";
            if ($ketuH == 7) $denied[] = "7-ல் கேது இருப்பது திருமணத்தடை ஏற்படுத்தும்.";
            if ($rahuH == 7) $denied[] = "7-ல் ராகு - கலப்பு திருமணம் அல்லது மாற்றார் மூலம் திருமணம்.";
            if (in_array($seventhLordH,[6,8,12])) $denied[] = "7-ம் அதிபதி {$pLord} மறைவு பெற்றுள்ளது திருமணத்தை பலவீனப்படுத்துகிறது.";
            if (in_array($saturnH,[7,1,9])) $delayed[] = "சனி பகவான் {$saturnH}-ல் இருப்பது திருமணத்தை தாமதப்படுத்தும்.";
        } else if ($lang == 'hi') {
            if (in_array($venusH, [1,7])) $promised[] = "शुक्र {$venusH}वें भाव में है - विवाह योग बहुत प्रबल है।";
            if (in_array($jupiterH, [1,7])) $promised[] = "गुरु का {$jupiterH}वें भाव में होना विवाह के लिए शुभ है।";
            if (!in_array($seventhLordH, [6,8,12])) $promised[] = "7वें भाव का स्वामी {$pLord} {$seventhLordH}वें भाव में है, जो विवाह सुनिश्चित करता है।";
            if ($ketuH == 7) $denied[] = "7वें भाव में केतु विवाह में बाधाएँ उत्पन्न करता है।";
            if ($rahuH == 7) $denied[] = "7वें भाव में राहु - अपरंपरागत विवाह के संकेत।";
            if (in_array($seventhLordH,[6,8,12])) $denied[] = "7वें भाव का स्वामी {$pLord} कमजोर स्थिति में है।";
            if (in_array($saturnH,[7,1,9])) $delayed[] = "शनि का {$saturnH}वें भाव में होना विवाह में देरी का संकेत है।";
        } else {
            if (in_array($venusH, [1,7])) $promised[] = "Venus in {$this->ord($venusH)} house — marriage strongly promised.";
            if (in_array($jupiterH, [1,7])) $promised[] = "Jupiter in {$this->ord($jupiterH)} house — auspicious for marriage.";
            if (!in_array($seventhLordH, [6,8,12])) $promised[] = "7th lord {$seventhLord} in {$this->ord($seventhLordH)} — marriage at right time.";
            if ($ketuH == 7) $denied[] = "Ketu in 7th creates obstacles.";
            if ($rahuH == 7) $denied[] = "Rahu in 7th — unconventional marriage.";
            if (in_array($seventhLordH,[6,8,12])) $denied[] = "7th lord {$seventhLord} in {$this->ord($seventhLordH)} weakens marriage.";
            if (in_array($saturnH,[7,1,9])) $delayed[] = "Saturn in {$this->ord($saturnH)} — marriage delayed.";
        }

        // Love Score
        $loveScore = 0;
        if (in_array($venusH, [1,5,7,11])) $loveScore += 3;
        if ($rahuH == 7) $loveScore += 2;
        if ($fifthLordH == $seventhLordH) $loveScore += 2;

        $mangal = in_array($marsH, [1,2,4,7,8,12]);
        $mls = 70;
        if (count($denied) > 0) $mls -= 15;
        if ($mangal) $mls -= 10;
        if (count($promised) > 2) $mls += 10;

        return [
            'lagna_rasi'       => $this->rasis[$lang][$lagnaIdx] ?? $this->rasis['en'][$lagnaIdx],
            'seventh_house'    => $this->rasis[$lang][$seventhRasi] ?? $this->rasis['en'][$seventhRasi],
            'seventh_lord'     => $seventhLord,
            'promised_marriage'=> count($promised) > count($denied),
            'promised_reasons' => $promised,
            'denied_reasons'   => $denied,
            'delayed_reasons'  => $delayed,
            'is_delayed'       => count($delayed) > 0,
            'love_or_arranged' => $this->transL($loveScore >= 3 ? 'Love' : 'Arranged', $lang),
            'love_confidence'  => min(95, 50 + $loveScore * 8),
            'mangal_dosha'     => $mangal,
            'mangal_severity'  => $mangal ? (in_array($marsH,[7,8]) ? ($lang == 'ta' ? 'அதிகம்' : 'High') : ($lang == 'ta' ? 'மிதமான' : 'Moderate')) : 'None',
            'mangal_dosha_house'=> $marsH,
            'marriage_timing'  => $this->timing($pp, $lagnaIdx, $lang),
            'spouse_nature'    => $this->spouse($seventhLord, $seventhLordH, $lang),
            'married_life_score'=> max(20, min(98, $mls)),
            'remedies'         => $this->remedies($mangal, $denied, $delayed, $lang),
        ];
    }

    public function getPeakMarriageDays(int $bDay, int $bMonth, int $bYear, float $moonSidereal, string $seventhLord, string $lang = 'en'): array
    {
        $astro = new AstrologyService();
        $nLord = ['Ketu','Venus','Sun','Moon','Mars','Rahu','Jupiter','Saturn','Mercury',
                  'Ketu','Venus','Sun','Moon','Mars','Rahu','Jupiter','Saturn','Mercury','Ketu',
                  'Venus','Sun','Moon','Mars','Rahu','Jupiter','Saturn','Mercury'];
        $dYears = ['Ketu'=>7,'Venus'=>20,'Sun'=>6,'Moon'=>10,'Mars'=>7,'Rahu'=>18,'Jupiter'=>16,'Saturn'=>19,'Mercury'=>17];
        $dOrder = ['Ketu','Venus','Sun','Moon','Mars','Rahu','Jupiter','Saturn','Mercury'];
        $marriageDashas = array_unique(['Venus','Jupiter',$seventhLord]);

        $starIdx = (int)($moonSidereal / (360/27));
        $startIdx = array_search($nLord[$starIdx], $dOrder);
        $balance = 1 - fmod($moonSidereal, 360/27) / (360/27);

        $cur = new \DateTime("{$bYear}-{$bMonth}-{$bDay}");
        $dashas = [];
        for ($i = 0; $i < 9; $i++) {
            $p = $dOrder[($startIdx + $i) % 9];
            $yrs = $i === 0 ? $dYears[$p] * $balance : $dYears[$p];
            $days = (int)round($yrs * 365.25);
            $s = clone $cur; $cur->modify("+{$days} days"); $e = clone $cur;
            $dashas[] = ['planet'=>$p,'start'=>$s,'end'=>$e,'start_str'=>$s->format('M Y'),'end_str'=>$e->format('M Y')];
        }

        $now = new \DateTime('now'); $window = null; $mDasha = null;
        foreach ($dashas as $d) {
            if (in_array($d['planet'], $marriageDashas) && $d['end'] > $now) {
                $mDasha = $d; $window = ['start'=>($d['start']<$now?$now:$d['start']),'end'=>$d['end']]; break;
            }
        }
        if (!$window) $window = ['start'=>$now,'end'=>(clone $now)->modify('+3 years')];

        $scored = []; $scanD = clone $window['start']; $scanEnd = (clone $window['start'])->modify('+5 years');
        if ($scanEnd > $window['end']) $scanEnd = clone $window['end'];

        // Translated names for days/stars/etc.
        $transStars = [
            'en' => ['Aswini','Bharani','Karthigai','Rohini','Mirugaseerisham','Thiruvathirai','Punarpusam','Poosam','Ayilyam','Magam','Pooram','Uthiram','Hastham','Chithirai','Swathi','Visakam','Anusham','Kettai','Moolam','Pooradam','Uthiradam','Thiruvonam','Avittam','Sadhayam','Pooratathi','Uthiratathi','Revathi'],
            'ta' => ['அஸ்வினி','பரணி','கார்த்திகை','ரோகிணி','மிருகசீரிஷம்','திருவாதிரை','புனர்பூசம்','பூசம்','ஆயில்யம்','மகம்','பூரம்','உத்திரம்','அஸ்தம்','சித்திரை','சுவாதி','விசாகம்','அனுஷம்','கேட்டை','மூலம்','பூராடம்','உத்திராடம்','திருவோணம்','அவிட்டம்','சதயம்','பூரட்டாதி','உத்திரட்டாதி','ரேவதி'],
            'hi' => ['अश्विनी','भरणी','कृत्तिका','रोहिणी','मृगशिरा','आर्द्रा','पुनर्वसु','पुष्य','श्लेषा','मघा','पूर्वा फाल्गुनी','उत्तरा फाल्गुनी','हस्त','चित्रा','स्वाती','विशाखा','अनुराधा','ज्येष्ठा','मूल','पूर्वाषाढ़ा','उत्तराषाढ़ा','श्रवण','धनिष्ठा','शतभिषा','पूर्वाभाद्रपद','उत्तराभाद्रपद','रेवती']
        ];
        $transDays = [
            'en' => ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
            'ta' => ['ஞாயிறு','திங்கள்','செவ்வாய்','புதன்','வியாழன்','வெள்ளி','சனி'],
            'hi' => ['रविवार','सोमवार','मंगलवार','बुधवार','गुरुवार','शुक्रवार','शनिवार']
        ];

        while ($scanD <= $scanEnd) {
            $jd = $astro->getJulianDay((int)$scanD->format('j'),(int)$scanD->format('n'),(int)$scanD->format('Y'),10,0,0);
            $ay = $astro->getLahiriAyanamsa($jd);
            $mn = fmod($astro->estimateMoonLongitude($jd) - $ay + 360, 360);
            $sn = fmod($astro->getSunLong($jd) - $ay + 360, 360);
            $ti = (int)(fmod($mn - $sn + 360, 360)/12)+1;
            $va = ((int)($jd+1.5))%7;
            $si = (int)($mn/(360/27));
            $yi = (int)(fmod($sn+$mn,360)/(360/27));

            $sc = 0; // Scoring (Simplified for brevity but keep existing logic)
            if (in_array($ti,[2,3,5,7,10,11,13])) $sc+=30; if (in_array($va,[1,3,4,5])) $sc+=25;
            if (in_array($si,[3,4,9,11,12,14,16,20,25,26])) $sc+=25;
            if ($sc >= 55) {
                $scored[] = [
                    'score' => $sc,
                    'date' => $scanD->format('d M Y'),
                    'day' => $transDays[$lang][$va] ?? $scanD->format('l'),
                    'tithi' => "Tithi $ti",
                    'nakshatra' => $transStars[$lang][$si] ?? $transStars['en'][$si],
                    'quality' => $sc>=80 ? '⭐⭐⭐' : '⭐⭐',
                    'quality_color' => $sc>=80 ? '#00b894' : '#6c5ce7',
                    'dasha_context' => $this->transP($mDasha['planet'], $lang) . ($lang=='ta' ? ' திசை' : ' Dasha')
                ];
            }
            $scanD->modify('+1 day');
        }

        usort($scored, fn($a,$b) => $b['score']-$a['score']);
        return [
            'marriage_dasha' => $mDasha ? [
                'planet' => $this->transP($mDasha['planet'], $lang),
                'start' => $mDasha['start_str'], 'end' => $mDasha['end_str'],
                'message' => $lang=='ta' ? "உங்கள் திருமணம் {$this->transP($mDasha['planet'],$lang)} திசையில் அமையும்." : ($lang=='hi' ? "आपका विवाह {$this->transP($mDasha['planet'],$lang)} दशा में होगा।" : "Marriage destined during {$mDasha['planet']} Dasha.")
            ] : null,
            'dasha_timeline' => array_map(fn($d)=>[
                'planet' => $this->transP($d['planet'], $lang),
                'start' => $d['start_str'], 'end' => $d['end_str'],
                'is_marriage_dasha' => in_array($d['planet'], $marriageDashas)
            ], $dashas),
            'peak_dates' => array_slice($scored, 0, 12)
        ];
    }

    private function house(float $long, int $lagnaIdx): int { return ((int)($long/30) - $lagnaIdx + 12) % 12 + 1; }

    private function transP(string $p, string $lang): string {
        $m = [
            'ta' => ['Sun'=>'சூரியன்','Moon'=>'சந்திரன்','Mars'=>'செவ்வாய்','Mercury'=>'புதன்','Jupiter'=>'குரு','Venus'=>'சுக்கிரன்','Saturn'=>'சனி','Rahu'=>'ராகு','Ketu'=>'கேது'],
            'hi' => ['Sun'=>'सूर्य','Moon'=>'चंद्र','Mars'=>'मंगल','Mercury'=>'बुध','Jupiter'=>'गुरु','Venus'=>'शुक्र','Saturn'=>'शनि','Rahu'=>'राहु','Ketu'=>'केतु']
        ];
        return $m[$lang][$p] ?? $p;
    }

    private function transL(string $l, string $lang): string {
        if ($lang == 'ta') return $l == 'Love' ? 'காதல் திருமணம்' : 'நிச்சயிக்கப்பட்ட திருமணம்';
        if ($lang == 'hi') return $l == 'Love' ? 'प्रेम विवाह' : 'व्यवस्थित विवाह';
        return $l . ' Marriage';
    }

    private function spouse(string $lord, int $house, string $lang): array {
        $traits = [
            'en' => ['Sun'=>['Confident','Leader'],'Moon'=>['Caring','Emotional'],'Mars'=>['Bold','Energetic'],'Mercury'=>['Witty','Smart'],'Jupiter'=>['Wise','Educated'],'Venus'=>['Beautiful','Charming'],'Saturn'=>['Disciplined','Serious'],'Rahu'=>['Ambitious','Unique'],'Ketu'=>['Spiritual']],
            'ta' => ['Sun'=>['நம்பிக்கை கொண்டவர்','தலைமை பண்பு'],'Moon'=>['அன்பானவர்','உணர்ச்சிவசப்படக்கூடியவர்'],'Mars'=>['துணிச்சலானவர்','சுறுசுறுப்பானவர்'],'Mercury'=>['அறிவுக்கூர்மை','சாதுர்யமானவர்'],'Jupiter'=>['அறிவாளி','தெய்வீக குணம்'],'Venus'=>['அழகானவர்','கவர்ச்சியானவர்'],'Saturn'=>['ஒழுக்கமானவர்','கண்டிப்பானவர்'],'Rahu'=>['ஆசைமிக்கவர்','வித்தியாசமானவர்'],'Ketu'=>['ஆன்மீக நாட்டம்']],
            'hi' => ['Sun'=>['आत्मविश्वासी','नेतृत्व'],'Moon'=>['देखभाल करने वाला','भावुक'],'Mars'=>['साहसी','ऊर्जावान'],'Mercury'=>['बुद्धिमान','चतुर'],'Jupiter'=>['ज्ञानी','शिक्षित'],'Venus'=>['सुंदर','आकर्षक'],'Saturn'=>['अनुशासित','गंभीर'],'Rahu'=>['महत्वाकांक्षी','अद्वितीय'],'Ketu'=>['आध्यात्मिक']]
        ];
        $app = [
            'ta' => ['Jupiter'=>'அறிவார்ந்த தோற்றம்','Venus'=>'அழகான முகம்','Mercury'=>'இளமையான தோற்றம்','Moon'=>'வட்டமான முகம்','Mars'=>'சுறுசுறுப்பான உடல்வாகு','Saturn'=>'உயரமான, முதிர்ந்த தோற்றம்','Sun'=>'கம்பீரமான தோற்றம்'],
            'hi' => ['Jupiter'=>'बुद्धिमान रूप','Venus'=>'सुंदर चेहरा','Mercury'=>'युवा रूप','Moon'=>'गोल चेहरा','Mars'=>'ऊर्जावान शरीर','Saturn'=>'लंबा, परिपक्व रूप','Sun'=>'गरिमापूर्ण रूप']
        ];
        $pLord = $this->transP($lord, $lang);
        $t = $traits[$lang][$lord] ?? $traits['en'][$lord];
        $desc = ($lang=='ta') ? "7-ம் அதிபதி {$pLord} என்பதால், உங்கள் துணைவி {$t[0]} மற்றும் {$t[1]} கொண்டவராக இருப்பார்." : (($lang=='hi') ? "7वें भाव के स्वामी {$pLord} होने के कारण, आपका जीवनसाथी {$t[0]} और {$t[1]} स्वभाव का होगा।" : "Spouse likely to be {$t[0]} and {$t[1]}, influenced by {$lord}.");

        return [
            'personality' => $t,
            'appearance' => $app[$lang][$lord] ?? 'Pleasant',
            'direction' => 'North', // Fixed for now
            'description' => $desc
        ];
    }

    private function remedies(bool $mangal, array $denied, array $delayed, string $lang): array {
        if ($lang == 'ta') {
            $r = ['வெள்ளிக்கிழமை அம்பிகையை வழிபடவும்.','நிச்சயிக்கப்பட்ட திருமணத்திற்கு முன் குலதெய்வ வழிபாடு செய்யவும்.'];
            if ($mangal) $r[] = 'செவ்வாய்க்கிழமை முருகனை வழிபடவும்.';
            return $r;
        }
        if ($lang == 'hi') {
            $r = ['शुक्रवार को देवी की पूजा करें।','विवाह से पहले कुलदेवता की पूजा करें।'];
            if ($mangal) $r[] = 'मंगलवार को भगवान कार्तिकेय की पूजा करें।';
            return $r;
        }
        return ['Worship Goddess Lakshmi on Fridays.', 'Perform Kuladevata Puja before marriage.'];
    }

    private function timing(array $pp, int $lagnaIdx, string $lang): array {
        return ['age_range' => '24 – 29', 'favorable_dashas' => [$lang=='ta' ? 'சுக்கிர அல்லது குரு திசை' : 'Venus or Jupiter Dasha']];
    }

    private function ord(int $n): string { $s = ['th','st','nd','rd']; $v = $n%100; return $n.($s[($v-20)%10] ?? $s[min($v,3)] ?? $s[0]); }
}
