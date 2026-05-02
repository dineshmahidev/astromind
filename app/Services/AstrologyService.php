<?php

namespace App\Services;

class AstrologyService
{
    private $stars = ['Aswini', 'Bharani', 'Karthigai', 'Rohini', 'Mirugaseerisham', 'Thiruvathirai', 'Punarpusam', 'Poosam', 'Ayilyam', 'Magam', 'Pooram', 'Uthiram', 'Hastham', 'Chithirai', 'Swathi', 'Visakam', 'Anusham', 'Kettai', 'Moolam', 'Pooradam', 'Uthiradam', 'Thiruvonam', 'Avittam', 'Sadhayam', 'Pooratathi', 'Uthiratathi', 'Revathi'];

    private $rasis = [
        'en' => ['Mesham', 'Rishabam', 'Mithunam', 'Kadagam', 'Simmam', 'Kanni', 'Thulaam', 'Virutchigam', 'Dhanusu', 'Magaram', 'Kumbam', 'Meenam'],
        'ta' => ['மேஷம்', 'ரிஷபம்', 'மிதுனம்', 'கடகம்', 'சிம்மம்', 'கன்னி', 'துலாம்', 'விருச்சிகம்', 'தனுசு', 'மகரம்', 'கும்பம்', 'மீனம்'],
        'hi' => ['मेष', 'वृषभ', 'मिथुन', 'कर्क', 'सिंह', 'कन्या', 'तुला', 'वृश्चिक', 'धनु', 'मकर', 'कुंभ', 'मीन']
    ];

    private $planets = [
        'en' => ['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn', 'Rahu', 'Ketu'],
        'ta' => ['சூரியன்', 'சந்திரன்', 'செவ்வாய்', 'புதன்', 'குரு', 'சுக்கிரன்', 'சனி', 'ராகு', 'கேது'],
        'hi' => ['सूर्य', 'चंद्र', 'मंगल', 'बुध', 'गुरु', 'शुक्र', 'शनि', 'राहु', 'केतु']
    ];

    private $dashaYears = ['Ketu' => 7, 'Venus' => 20, 'Sun' => 6, 'Moon' => 10, 'Mars' => 7, 'Rahu' => 18, 'Jupiter' => 16, 'Saturn' => 19, 'Mercury' => 17];
    private $dashaOrder = ['Ketu', 'Venus', 'Sun', 'Moon', 'Mars', 'Rahu', 'Jupiter', 'Saturn', 'Mercury'];

    public function getDetails($day, $month, $year, $hour, $minute, $second) {
        $jd = $this->getJulianDay($day, $month, $year, $hour, $minute, $second);
        $moonLong = $this->estimateMoonLongitude($jd);
        $sunLong  = $this->getSunPosition($jd);
        $ayanamsa = $this->getLahiriAyanamsa($jd);

        $siderealMoon = fmod($moonLong - $ayanamsa + 360, 360);
        $siderealSun  = fmod($sunLong  - $ayanamsa + 360, 360);

        $rasiIdx  = (int)($siderealMoon / 30);
        $starIdx  = (int)($siderealMoon / (360 / 27)); // 0-based (0=Aswini..26=Revathi)
        $padam    = $this->getPadam($siderealMoon);

        // --- TITHI ---
        // Tithi = difference between Moon & Sun longitudes / 12 degrees
        $tithiRaw = fmod($siderealMoon - $siderealSun + 360, 360);
        $tithiNum = (int)($tithiRaw / 12) + 1; // 1-30

        // --- VARA (Weekday) ---
        // JD 0 = Monday offset. Day of week from Julian Day
        $vara = ((int)($jd + 1.5)) % 7; // 0=Sun,1=Mon,...6=Sat

        // --- YOGA ---
        // Yoga = (Sun + Moon sidereal) / (360/27), index 0-26
        $yogaSum = fmod($siderealSun + $siderealMoon, 360);
        $yogaIdx = (int)($yogaSum / (360 / 27));
        $yogaNames = ['Vishkamba','Preeti','Ayushman','Saubhagya','Shobhana','Atiganda',
                      'Sukarman','Dhriti','Shoola','Ganda','Vriddhi','Dhruva','Vyaghata',
                      'Harshana','Vajra','Siddhi','Vyatipata','Variyan','Parigha','Shiva',
                      'Siddha','Sadhya','Shubha','Shukla','Brahma','Indra','Vaidhriti'];

        // --- KARANA ---
        // Karana = half-tithi (every 6 degrees)
        $karanaIdx = (int)($tithiRaw / 6) % 11;
        $karanaNames = ['Bava','Balava','Kaulava','Taitila','Garija','Vanija','Vishti',
                        'Shakuni','Chatushpada','Naga','Kimstughna'];

        // --- LAGNA (Ascendant) ---
        // Approximate: Sidereal time at birth location + birth time → Lagna Rasi index
        // Using a simplified mean ascendant: Sun advances ~1° per day,
        // Ascendant changes 1 sign (~2hrs). Approx: Lagna = Sun + 90° for noon
        $lagnaLong  = fmod($siderealSun + ($hour + $minute / 60) * 15, 360);
        $lagnaRasi  = (int)($lagnaLong / 30); // 0-11

        // --- NAVAMSA of Moon ---
        // Each sign = 30°, divided into 9 navamsas of 3°20' each
        $navamsaIdx = (int)(fmod($siderealMoon, 30) / (30 / 9));

        // ============================================================
        // PANCHAKAM CALCULATION (Traditional 5-Factor Formula)
        // Panchakam = (Vara + Tithi + Nakshatra + Lagna + Navamsa) mod 9
        // Nakshatra index is 1-based in the formula
        // ============================================================
        $panchakamNum = ($vara + $tithiNum + ($starIdx + 1) + ($lagnaRasi + 1) + ($navamsaIdx + 1)) % 9;

        $panchakamTypes = [
            0 => null,
            1 => ['name' => 'Mrityu Panchakam', 'ta' => 'மிருத்யு பஞ்சகம்', 'severity' => 'Very Bad', 'color' => '#ff4757'],
            2 => ['name' => 'Agni Panchakam',   'ta' => 'அக்னி பஞ்சகம்',   'severity' => 'Bad',      'color' => '#ff6b35'],
            3 => null,
            4 => ['name' => 'Raja Panchakam',   'ta' => 'ராஜ பஞ்சகம்',     'severity' => 'Neutral',  'color' => '#ffd32a'],
            5 => null,
            6 => ['name' => 'Chora Panchakam',  'ta' => 'சோர பஞ்சகம்',     'severity' => 'Bad',      'color' => '#575fcf'],
            7 => null,
            8 => ['name' => 'Roga Panchakam',   'ta' => 'ரோக பஞ்சகம்',     'severity' => 'Bad',      'color' => '#ef5777'],
        ];

        $panchakam = $panchakamTypes[$panchakamNum] ?? null;
        $hasPanchakam = !is_null($panchakam);

        // ============================================================
        // NAKSHATRA-BASED PANCHAKAM CHECK
        // Last 5 Nakshatras (index 22-26): Avittam(22), Sadhayam(23),
        // Pooratathi(24), Uthiratathi(25), Revathi(26)
        // ============================================================
        $nakshatraPanchakam = $starIdx >= 22;
        $panchakamNakshatras = ['Avittam', 'Sadhayam', 'Pooratathi', 'Uthiratathi', 'Revathi'];

        return [
            'jd'              => $jd,
            'sidereal_moon'   => $siderealMoon,
            'rasi'            => $this->rasis['en'][$rasiIdx],
            'rasi_ta'         => $this->rasis['ta'][$rasiIdx],
            'rasi_idx'        => $rasiIdx,
            'nakshatra'       => $this->stars[$starIdx],
            'padam'           => $padam,

            // Panchangam
            'tithi'           => $tithiNum,
            'tithi_name'      => $this->getTithiName($tithiNum),
            'vara'            => $vara,
            'vara_name'       => ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][$vara],
            'yoga'            => $yogaNames[$yogaIdx],
            'karana'          => $karanaNames[$karanaIdx],
            'lagna'           => $this->rasis['en'][$lagnaRasi],

            // Panchakam
            'panchakam_number'        => $panchakamNum,
            'has_panchakam'           => $hasPanchakam,
            'panchakam'               => $panchakam,
            'nakshatra_panchakam'     => $nakshatraPanchakam,
            'panchakam_description'   => $hasPanchakam
                ? "⚠️ {$panchakam['name']} is active. Avoid auspicious events."
                : ($nakshatraPanchakam
                    ? "⚠️ Nakshatra Panchakam active ({$this->stars[$starIdx]}). Be cautious."
                    : "✅ No Panchakam. The period is auspicious."),
            'panchakam_remedy'        => $hasPanchakam || $nakshatraPanchakam
                ? "Perform Panchakam Shanthi Puja. Recite Maha Mrityunjaya Mantra 108 times."
                : null,
        ];
    }

    private function getTithiName(int $tithi): string {
        $names = [
            1=>'Pratipada',2=>'Dwitiya',3=>'Tritiya',4=>'Chaturthi',5=>'Panchami',
            6=>'Shashthi',7=>'Saptami',8=>'Ashtami',9=>'Navami',10=>'Dashami',
            11=>'Ekadashi',12=>'Dwadashi',13=>'Trayodashi',14=>'Chaturdashi',15=>'Purnima',
            16=>'Pratipada',17=>'Dwitiya',18=>'Tritiya',19=>'Chaturthi',20=>'Panchami',
            21=>'Shashthi',22=>'Saptami',23=>'Ashtami',24=>'Navami',25=>'Dashami',
            26=>'Ekadashi',27=>'Dwadashi',28=>'Trayodashi',29=>'Chaturdashi',30=>'Amavasya'
        ];
        return $names[$tithi] ?? 'Unknown';
    }

    public function getFullDetails($day, $month, $year, $hour, $minute, $second, $lang = 'en') {
        $jd = $this->getJulianDay($day, $month, $year, $hour, $minute, $second);
        $ayanamsa = $this->getLahiriAyanamsa($jd);
        $l = in_array($lang, ['en', 'ta', 'hi']) ? $lang : 'en';

        $planetsRaw = ['Sun' => $this->getSunPosition($jd), 'Moon' => $this->estimateMoonLongitude($jd), 'Mars' => $this->getMarsPosition($jd), 'Mercury' => $this->getMercuryPosition($jd), 'Jupiter' => $this->getJupiterPosition($jd), 'Venus' => $this->getVenusPosition($jd), 'Saturn' => $this->getSaturnPosition($jd), 'Rahu' => $this->getRahuPosition($jd)];
        $planetsRaw['Ketu'] = fmod($planetsRaw['Rahu'] + 180, 360);

        $chart = []; $details = [];
        foreach ($planetsRaw as $name => $long) {
            $sidereal = fmod($long - $ayanamsa + 360, 360);
            $rasiIdx = (int)($sidereal / 30);
            $starIdx = (int)($sidereal / (360 / 27));
            $chart[$name] = ['rasi' => $rasiIdx, 'degree' => fmod($sidereal, 30)];
            $pName = $this->planets[$l][array_search($name, $this->planets['en'])] ?? $name;
            $details[] = [
                'name' => $pName, 'rasi' => $this->rasis[$l][$rasiIdx],
                'degree' => floor(fmod($sidereal, 30)) . "° " . floor((fmod($sidereal, 1) * 60)) . "'",
                'nakshatra' => $this->stars[$starIdx], 'padam' => $this->getPadam($sidereal)
            ];
        }

        $birthDate = new \DateTime("$year-$month-$day $hour:$minute");
        $dashaTimeline = $this->calculateDashaTimeline($birthDate, fmod($planetsRaw['Moon'] - $ayanamsa + 360, 360), $l);

        return ['chart' => $chart, 'details' => $details, 'dasha' => $dashaTimeline, 'rasi_names' => $this->rasis[$l]];
    }

    public function getHoroscope($signIdx, $period, $lang = 'en') {
        $l = in_array($lang, ['en', 'ta', 'hi']) ? $lang : 'en';
        $signName = $this->rasis[$l][$signIdx];
        
        // Get current date/time for real-time panchangam
        $now = new \DateTime();
        $cur = $this->getDetails(
            (int)$now->format('d'), (int)$now->format('m'), (int)$now->format('Y'), 
            (int)$now->format('H'), (int)$now->format('i'), (int)$now->format('s')
        );

        $luck_metrics = [
            'career' => rand(65, 95), 'wealth' => rand(60, 92), 'love' => rand(55, 88), 'health' => rand(75, 99)
        ];

        $panchangam = [
            'ta' => ['tithi' => $cur['tithi_name'], 'nakshatra' => $cur['nakshatra'], 'yoga' => $cur['yoga']],
            'en' => ['tithi' => $cur['tithi_name'], 'nakshatra' => $cur['nakshatra'], 'yoga' => $cur['yoga']],
            'hi' => ['tithi' => $cur['tithi_name'], 'nakshatra' => $cur['nakshatra'], 'yoga' => $cur['yoga']]
        ];

        $timings = [
            'ta' => ['rahu' => '10:30 AM - 12:00 PM', 'yama' => '07:30 AM - 09:00 AM', 'gulika' => '01:30 PM - 03:00 PM'],
            'en' => ['rahu' => '10:30 AM - 12:00 PM', 'yama' => '07:30 AM - 09:00 AM', 'gulika' => '01:30 PM - 03:00 PM'],
            'hi' => ['rahu' => '10:30 AM - 12:00 PM', 'yama' => '07:30 AM - 09:00 AM', 'gulika' => '01:30 PM - 03:00 PM']
        ];

        $remedies = [
            'ta' => ['color' => ($signIdx % 2 == 0 ? 'சிவப்பு' : 'மஞ்சள்'), 'number' => (string)(($signIdx + 3) % 9 + 1), 'mantra' => 'ஓம் நமோ நாராயணாய', 'ritual' => 'நெய் தீபம் ஏற்றி வழிபடவும்'],
            'en' => ['color' => ($signIdx % 2 == 0 ? 'Red' : 'Yellow'), 'number' => (string)(($signIdx + 3) % 9 + 1), 'mantra' => 'Om Namo Narayanaya', 'ritual' => 'Light a Ghee Lamp'],
            'hi' => ['color' => ($signIdx % 2 == 0 ? 'लाल' : 'पीला'), 'number' => (string)(($signIdx + 3) % 9 + 1), 'mantra' => 'ॐ नमो नारायणाय', 'ritual' => 'घी का दीपक जलाएं']
        ];

        $predictions = [
            'ta' => [
                'daily' => "இன்று {$signName} ராசிக்கு கிரக நிலைகள் மிகச் சிறப்பாக உள்ளன. உங்கள் முயற்சியால் நீங்கள் நினைத்த காரியங்கள் தடையின்றி முடியும்.",
                'weekly' => "இந்த வாரம் உங்கள் தொழில் மற்றும் பொருளாதார ரீதியாக நல்ல முன்னேற்றம் காணப்படும்.",
                'monthly' => "இந்த மாதம் உங்கள் குடும்பத்தில் மகிழ்ச்சியும் சுபிட்சமும் பெருகும்."
            ],
            'en' => [
                'daily' => "Planetary positions for {$signName} are highly favorable today. Your efforts will lead to successful completion of pending tasks.",
                'weekly' => "This week promises significant growth in your professional and financial life.",
                'monthly' => "A month filled with prosperity and happiness for you and your family."
            ],
            'hi' => [
                'daily' => "आज {$signName} के लिए ग्रहों की स्थिति बहुत शुभ है। आपके प्रयास सफल होंगे और रुके हुए काम पूरे होंगे।",
                'weekly' => "यह सप्ताह आपके पेशेवर और आर्थिक जीवन में अच्छी वृद्धि का वादा करता है।",
                'monthly' => "यह महीना आपके और आपके परिवार के लिए समृद्धि और खुशियों से भरा रहेगा।"
            ]
        ];

        return [
            'sign' => $signName, 'prediction' => $predictions[$l][$period] ?? $predictions['en'][$period],
            'luck_metrics' => $luck_metrics, 'panchangam' => $panchangam[$l], 'timings' => $timings[$l], 'remedies' => $remedies[$l],
            'transit_analysis' => ($l == 'ta') ? "வியாழன் மற்றும் சுக்கிரன் வலுவாக இருப்பதால் நற்பலன்கள் கூடும்." : "Strong Jupiter and Venus positions bring positive results.",
            'sani_report' => ($l == 'ta') ? "சனி பகவான் அருள் இருப்பதால் தடைகள் நீங்கும்." : "Shani's blessings will help overcome obstacles."
        ];
    }

    private function calculateDashaTimeline($birthDate, $moonLong, $lang) {
        $starWidth = 360 / 27;
        $starProgress = fmod($moonLong, $starWidth) / $starWidth;
        $startDashaIdx = ((int)($moonLong / $starWidth)) % 9;
        $currentDate = clone $birthDate;
        
        $timeline = [];
        for ($i = 0; $i < 9; $i++) {
            $mdPlanetEn = $this->dashaOrder[($startDashaIdx + $i) % 9];
            $mahadashaYears = ($i == 0) ? $this->dashaYears[$mdPlanetEn] * (1 - $starProgress) : $this->dashaYears[$mdPlanetEn];
            $mdStartDate = clone $currentDate;
            $mdEndDate = clone $currentDate;
            $mdEndDate->add(new \DateInterval('P' . floor($mahadashaYears * 365.25) . 'D'));

            $bhuktis = [];
            $bhuktiDate = clone $mdStartDate;
            for ($j = 0; $j < 9; $j++) {
                $bPlanetEn = $this->dashaOrder[($startDashaIdx + $i + $j) % 9];
                $bhuktiYears = ($this->dashaYears[$mdPlanetEn] * $this->dashaYears[$bPlanetEn]) / 120;
                $bStart = clone $bhuktiDate;
                $bhuktiDate->add(new \DateInterval('P' . floor($bhuktiYears * 365.25) . 'D'));
                $bEnd = clone $bhuktiDate;
                if ($bStart >= $mdEndDate) break;
                if ($bEnd > $mdEndDate) $bEnd = clone $mdEndDate;
                
                $segments = $this->getMonthlyTimeline($bPlanetEn, $bStart, $bEnd, $lang);
                $bName = $this->planets[$lang][array_search($bPlanetEn, $this->planets['en'])] ?? $bPlanetEn;
                $bhuktis[] = [
                    'planet' => $bName, 'start' => $bStart->format('d M Y'), 'end' => $bEnd->format('d M Y'), 
                    'desc' => $this->getBhuktiDescTranslated($bPlanetEn, $lang)['main'], 'segments' => $segments
                ];
            }

            $info = $this->getDashaInfoTranslated($mdPlanetEn, $lang);
            $pName = $this->planets[$lang][array_search($mdPlanetEn, $this->planets['en'])] ?? $mdPlanetEn;
            $timeline[] = ['planet' => $pName, 'start' => $mdStartDate->format('d M Y'), 'end' => $mdEndDate->format('d M Y'), 'description' => $info['main'], 'dos' => $info['dos'] ?? [], 'donts' => $info['donts'] ?? [], 'bhuktis' => $bhuktis];
            $currentDate = clone $mdEndDate;
        }
        return $timeline;
    }

    private function getMonthlyTimeline($planet, $startDate, $endDate, $lang) {
        $segments = []; $current = clone $startDate; $monthCount = 1;
        while ($current < $endDate) {
            $mStart = clone $current; $current->add(new \DateInterval('P30D')); 
            $mEnd = ($current > $endDate) ? clone $endDate : clone $current;
            $title = ($lang == 'ta') ? "மாதம் " . $monthCount : "Month " . $monthCount;
            $segments[] = ['title' => $title, 'dates' => $mStart->format('d M Y') . ' — ' . $mEnd->format('d M Y'), 'desc' => $this->getMonthSpecificDesc($planet, $monthCount, $lang)];
            $monthCount++; if ($monthCount > 36) break;
        }
        return $segments;
    }

    private function getMonthSpecificDesc($planet, $month, $lang) {
        $ta_templates = [
            "இந்த மாதத்தில் {$planet} கிரகத்தின் தாக்கம் உங்கள் ராசிக்கு மிகவும் சாதகமாக இருக்கும். நன்மைகள்: பண வரவு அதிகரித்தல். எச்சரிக்கை: ஆரோக்கியத்தில் கவனம்.",
            "மாதத்தின் தொடக்கத்தில் சில சவால்கள் இருந்தாலும், நிலைமை சீராகும். நன்மைகள்: நிலுவையில் இருந்த வேலைகள் முடியும்."
        ];
        $idx = ($month - 1) % count($ta_templates);
        return ($lang == 'ta') ? $ta_templates[$idx] : "Month {$month} analysis for {$planet}.";
    }

    private function getDashaInfoTranslated($planet, $lang) {
        $content = [
            'Jupiter' => ['en' => ['main' => 'Jupiter: Wisdom.'], 'ta' => ['main' => 'குரு: அறிவு.']],
            'Saturn' => ['en' => ['main' => 'Saturn: Hard work.'], 'ta' => ['main' => 'சனி: உழைப்பு.']]
        ];
        if(!isset($content[$planet])) $content[$planet] = ['en' => ['main' => "{$planet} period."], 'ta' => ['main' => "{$planet} காலம்."]];
        return $content[$planet][$lang] ?? $content[$planet]['en'];
    }

    private function getBhuktiDescTranslated($planet, $lang) {
        return ['main' => ($lang == 'ta') ? "{$planet} புத்தி பலன்கள்." : "{$planet} Bhukti effects."];
    }

    public function getJulianDay($day, $month, $year, $hour, $minute, $second) {
        $hour -= 5.5; if ($month <= 2) { $year--; $month += 12; }
        $a = floor($year / 100); $b = 2 - $a + floor($a / 4);
        return floor(365.25 * ($year + 4716)) + floor(30.6001 * ($month + 1)) + $day + $b - 1524.5 + ($hour + $minute / 60) / 24;
    }
    public function estimateMoonLongitude($jd) { $t = ($jd - 2451545.0) / 36525.0; $l0 = 218.316 + 481267.881 * $t; $m = 134.963 + 477198.867 * $t; return fmod($l0 + 6.289 * sin(deg2rad($m)), 360); }
    public function getPadam($moonLong) { $oneStar = 360 / 27; $onePadam = $oneStar / 4; return (int)((fmod($moonLong, $oneStar) / $onePadam) + 1); }
    public function getLahiriAyanamsa($jd) { $t = ($jd - 2451545.0) / 36525.0; return 22.4478333 + 1.55730 * $t; }

    // Public planet methods (used by MarriageController & others)
    public function getSunLong($jd)     { $n = $jd - 2451545.0; return fmod(280.460 + 0.9856474 * $n, 360); }
    public function getMarsLong($jd)    { $n = $jd - 2451545.0; return fmod(355.433 + 0.5240329 * $n, 360); }
    public function getMercuryLong($jd) { $n = $jd - 2451545.0; return fmod(252.251 + 4.0923388 * $n, 360); }
    public function getJupiterLong($jd) { $n = $jd - 2451545.0; return fmod(34.351  + 0.0830912 * $n, 360); }
    public function getVenusLong($jd)   { $n = $jd - 2451545.0; return fmod(181.979 + 1.6021303 * $n, 360); }
    public function getSaturnLong($jd)  { $n = $jd - 2451545.0; return fmod(50.077  + 0.0334597 * $n, 360); }
    public function getRahuLong($jd)    { $n = $jd - 2451545.0; return fmod(125.122 - 0.0529532 * $n, 360); }

    // Private aliases for internal use
    private function getSunPosition($jd)     { return $this->getSunLong($jd); }
    private function getMarsPosition($jd)    { return $this->getMarsLong($jd); }
    private function getMercuryPosition($jd) { return $this->getMercuryLong($jd); }
    private function getJupiterPosition($jd) { return $this->getJupiterLong($jd); }
    private function getVenusPosition($jd)   { return $this->getVenusLong($jd); }
    private function getSaturnPosition($jd)  { return $this->getSaturnLong($jd); }
    private function getRahuPosition($jd)    { return $this->getRahuLong($jd); }
}
