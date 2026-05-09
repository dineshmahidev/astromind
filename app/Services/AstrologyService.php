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

    public function getDetails($day, $month, $year, $hour, $minute, $second, $lat = 13.0827, $lon = 80.2707, $lang = 'en')
    {
        $jd = $this->getJulianDay($day, $month, $year, $hour, $minute, $second);
        $moonLong = $this->estimateMoonLongitude($jd);
        $sunLong = $this->getSunPosition($jd);
        $ayanamsa = $this->getLahiriAyanamsa($jd);

        $siderealMoon = fmod($moonLong - $ayanamsa + 360, 360);
        $siderealSun = fmod($sunLong - $ayanamsa + 360, 360);

        $rasiIdx = (int) ($siderealMoon / 30);
        $starIdx = (int) ($siderealMoon / (360 / 27));
        $padam = $this->getPadam($siderealMoon);

        // --- TITHI ---
        $tithiRaw = fmod($siderealMoon - $siderealSun + 360, 360);
        $tithiNum = (int) ($tithiRaw / 12) + 1;

        // --- VARA (Day) ---
        $vara = (int) floor($jd + 1.5) % 7;

        // --- YOGA ---
        $yogaRaw = fmod($siderealMoon + $siderealSun, 360);
        $yogaIdx = (int) ($yogaRaw / (360 / 27));
        $yogaNamesEn = ['Vishkambam', 'Priti', 'Ayushman', 'Saubhagya', 'Shobhana', 'Atiganda', 'Sukarma', 'Dhriti', 'Shula', 'Ganda', 'Vriddhi', 'Dhruva', 'Vyaghata', 'Harshana', 'Vajra', 'Siddhi', 'Vyatipata', 'Variyan', 'Parigha', 'Shiva', 'Siddha', 'Sadhya', 'Shubha', 'Shukla', 'Brahma', 'Indra', 'Vaidhriti'];
        $yogaNamesTa = ['விஷ்கம்பம்', 'பிரீதி', 'ஆயுஷ்மான்', 'சௌபாக்கியம்', 'சோபனம்', 'அதிகண்டம்', 'சுகர்மம்', 'திருதி', 'சூலம்', 'கண்டம்', 'விருத்தி', 'துருவம்', 'வ்யாகாதம்', 'ஹர்ஷணம்', 'வஜ்ரம்', 'சித்தி', 'வ்யதீபாதம்', 'வாரியான்', 'பரிகம்', 'சிவம்', 'சித்தம்', 'சாத்தியம்', 'சுபம்', 'சுப்பிரம்', 'பிரம்மம்', 'ஐந்திரம்', 'வைதிருதி'];

        // --- KARANA ---
        $karanaRaw = fmod($tithiRaw, 12);
        $karanaIdx = (int) ($tithiRaw / 6);
        $karanaNamesEn = ['Bava', 'Balava', 'Kaulava', 'Taitila', 'Gara', 'Vanija', 'Vishti', 'Sakuni', 'Chatushpada', 'Naga', 'Kimstughna'];
        $karanaNamesTa = ['பவம்', 'பாலவம்', 'கௌலவம்', 'சைதிலம்', 'கரசை', 'வணிசை', 'பத்திரை', 'சகுனி', 'சதுஷ்பாதம்', 'நாகவம்', 'கிம்ஸ்துக்னம்'];

        $kNameEn = ($karanaIdx == 0) ? 'Kimstughna' : (($karanaIdx >= 57) ? $karanaNamesEn[($karanaIdx - 57) + 7] : $karanaNamesEn[($karanaIdx - 1) % 7]);
        $kNameTa = ($karanaIdx == 0) ? 'கிம்ஸ்துக்னம்' : (($karanaIdx >= 57) ? $karanaNamesTa[($karanaIdx - 57) + 7] : $karanaNamesTa[($karanaIdx - 1) % 7]);

        // --- LAGNAM ---
        $lagnaRasi = $this->calculateLagna($jd, $hour, $minute, $lat, $lon);

        // --- YOGI / AVAYOGI ---
        $yogiPoint = fmod($siderealMoon + $siderealSun + 93.333, 360);
        $yogiStarIdx = (int) ($yogiPoint / (360 / 27));
        $yogiPlanetEn = $this->dashaOrder[$yogiStarIdx % 9];
        $avayogiPlanetEn = $this->dashaOrder[($yogiStarIdx + 6) % 9];

        $yogiPlanet = $this->planets[$lang][array_search($yogiPlanetEn, $this->planets['en'])] ?? $yogiPlanetEn;
        $avayogiPlanet = $this->planets[$lang][array_search($avayogiPlanetEn, $this->planets['en'])] ?? $avayogiPlanetEn;

        return [
            'jd' => $jd,
            'sidereal_moon' => $siderealMoon,
            'ayanamsa' => $ayanamsa,
            'rasi' => $this->rasis['en'][$rasiIdx],
            'rasi_ta' => $this->rasis['ta'][$rasiIdx],
            'rasi_idx' => $rasiIdx,
            'nakshatra' => $this->stars[$starIdx],
            'padam' => $padam,
            'tithi' => $tithiNum,
            'tithi_name' => $this->getTithiName($tithiNum, $lang),
            'vara_name' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][$vara],
            'yoga' => ($lang === 'ta') ? $yogaNamesTa[$yogaIdx % 27] : $yogaNamesEn[$yogaIdx % 27],
            'karana' => ($lang === 'ta') ? $kNameTa : $kNameEn,
            'lagna' => $this->rasis['en'][$lagnaRasi],
            'lagna_ta' => $this->rasis['ta'][$lagnaRasi],
            'yogi' => $yogiPlanet,
            'avayogi' => $avayogiPlanet
        ];
    }

    private function calculateLagna($jd, $hour, $minute, $lat, $lon)
    {
        $n = $jd - 2451545.0;
        $epsilon = 23.439 - 0.0000004 * $n;
        $ut = ($hour - 5.5) + ($minute / 60);
        $gmst = fmod(6.697374558 + 0.06570982441908 * $n + $ut, 24);
        $lst = fmod($gmst + $lon / 15, 24) * 15;
        $asc = rad2deg(atan2(cos(deg2rad($lst)), -sin(deg2rad($lst)) * cos(deg2rad($epsilon)) - tan(deg2rad($lat)) * sin(deg2rad($epsilon))));
        $ayanamsa = $this->getLahiriAyanamsa($jd);
        return (int) (fmod($asc - $ayanamsa + 360, 360) / 30);
    }

    private function getTithiName(int $tithi, $lang = 'en'): string
    {
        $namesEn = [
            1 => 'Pratipada',
            2 => 'Dwitiya',
            3 => 'Tritiya',
            4 => 'Chaturthi',
            5 => 'Panchami',
            6 => 'Shashthi',
            7 => 'Saptami',
            8 => 'Ashtami',
            9 => 'Navami',
            10 => 'Dashami',
            11 => 'Ekadashi',
            12 => 'Dwadashi',
            13 => 'Trayodashi',
            14 => 'Chaturdashi',
            15 => 'Purnima',
            16 => 'Pratipada',
            17 => 'Dwitiya',
            18 => 'Tritiya',
            19 => 'Chaturthi',
            20 => 'Panchami',
            21 => 'Shashthi',
            22 => 'Saptami',
            23 => 'Ashtami',
            24 => 'Navami',
            25 => 'Dashami',
            26 => 'Ekadashi',
            27 => 'Dwadashi',
            28 => 'Trayodashi',
            29 => 'Chaturdashi',
            30 => 'Amavasya'
        ];
        $namesTa = [
            1 => 'பிரதமை',
            2 => 'துவிதியை',
            3 => 'திருதியை',
            4 => 'சதுர்த்தி',
            5 => 'பஞ்சமி',
            6 => 'சஷ்டி',
            7 => 'சப்தமி',
            8 => 'அஷ்டமி',
            9 => 'நவமி',
            10 => 'தசமி',
            11 => 'ஏகாதசி',
            12 => 'துவாதசி',
            13 => 'திரயோதசி',
            14 => 'சதுர்த்தசி',
            15 => 'பௌர்ணமி',
            16 => 'பிரதமை',
            17 => 'துவிதியை',
            18 => 'திருதியை',
            19 => 'சதுர்த்தி',
            20 => 'பஞ்சமி',
            21 => 'சஷ்டி',
            22 => 'சப்தமி',
            23 => 'அஷ்டமி',
            24 => 'நவமி',
            25 => 'தசமி',
            26 => 'ஏகாதசி',
            27 => 'துவாதசி',
            28 => 'திரயோதசி',
            29 => 'சதுர்த்தசி',
            30 => 'அமாவாசை'
        ];
        return ($lang === 'ta') ? ($namesTa[$tithi] ?? 'தெரியவில்லை') : ($namesEn[$tithi] ?? 'Unknown');
    }

    public function getFullDetails($day, $month, $year, $hour, $minute, $second, $lang = 'en', $lat = 11.3410, $lon = 77.7172)
    {
        $l = in_array($lang, ['en', 'ta', 'hi']) ? $lang : 'en';
        $summary = $this->getDetails($day, $month, $year, $hour, $minute, $second, $lat, $lon, $l);
        $jd = $summary['jd'];
        $ayanamsa = $summary['ayanamsa'];

        $planetsRaw = [
            'Sun' => $this->getSunPosition($jd),
            'Moon' => $this->estimateMoonLongitude($jd),
            'Mars' => $this->getMarsPosition($jd),
            'Mercury' => $this->getMercuryPosition($jd),
            'Jupiter' => $this->getJupiterPosition($jd),
            'Venus' => $this->getVenusPosition($jd),
            'Saturn' => $this->getSaturnPosition($jd),
            'Rahu' => $this->getRahuPosition($jd)
        ];
        $planetsRaw['Ketu'] = fmod($planetsRaw['Rahu'] + 180, 360);

        $chart = [];
        $details = [];
        foreach ($planetsRaw as $name => $long) {
            $sidereal = fmod($long - $ayanamsa + 360, 360);
            $rasiIdx = (int) ($sidereal / 30);
            $starIdx = (int) ($sidereal / (360 / 27));
            $chart[$name] = ['rasi' => $rasiIdx, 'degree' => fmod($sidereal, 30)];
            $pName = $this->planets[$l][array_search($name, $this->planets['en'])] ?? $name;
            $details[] = [
                'name' => $pName,
                'rasi' => $this->rasis[$l][$rasiIdx],
                'degree' => floor(fmod($sidereal, 30)) . "° " . floor((fmod($sidereal, 1) * 60)) . "'",
                'nakshatra' => $this->stars[$starIdx],
                'padam' => $this->getPadam($sidereal)
            ];
        }

        $birthDate = new \DateTime("$year-$month-$day $hour:$minute");
        $dashaTimeline = $this->calculateDashaTimeline($birthDate, $summary['sidereal_moon'], $l);

        return [
            'summary' => [
                'tithi_name' => $summary['tithi_name'],
                'nakshatra' => $summary['nakshatra'],
                'rasi' => $summary['rasi'],
                'rasi_ta' => $summary['rasi_ta'],
                'yoga' => $summary['yoga'],
                'karana' => $summary['karana'],
                'lagna' => $summary['lagna'],
                'lagna_ta' => $summary['lagna_ta'],
                'yogi' => $summary['yogi'],
                'avayogi' => $summary['avayogi'],
            ],
            'chart' => $chart,
            'details' => $details,
            'dasha' => $dashaTimeline,
            'rasi_names' => $this->rasis[$l]
        ];
    }

    private function calculateDashaTimeline($birthDate, $moonLong, $lang)
    {
        $starRange = 360 / 27;
        $starIdx = (int) ($moonLong / $starRange);
        $starLong = fmod($moonLong, $starRange);
        $starPercent = $starLong / $starRange;

        $startPlanet = $this->dashaOrder[$starIdx % 9];
        $yearsLeft = $this->dashaYears[$startPlanet] * (1 - $starPercent);

        $timeline = [];
        $currentDate = clone $birthDate;
        $order = array_merge(array_slice($this->dashaOrder, array_search($startPlanet, $this->dashaOrder)), array_slice($this->dashaOrder, 0, array_search($startPlanet, $this->dashaOrder)));

        foreach ($order as $i => $planet) {
            $years = ($i === 0) ? $yearsLeft : $this->dashaYears[$planet];
            $startDate = clone $currentDate;
            $currentDate->modify("+" . round($years * 365) . " days");

            $info = $this->getDashaInfoTranslated($planet, $lang);
            $bhuktis = $this->calculateBhuktis($startDate, $years, $planet, $lang);

            $timeline[] = [
                'planet' => $this->planets[$lang][array_search($planet, $this->planets['en'])] ?? $planet,
                'start' => $startDate->format('d M Y'),
                'end' => $currentDate->format('d M Y'),
                'description' => $info['main'],
                'dos' => $info['dos'] ?? [],
                'donts' => $info['donts'] ?? [],
                'bhuktis' => $bhuktis
            ];
        }
        return $timeline;
    }

    private function calculateBhuktis($dashaStart, $totalYears, $mainPlanet, $lang)
    {
        $bhuktis = [];
        $currentDate = clone $dashaStart;
        $mainYears = $this->dashaYears[$mainPlanet];

        $startIndex = array_search($mainPlanet, $this->dashaOrder);
        for ($i = 0; $i < 9; $i++) {
            $subPlanet = $this->dashaOrder[($startIndex + $i) % 9];
            $subYears = $this->dashaYears[$subPlanet];
            $bhuktiYears = ($mainYears * $subYears) / 120;

            $startDate = clone $currentDate;
            $currentDate->modify("+" . round($bhuktiYears * 365) . " days");

            // Calculate monthly segments (Pratyantardashas) for this Bhukti
            $segments = [];
            $segDate = clone $startDate;
            for ($j = 0; $j < 9; $j++) {
                $pntPlanet = $this->dashaOrder[($startIndex + $i + $j) % 9];
                $pntYears = $this->dashaYears[$pntPlanet];
                $pntDays = ($bhuktiYears * $pntYears / 120) * 365;
                
                $pntStart = clone $segDate;
                $segDate->modify("+" . round($pntDays) . " days");
                
                $planetName = $this->planets[$lang][array_search($pntPlanet, $this->planets['en'])] ?? $pntPlanet;
                
                $segments[] = [
                    'title' => $planetName . " Phase",
                    'dates' => $pntStart->format('d M Y') . " - " . $segDate->format('d M Y'),
                    'desc' => $this->getPratyantardashaDesc($pntPlanet, $lang)
                ];
            }

            $bhuktis[] = [
                'planet' => $this->planets[$lang][array_search($subPlanet, $this->planets['en'])] ?? $subPlanet,
                'start' => $startDate->format('d M Y'),
                'end' => $currentDate->format('d M Y'),
                'description' => $this->getBhuktiDescTranslated($subPlanet, $lang)['main'],
                'segments' => $segments
            ];
        }
        return $bhuktis;
    }

    private function getPratyantardashaDesc($planet, $lang)
    {
        $descs = [
            'Sun' => ['en' => 'Increase in confidence and social status.', 'ta' => 'தன்னம்பிக்கை மற்றும் சமூக அந்தஸ்து அதிகரிக்கும்.'],
            'Moon' => ['en' => 'Focus on emotional well-being and home life.', 'ta' => 'மன அமைதி மற்றும் குடும்ப வாழ்க்கையில் கவனம் தேவை.'],
            'Mars' => ['en' => 'High energy period, avoid unnecessary conflicts.', 'ta' => 'அதிக ஆற்றல் கொண்ட காலம், தேவையற்ற மோதல்களைத் தவிர்க்கவும்.'],
            'Mercury' => ['en' => 'Good for learning and intellectual pursuits.', 'ta' => 'புதிய விஷயங்களைக் கற்றுக்கொள்ளவும் அறிவுத்தேடலுக்கும் ஏற்ற காலம்.'],
            'Jupiter' => ['en' => 'Favorable for spiritual growth and wisdom.', 'ta' => 'ஆன்மீக வளர்ச்சி மற்றும் ஞானம் பெற சாதகமான காலம்.'],
            'Venus' => ['en' => 'Appreciation of beauty, arts, and comfort.', 'ta' => 'கலைகள் மற்றும் வசதிகளில் ஆர்வம் கூடும் காலம்.'],
            'Saturn' => ['en' => 'Period requiring discipline and patience.', 'ta' => 'ஒழுக்கம் மற்றும் பொறுமை தேவைப்படும் காலம்.'],
            'Rahu' => ['en' => 'Sudden changes and unconventional growth.', 'ta' => 'திடீர் மாற்றங்கள் மற்றும் அசாதாரண வளர்ச்சி ஏற்படும்.'],
            'Ketu' => ['en' => 'Introspection and spiritual insights.', 'ta' => 'உள்நோக்கிய தேடல் மற்றும் ஆன்மீக ஞானம் கிடைக்கும்.'],
        ];
        return $descs[$planet][$lang] ?? ($descs[$planet]['en'] ?? 'Positive influences expected.');
    }

    private function getDashaInfoTranslated($planet, $lang)
    {
        $content = [
            'Sun' => [
                'ta' => ['main' => "சூரிய தசை: அதிகாரம் மற்றும் கௌரவம் கூடும் காலம். அரசு வழி ஆதாயம் உண்டு.", 'dos' => ['தந்தையை மதிக்கவும்', 'அதிகாலையில் எழவும்'], 'donts' => ['யாரையும் ஏளனம் செய்யாதீர்', 'முரட்டுத்தனத்தை தவிர்க்கவும்']],
                'en' => ['main' => "Sun Dasha: A period of authority and prestige. Gains through government are likely.", 'dos' => ['Respect father figures', 'Wake up early'], 'donts' => ['Avoid egoistic behavior', 'Do not disrespect elders']]
            ],
            'Moon' => [
                'ta' => ['main' => "சந்திர தசை: மன அமைதியும் மகிழ்ச்சியும் தரும் காலம். கலைகளில் ஆர்வம் கூடும்.", 'dos' => ['தாய்க்கு உதவவும்', 'தியானம் செய்யவும்'], 'donts' => ['தேவையற்ற கவலைகளை தவிர்க்கவும்', 'இரவில் குளிர்ந்த உணவை தவிர்க்கவும்']],
                'en' => ['main' => "Moon Dasha: Period of peace and happiness. Interest in arts will increase.", 'dos' => ['Help mother/women', 'Practice meditation'], 'donts' => ['Avoid overthinking', 'Stay away from cold environments']]
            ],
            'Mars' => [
                'ta' => ['main' => "செவ்வாய் தசை: ஆற்றலும் துணிச்சலும் மிக்க காலம். நிலபுலன்கள் சேரும்.", 'dos' => ['உடற்பயிற்சி செய்யவும்', 'சகோதரர்களுக்கு உதவவும்'], 'donts' => ['கோபத்தை குறைக்கவும்', 'கூர்மையான ஆயுதங்களுடன் கவனம் தேவை']],
                'en' => ['main' => "Mars Dasha: Period of energy and courage. Real estate gains possible.", 'dos' => ['Exercise regularly', 'Support siblings'], 'donts' => ['Control anger', 'Be careful with sharp tools']]
            ],
            'Mercury' => [
                'ta' => ['main' => "புதன் தசை: அறிவுத்திறனும் வியாபார வெற்றியும் தரும் காலம். பேச்சாற்றல் கூடும்.", 'dos' => ['புதிய கல்வி கற்கவும்', 'பசுவுக்கு புல் அளிக்கவும்'], 'donts' => ['உறுதியற்ற பேச்சை தவிர்க்கவும்', 'சதி திட்டங்களை தவிர்க்கவும்']],
                'en' => ['main' => "Mercury Dasha: Intellectual growth and business success. Improved communication.", 'dos' => ['Learn new skills', 'Feed cows'], 'donts' => ['Avoid indecisiveness', 'Stay away from gossip']]
            ],
            'Jupiter' => [
                'ta' => ['main' => "குரு தசை: செல்வம் மற்றும் ஆன்மீக முன்னேற்றம் தரும் பொற்காலம். குழந்தைகளால் மகிழ்ச்சி உண்டு.", 'dos' => ['பெரியோர்களை மதிக்கவும்', 'தான தர்மம் செய்யவும்'], 'donts' => ['அகந்தையை தவிர்க்கவும்', 'பிறரை குறை சொல்லாதீர்']],
                'en' => ['main' => "Jupiter Dasha: Golden period for wealth and spiritual growth. Joy through children.", 'dos' => ['Respect teachers/elders', 'Donate to charity'], 'donts' => ['Avoid arrogance', 'Do not criticize others']]
            ],
            'Venus' => [
                'ta' => ['main' => "சுக்கிர தசை: சொகுசு வாழ்வும் கலை நயமும் மிக்க காலம். திருமணம் கூடி வரும்.", 'dos' => ['தூய்மையாக இருக்கவும்', 'கலைகளை ரசிக்கவும்'], 'donts' => ['வீண் ஆடம்பரத்தை தவிர்க்கவும்', 'பெண்களை அவமதிக்காதீர்']],
                'en' => ['main' => "Venus Dasha: Period of luxury and artistic enjoyment. Marriage prospects improve.", 'dos' => ['Maintain hygiene', 'Engage in creative arts'], 'donts' => ['Avoid overspending', 'Do not disrespect women']]
            ],
            'Saturn' => [
                'ta' => ['main' => "சனி தசை: பொறுமையும் உழைப்பும் தேவைப்படும் காலம். நீண்ட கால ஆதாயம் உண்டு.", 'dos' => ['நேர்மையாக இருக்கவும்', 'ஏழைகளுக்கு உதவவும்'], 'donts' => ['சோம்பலை தவிர்க்கவும்', 'மது பழக்கத்தை தவிர்க்கவும்']],
                'en' => ['main' => "Saturn Dasha: Period requiring patience and hard work. Long-term gains possible.", 'dos' => ['Be honest', 'Help the needy'], 'donts' => ['Avoid laziness', 'Stay away from addictions']]
            ],
            'Rahu' => [
                'ta' => ['main' => "ராகு தசை: எதிர்பாராத திருப்பங்களும் வெளிநாட்டு பயணங்களும் தரும் காலம்.", 'dos' => ['குலதெய்வ வழிபாடு செய்யவும்', 'விலங்குகளுக்கு உணவளிக்கவும்'], 'donts' => ['குறுக்கு வழியை தவிர்க்கவும்', 'அறிமுகம் இல்லாதவர்களை நம்பாதீர்']],
                'en' => ['main' => "Rahu Dasha: Period of unexpected turns and foreign travels.", 'dos' => ['Pray to ancestors', 'Feed animals'], 'donts' => ['Avoid shortcuts', 'Do not trust strangers blindly']]
            ],
            'Ketu' => [
                'ta' => ['main' => "கேது தசை: ஞானமும் முக்தியும் தேடும் காலம். ஆன்மீக பயணங்கள் உண்டு.", 'dos' => ['ஆன்மீக புத்தகங்கள் படிக்கவும்', 'நாய்களுக்கு உணவளிக்கவும்'], 'donts' => ['உலகாயத பற்றை குறைக்கவும்', 'தனிமையை தவிர்க்கவும்']],
                'en' => ['main' => "Ketu Dasha: Period of seeking wisdom and detachment. Spiritual travels likely.", 'dos' => ['Read spiritual texts', 'Feed dogs'], 'donts' => ['Reduce material attachment', 'Avoid isolation']]
            ]
        ];
        return $content[$planet][$lang] ?? ($content[$planet]['en'] ?? ['main' => "{$planet} period.", 'dos' => [], 'donts' => []]);
    }

    private function getBhuktiDescTranslated($planet, $lang)
    {
        $descs = [
            'en' => "Special focus on {$planet} energy during this sub-period.",
            'ta' => "இந்த புத்தி காலத்தில் {$planet} கிரகத்தின் ஆற்றல் உங்களுக்கு சிறப்பான பலன்களைத் தரும்."
        ];
        return ['main' => $descs[$lang] ?? $descs['en']];
    }

    public function getJulianDay($day, $month, $year, $hour, $minute, $second)
    {
        $hour -= 5.5;
        if ($month <= 2) {
            $year--;
            $month += 12;
        }
        $a = floor($year / 100);
        $b = 2 - $a + floor($a / 4);
        return floor(365.25 * ($year + 4716)) + floor(30.6001 * ($month + 1)) + $day + $b - 1524.5 + ($hour + $minute / 60) / 24;
    }

    public function estimateMoonLongitude($jd)
    {
        $t = ($jd - 2451545.0) / 36525.0;
        $l0 = 218.316 + 481267.881 * $t;
        $m = 134.963 + 477198.867 * $t;
        return fmod($l0 + 6.289 * sin(deg2rad($m)), 360);
    }

    public function getPadam($moonLong)
    {
        $oneStar = 360 / 27;
        $onePadam = $oneStar / 4;
        return (int) ((fmod($moonLong, $oneStar) / $onePadam) + 1);
    }

    public function getLahiriAyanamsa($jd)
    {
        $t = ($jd - 2451545.0) / 36525.0;
        return 22.4478333 + 1.55730 * $t;
    }

    public function getSunLong($jd)
    {
        $n = $jd - 2451545.0;
        return fmod(280.460 + 0.9856474 * $n, 360);
    }
    public function getMarsLong($jd)
    {
        $n = $jd - 2451545.0;
        return fmod(355.433 + 0.5240329 * $n, 360);
    }
    public function getMercuryLong($jd)
    {
        $n = $jd - 2451545.0;
        return fmod(252.251 + 4.0923388 * $n, 360);
    }
    public function getJupiterLong($jd)
    {
        $n = $jd - 2451545.0;
        return fmod(34.351 + 0.0830912 * $n, 360);
    }
    public function getVenusLong($jd)
    {
        $n = $jd - 2451545.0;
        return fmod(181.979 + 1.6021303 * $n, 360);
    }
    public function getSaturnLong($jd)
    {
        $n = $jd - 2451545.0;
        return fmod(50.077 + 0.0334597 * $n, 360);
    }
    public function getRahuLong($jd)
    {
        $n = $jd - 2451545.0;
        return fmod(125.122 - 0.0529532 * $n, 360);
    }

    private function getSunPosition($jd)
    {
        return $this->getSunLong($jd);
    }
    private function getMarsPosition($jd)
    {
        return $this->getMarsLong($jd);
    }
    private function getMercuryPosition($jd)
    {
        return $this->getMercuryLong($jd);
    }
    private function getJupiterPosition($jd)
    {
        return $this->getJupiterLong($jd);
    }
    private function getVenusPosition($jd)
    {
        return $this->getVenusLong($jd);
    }
    private function getSaturnPosition($jd)
    {
        return $this->getSaturnLong($jd);
    }
    private function getRahuPosition($jd)
    {
        return $this->getRahuLong($jd);
    }

    public function getHoroscope($signIdx, $period, $lang = 'en')
    {
        $rasis = [
            'en' => ['Mesham', 'Rishabam', 'Mithunam', 'Kadagam', 'Simmam', 'Kanni', 'Thulaam', 'Virutchigam', 'Dhanusu', 'Magaram', 'Kumbam', 'Meenam'],
            'ta' => ['மேஷம்', 'ரிஷபம்', 'மிதுனம்', 'கடகம்', 'சிம்மம்', 'கன்னி', 'துலாம்', 'விருச்சிகம்', 'தனுசு', 'மகரம்', 'கும்பம்', 'மீனம்'],
            'hi' => ['मेष', 'वृषभ', 'मिथुन', 'कर्क', 'सिंह', 'कन्या', 'तुला', 'वृश्चिक', 'धनु', 'मकर', 'कुंभ', 'मीन']
        ];
        
        $today = new \DateTime();
        $dayOfWeek = (int)$today->format('w'); // 0 (Sun) to 6 (Sat)
        
        // Dynamic Timings based on day of week
        $rahuTimings = ["16:30 - 18:00", "07:30 - 09:00", "15:00 - 16:30", "12:00 - 13:30", "13:30 - 15:00", "10:30 - 12:00", "09:00 - 10:30"];
        $yamaTimings = ["12:00 - 13:30", "10:30 - 12:00", "09:00 - 10:30", "07:30 - 09:00", "06:00 - 07:30", "15:00 - 16:30", "13:30 - 15:00"];
        $gulikaTimings = ["15:00 - 16:30", "13:30 - 15:00", "12:00 - 13:30", "10:30 - 12:00", "09:00 - 10:30", "07:30 - 09:00", "06:00 - 07:30"];

        // Calculate current Tithi/Nakshatra for today
        $nowDetails = $this->getDetails((int)$today->format('d'), (int)$today->format('m'), (int)$today->format('Y'), (int)$today->format('H'), (int)$today->format('i'), 0);

        $predictions = [
            'daily' => [
                'en' => "Today's planetary alignment brings clarity and new opportunities for {$rasis['en'][$signIdx]}. Stay focused on your goals.",
                'ta' => "இன்றைய கிரக நிலைகள் {$rasis['ta'][$signIdx]} ராசிக்கு தெளிவையும் புதிய வாய்ப்புகளையும் வழங்கும். உங்கள் குறிக்கோளில் கவனமாக இருங்கள்.",
                'hi' => "आज का ग्रहों का संरेखण {$rasis['hi'][$signIdx]} के लिए स्पष्टता और नए अवसर लाता है। अपने लक्ष्यों पर केंद्रित रहें।"
            ]
        ];

        // Pseudo-random metrics based on date and rasi to keep it dynamic but consistent for the day
        $seed = (int)$today->format('Ymd') + $signIdx;
        srand($seed);

        return [
            'sign' => $rasis[$lang][$signIdx],
            'prediction' => $predictions[$period][$lang] ?? $predictions['daily'][$lang],
            'transit_analysis' => ($lang === 'ta') ? "குரு பகவான் உங்கள் ராசிக்கு சாதகமான நிலையில் உள்ளார்." : "Jupiter is in a favorable position for your sign.",
            'luck_metrics' => [
                'career' => rand(60, 95),
                'wealth' => rand(55, 90),
                'love' => rand(65, 98),
                'health' => rand(70, 95)
            ],
            'remedies' => [
                'color' => ($signIdx % 2 == 0) ? ($lang == 'ta' ? 'தங்கம்' : 'Gold') : ($lang == 'ta' ? 'நீலம்' : 'Blue'),
                'number' => ($signIdx + 3) % 9 + 1,
                'mantra' => ($lang === 'ta') ? "ஓம் நமோ நாராயணாய" : "Om Namo Narayanaya",
                'ritual' => ($lang === 'ta') ? "சூரிய வழிபாடு செய்யவும்" : "Worship the Sun god"
            ],
            'timings' => [
                'auspicious' => "10:30 AM - 12:00 PM",
                'rahu' => $rahuTimings[$dayOfWeek],
                'yama' => $yamaTimings[$dayOfWeek],
                'gulika' => $gulikaTimings[$dayOfWeek]
            ],
            'panchangam' => [
                'tithi' => $nowDetails['tithi_name'],
                'nakshatra' => $nowDetails['nakshatra']
            ],
            'sani_report' => ($lang === 'ta') ? 'சனி பகவான் தற்போது உங்கள் ராசிக்கு சுப பலன்களைத் தருவார்.' : 'Lord Shani is currently in a favorable position for your sign.'
        ];
    }

    public function getFuturePrediction($day, $month, $year, $hour, $minute, $category, $lang = 'en')
    {
        $fullDetails = $this->getFullDetails($day, $month, $year, $hour, $minute, 0, $lang);
        
        $currentDasha = "Unknown";
        $currentBhukti = "Unknown";
        $today = new \DateTime();
        
        foreach ($fullDetails['dasha'] as $d) {
            $dStart = new \DateTime($d['start']);
            $dEnd = new \DateTime($d['end']);
            if ($today >= $dStart && $today <= $dEnd) {
                $currentDasha = $d['planet'];
                foreach ($d['bhuktis'] as $b) {
                    $bStart = new \DateTime($b['start']);
                    $bEnd = new \DateTime($b['end']);
                    if ($today >= $bStart && $today <= $bEnd) {
                        $currentBhukti = $b['planet'];
                        break;
                    }
                }
                break;
            }
        }

        $lagna = $fullDetails['summary']['lagna'];
        $rasi = $fullDetails['summary']['rasi'];

        $templates = [
            'career' => [
                'en' => "Professional success is indicated during this {$currentDasha} Dasha. With your {$lagna} ascendant, hard work will yield rewards.",
                'ta' => "இந்த {$currentDasha} திசையில் தொழில் ரீதியான வெற்றி நிச்சயம். உங்கள் {$fullDetails['summary']['lagna_ta']} லக்னத்திற்கு, கடின உழைப்பு நல்ல பலன் தரும்.",
                'hi' => "इस {$currentDasha} दशा के दौरान व्यावसायिक सफलता का संकेत है। आपके {$lagna} लग्न के साथ, कड़ी मेहनत रंग लाएगी।"
            ],
            'love' => [
                'en' => "Relationships will be harmonious. The current {$currentBhukti} bhukti favors emotional bonding.",
                'ta' => "உறவுகளில் மகிழ்ச்சி நிலவும். தற்போதுள்ள {$currentBhukti} புத்தி உணர்வுபூர்வமான பிணைப்பை வலுப்படுத்தும்.",
                'hi' => "रिश्ते सामंजस्यपूर्ण रहेंगे। वर्तमान {$currentBhukti} भुक्ति भावनात्मक जुड़ाव के पक्ष में है।"
            ],
            'wealth' => [
                'en' => "Financial stability is expected. Investments made now will be fruitful in the long run.",
                'ta' => "நிதி நிலை சீராக இருக்கும். இப்போது செய்யும் முதலீடுகள் நீண்ட காலத்திற்கு பலன் தரும்.",
                'hi' => "वित्तीय स्थिरता की उम्मीद है। अभी किया गया निवेश लंबी अवधि में फलदायी होगा।"
            ],
            'health' => [
                'en' => "Focus on mental wellness. Practicing yoga during this {$currentDasha} phase will be highly beneficial.",
                'ta' => "மன ஆரோக்கியத்தில் கவனம் செலுத்துங்கள். இந்த {$currentDasha} காலத்தில் யோகா செய்வது மிகுந்த பலன் தரும்.",
                'hi' => "मानसिक स्वास्थ्य पर ध्यान दें। इस {$currentDasha} चरण के दौरान योग करना अत्यधिक फायदेमंद होगा।"
            ],
            'family' => [
                'en' => "Family support will be strong. A celebration or auspicious event is likely in your household.",
                'ta' => "குடும்ப ஆதரவு பலமாக இருக்கும். உங்கள் வீட்டில் சுப நிகழ்ச்சி நடக்க வாய்ப்பு உள்ளது.",
                'hi' => "पारिवारिक सहयोग मजबूत रहेगा। आपके घर में उत्सव या शुभ अवसर की संभावना है।"
            ],
            'education' => [
                'en' => "Academic pursuits will thrive. This is an excellent time for competitive exams and higher studies.",
                'ta' => "கல்விப் பயணம் சிறப்பாக இருக்கும். போட்டித் தேர்வுகள் மற்றும் உயர் படிப்புகளுக்கு இது மிகச் சிறந்த காலம்.",
                'hi' => "शैक्षणिक प्रयास फलेंगे-फूलेंगे। यह प्रतियोगी परीक्षाओं और उच्च शिक्षा के लिए उत्कृष्ट समय है।"
            ]
        ];

        $prediction = $templates[$category][$lang] ?? $templates['career'][$lang];

        return [
            'success' => true,
            'category' => $category,
            'prediction' => $prediction,
            'dasha' => $currentDasha,
            'bhukti' => $currentBhukti,
            'lagna' => $lagna,
            'rasi' => $rasi,
            'human_explanation' => $this->generateHumanExplanation($category, $currentDasha, $lang),
            'remedy' => $this->getCategoryRemedy($category, $lang)
        ];
    }

    private function generateHumanExplanation($category, $dasha, $lang)
    {
        $texts = [
            'en' => "Based on your birth chart, the current period is governed by {$dasha}. This celestial alignment indicates that your energy should be channeled towards {$category} for maximum results.",
            'ta' => "உங்கள் ஜாதகப்படி, தற்போதைய காலம் {$dasha}-ஆல் நிர்வகிக்கப்படுகிறது. இந்த கிரக நிலை உங்கள் ஆற்றலை {$category} சார்ந்து செலுத்தினால் சிறந்த பலன் கிடைக்கும் என்பதைக் குறிக்கிறது.",
            'hi' => "आपकी जन्म कुंडली के आधार पर, वर्तमान अवधि {$dasha} द्वारा शासित है। यह आकाशीय संरेखण इंगित करता है कि आपकी ऊर्जा को अधिकतम परिणामों के लिए {$category} की ओर निर्देशित किया जाना चाहिए।"
        ];
        return $texts[$lang] ?? $texts['en'];
    }

    private function getCategoryRemedy($category, $lang)
    {
        $remedies = [
            'career' => ['en' => "Offer water to the Sun every morning.", 'ta' => "தினமும் காலையில் சூரியனுக்கு நீர் அர்ச்சனை செய்யுங்கள்."],
            'love' => ['en' => "Feed white birds on Fridays.", 'ta' => "வெள்ளிக்கிழமைகளில் வெள்ளை நிற பறவைகளுக்கு தானியம் வழங்கவும்."],
            'wealth' => ['en' => "Keep a green handkerchief in your pocket.", 'ta' => "உங்கள் பாக்கெட்டில் பச்சை நிற கைக்குட்டை வைத்திருங்கள்."],
            'health' => ['en' => "Chant Dhanvantari Mantra.", 'ta' => "தன்வந்திரி மந்திரத்தை உச்சரிக்கவும்."],
            'family' => ['en' => "Visit your ancestral temple.", 'ta' => "குலதெய்வம் கோவிலுக்கு சென்று வரவும்."],
            'education' => ['en' => "Worship Goddess Saraswati.", 'ta' => "சரஸ்வதி தேவியை வழிபாடு செய்யுங்கள்."]
        ];
        return $remedies[$category][$lang] ?? $remedies['career'][$lang];
    }
}
