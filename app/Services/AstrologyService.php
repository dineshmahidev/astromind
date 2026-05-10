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
            'nakshatra_idx' => $starIdx,
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
                    'desc' => $this->getPratyantardashaDesc($pntPlanet, $mainPlanet, $lang)
                ];
            }

            $bhuktis[] = [
                'planet' => $this->planets[$lang][array_search($subPlanet, $this->planets['en'])] ?? $subPlanet,
                'start' => $startDate->format('d M Y'),
                'end' => $currentDate->format('d M Y'),
                'description' => $this->getBhuktiDescTranslated($subPlanet, $mainPlanet, $lang)['main'],
                'segments' => $segments
            ];
        }
        return $bhuktis;
    }

    private function getPratyantardashaDesc($planet, $mainPlanet, $lang)
    {
        $variations = [
            'Sun' => [
                'en' => [
                    "During this {$mainPlanet} phase, the Sun's energy brings a significant boost to your confidence and leadership qualities. You will find yourself more authoritative and capable of handling complex responsibilities at work and in your personal life. It is an excellent time to seek recognition from superiors or government officials, as your efforts are likely to be noticed and rewarded. Focus on maintaining your integrity and avoiding unnecessary ego clashes to make the most of this period. Health and vitality are generally high, and you may feel a renewed sense of purpose.",
                    "With the Sun's influence in this {$mainPlanet} cycle, your professional reputation is set to rise. You will have the clarity and drive to complete long-pending projects with excellence. This is a period of peak creativity and self-expression, allowing you to shine in competitive environments. However, be mindful of being too stubborn, which could lead to friction with colleagues. Use this time to establish your authority and ensure your work reflects your true potential. Spiritual pursuits and meditation will further strengthen your mental resolve."
                ],
                'ta' => [
                    "இந்த {$mainPlanet} கட்டத்தில் சூரியனின் ஆற்றல் உங்கள் தன்னம்பிக்கை மற்றும் தலைமைத்துவ பண்புகளை கணிசமாக மேம்படுத்துகிறது. வேலையிலும் தனிப்பட்ட வாழ்க்கையிலும் சிக்கலான பொறுப்புகளைக் கையாள்வதில் நீங்கள் அதிக அதிகாரம் கொண்டவராகவும் திறமையானவராகவும் இருப்பீர்கள். உங்கள் முயற்சிகள் கவனிக்கப்பட்டு வெகுமதி அளிக்கப்பட வாய்ப்புள்ளதால், மேலதிகாரிகள் அல்லது அரசு அதிகாரிகளிடம் அங்கீகாரம் பெற இது ஒரு சிறந்த நேரமாகும். இந்த காலத்தைப் பயன்படுத்திக் கொள்ள உங்கள் நேர்மையைப் பேணுவதிலும் தேவையற்ற அகந்தை மோதல்களைத் தவிர்ப்பதிலும் கவனம் செலுத்துங்கள். ஆரோக்கியம் மற்றும் உயிர்ச்சக்தி பொதுவாக அதிகமாக இருக்கும்.",
                    "இந்த {$mainPlanet} காலக்கட்டத்தில் சூரிய ஆற்றல் உங்கள் நீண்டகால இலக்குகள் மற்றும் தொழில்முறை அபிலாஷைகளில் தெளிவைக் கொண்டுவருகிறது. தந்தை வழியில் அல்லது மூத்தவர்கள் மூலம் உங்களுக்கு நல்ல ஆதரவு கிடைக்க வாய்ப்புள்ளது. இது படைப்பாற்றல் மற்றும் சுய வெளிப்பாட்டின் உச்சக்கட்டமாகும், இது போட்டிச் சூழலில் நீங்கள் பிரகாசிக்க அனுமதிக்கும். இருப்பினும், அதிகப்படியான பிடிவாதத்தைத் தவிர்க்கவும், அது சக ஊழியர்களுடன் உராய்வுக்கு வழிவகுக்கும். உங்கள் நற்பெயரை உறுதிப்படுத்திக் கொள்ள இந்த காலத்தைப் பயன்படுத்திக் கொள்ளுங்கள். ஆன்மீக நாட்டம் மற்றும் தியானம் உங்கள் மன உறுதியை வலுப்படுத்தும்."
                ]
            ],
            'Moon' => [
                'en' => [
                    "The lunar influence during this {$mainPlanet} period focuses heavily on your emotional equilibrium and domestic happiness. You will feel a strong urge to nurture your relationships and create a peaceful environment at home. This is an ideal time for introspection, meditation, and connecting with your inner self to resolve past emotional baggage. Your intuition will be heightened, helping you make wise decisions in personal matters and identifying true allies. Pay close attention to your mental health and avoid unnecessary stress by staying close to nature or water bodies. Travel to peaceful locations or ancestral homes will bring a sense of deep fulfillment.",
                    "With the Moon's presence in this {$mainPlanet} phase, you may find your creative and imaginative powers at their peak. It is a period of growth in areas related to hospitality, psychology, food industry, or family-run businesses. You will gain deep satisfaction from supporting others and engaging in charitable activities that help the underprivileged. Financial gains through liquid assets, property, or maternal inheritance are strongly indicated during this celestial transit. Ensure you manage your mood swings effectively by maintaining a consistent routine and seeking support from family. Emotional stability will be the cornerstone of your professional success now."
                ],
                'ta' => [
                    "இந்த {$mainPlanet} காலத்தில் சந்திரனின் தாக்கம் உங்கள் உணர்ச்சி சமநிலை மற்றும் குடும்ப மகிழ்ச்சியில் அதிக கவனம் செலுத்துகிறது. உங்கள் உறவுகளை வளர்ப்பதற்கும் வீட்டில் அமைதியான சூழலை உருவாக்குவதற்கும் நீங்கள் வலுவான தூண்டுதலை உணர்வீர்கள். கடந்த கால உணர்ச்சிகரமான சுமைகளைத் தீர்க்க சுயபரிசோதனை, தியானம் மற்றும் உங்கள் உள் உணர்வுடன் இணைவதற்கு இது ஒரு சிறந்த நேரமாகும். உங்கள் உள்ளுணர்வு மேம்படும், இது தனிப்பட்ட விஷயங்களில் புத்திசாலித்தனமான முடிவுகளை எடுக்கவும் உண்மையான கூட்டாளிகளை அடையாளம் காணவும் உதவும். உங்கள் மன ஆரோக்கியத்தில் அதிக கவனம் செலுத்துங்கள் மற்றும் இயற்கையுடன் நெருக்கமாக இருப்பதன் மூலம் தேவையற்ற மன அழுத்தத்தைத் தவிர்க்கவும். அமைதியான இடங்களுக்கு அல்லது முன்னோர்களின் வீட்டிற்குப் பயணம் செய்வது ஆழ்ந்த திருப்தியைக் கொண்டுவரும்.",
                    "இந்த {$mainPlanet} கட்டத்தில் சந்திரனின் இருப்புடன், உங்கள் படைப்பாற்றல் மற்றும் கற்பனைத் திறன்கள் உச்சத்தில் இருப்பதைக் காண்பீர்கள். விருந்தோம்பல், உளவியல், உணவுத் தொழில் அல்லது குடும்பம் நடத்தும் வணிகங்கள் தொடர்பான பகுதிகளில் இது வளர்ச்சியின் காலமாகும். மற்றவர்களுக்கு ஆதரவளிப்பதன் மூலமும், பின்தங்கியவர்களுக்கு உதவும் தர்ம காரியங்களில் ஈடுபடுவதன் மூலமும் நீங்கள் ஆழ்ந்த திருப்தியைப் பெறுவீர்கள். இந்த அண்ட பெயர்ச்சியின் போது திரவ சொத்துக்கள், சொத்து அல்லது தாய்வழி பரம்பரை மூலம் நிதி ஆதாயங்கள் வலுவாகக் குறிக்கப்படுகின்றன. உங்கள் மனநிலை மாற்றங்களைச் சீரான வழக்கத்தைப் பராமரிப்பதன் மூலம் திறம்பட நிர்வகிப்பதை உறுதிசெய்து, குடும்பத்தின் ஆதரவைப் பெறுங்கள். உணர்ச்சி நிலைத்தன்மை இப்போது உங்கள் தொழில்முறை வெற்றியின் அடித்தளமாக இருக்கும்."
                ]
            ],
            'Mars' => [
                'en' => [
                    "Mars injects a surge of dynamic energy and courage into your life during this {$mainPlanet} phase. You will feel empowered to tackle difficult challenges and overcome obstacles that previously seemed insurmountable. This is a highly productive time for those in technical fields, engineering, or real estate. However, it is crucial to channel this intense energy constructively, as it can also lead to impulsiveness and heated arguments. Practice patience in your interactions and avoid making hasty decisions under pressure. Physical activities like sports or regular exercise will help balance this high-energy transit and prevent restlessness.",
                    "In this Mars-influenced period under {$mainPlanet}, your competitive spirit will be sharp and effective. You have the drive to outshine competitors and establish your dominance in your chosen field. It is a favorable time for land-related transactions, construction projects, or starting new, ambitious ventures. Be cautious with sharp objects and drive carefully to avoid minor accidents. Your brothers or close male friends may play a significant role in your life now, providing support or presenting new opportunities. Discipline and strategic planning are your keys to harnessing the power of Mars without the associated risks."
                ],
                'ta' => [
                    "இந்த {$mainPlanet} கட்டத்தில் செவ்வாய் உங்கள் வாழ்க்கையில் மாறும் ஆற்றல் மற்றும் தைரியத்தை செலுத்துகிறது. கடினமான சவால்களை எதிர்கொள்ளவும், முன்பு கடக்க முடியாததாகத் தோன்றிய தடைகளைத் தாண்டவும் நீங்கள் அதிகாரம் பெற்றதாக உணர்வீர்கள். தொழில்நுட்பத் துறைகள், பொறியியல் அல்லது ரியல் எஸ்டேட் துறைகளில் இருப்பவர்களுக்கு இது ஒரு சிறந்த காலமாகும். இருப்பினும், இந்த தீவிர ஆற்றலை ஆக்கபூர்வமாக வழிநடத்துவது முக்கியம், ஏனெனில் இது தேவையற்ற மோதல்களுக்கும் அவசர முடிவுகளுக்கும் வழிவகுக்கும். பொறுமையைக் கடைப்பிடிக்கவும் மற்றும் அழுத்தத்தின் கீழ் அவசர முடிவுகளை எடுப்பதைத் தவிர்க்கவும். விளையாட்டுகள் அல்லது வழக்கமான உடற்பயிற்சி போன்ற உடல் செயல்பாடுகள் இந்த உயர் ஆற்றல் மாற்றத்தைச் சமநிலைப்படுத்த உதவும்.",
                    "{$mainPlanet} திசையில் செவ்வாயின் செல்வாக்கு பெற்ற இந்த காலத்தில், உங்கள் போட்டி மனப்பான்மை கூர்மையாகவும் பயனுள்ளதாகவும் இருக்கும். நீங்கள் போட்டியாளர்களை விட சிறப்பாக செயல்பட்டு உங்கள் துறையில் ஆதிக்கத்தை நிலைநாட்ட முடியும். நிலம் தொடர்பான பரிவர்த்தனைகள், கட்டுமானத் திட்டங்கள் அல்லது புதிய லட்சிய முயற்சிகளைத் தொடங்க இது சாதகமான நேரமாகும். கூர்மையான பொருட்களுடன் எச்சரிக்கையாக இருங்கள் மற்றும் சிறிய விபத்துக்களைத் தவிர்க்க கவனமாக வாகனம் ஓட்டவும். உங்கள் சகோதரர்கள் அல்லது நெருங்கிய ஆண் நண்பர்கள் இப்போது உங்கள் வாழ்க்கையில் முக்கிய பங்கு வகிக்கலாம், ஆதரவை வழங்கலாம் அல்லது புதிய வாய்ப்புகளை வழங்கலாம்."
                ]
            ],
            'Mercury' => [
                'en' => [
                    "Intellectual growth and communication excellence define this Mercury phase during {$mainPlanet}. You will find it easier to learn new skills, excel in examinations, and express your thoughts clearly. It is a fantastic period for writers, students, researchers, and those in business. New networking opportunities will arise, bringing you in contact with influential people. Your wit and sense of humor will be appreciated, helping you build better rapport with both colleagues and friends. Focus on diversifying your knowledge and exploring new ways to expand your commercial ventures.",
                    "The communicative energy of Mercury in this {$mainPlanet} period enhances your analytical abilities and business acumen significantly. You will be quick to identify profitable opportunities and make strategic moves in your career. Travel for educational, research, or business purposes will be highly successful and rewarding. Success in commerce, trade, and media-related activities is strongly indicated. Ensure you keep your documents organized and maintain clarity in all your legal and financial transactions. Your logical reasoning will be your greatest asset in resolving any lingering misunderstandings."
                ],
                'ta' => [
                    "{$mainPlanet} காலத்தில் இந்த புதன் கட்டமானது அறிவுத்திறன் வளர்ச்சி மற்றும் சிறந்த தகவல் தொடர்பை வரையறுக்கிறது. புதிய திறன்களைக் கற்றுக்கொள்வது, தேர்வுகளில் சிறப்பாகச் செயல்படுவது மற்றும் உங்கள் எண்ணங்களைத் தெளிவாக வெளிப்படுத்துவது எளிதாக இருக்கும். எழுத்தாளர்கள், மாணவர்கள், ஆராய்ச்சியாளர்கள் மற்றும் வணிகர்களுக்கு இது ஒரு அருமையான காலமாகும். புதிய நெட்வொர்க்கிங் வாய்ப்புகள் உருவாகும், இது செல்வாக்கு மிக்க நபர்களுடன் உங்களைத் தொடர்பு கொள்ள வைக்கும். உங்கள் நகைச்சுவை உணர்வு பாராட்டப்படும், இது சக ஊழியர்களுடனும் நண்பர்களுடனும் சிறந்த உறவை உருவாக்க உதவும்.",
                    "இந்த {$mainPlanet} காலத்தில் புதனின் ஆற்றல் உங்கள் பகுப்பாய்வுத் திறன் மற்றும் வணிக நுணுக்கங்களை மேம்படுத்துகிறது. லாபகரமான வாய்ப்புகளை விரைவாகக் கண்டறிந்து உங்கள் தொழிலில் மூலோபாய நகர்வுகளை மேற்கொள்வீர்கள். கல்வி, ஆராய்ச்சி அல்லது வணிகப் பயணங்கள் மிகவும் வெற்றிகரமாகவும் பலனளிக்கக்கூடியதாகவும் இருக்கும். வணிகம், வர்த்தகம் மற்றும் ஊடகம் தொடர்பான செயல்பாடுகளில் வெற்றி வலுவாகக் குறிக்கப்படுகிறது. உங்கள் ஆவணங்களை ஒழுங்கமைத்து வைத்திருப்பதை உறுதிசெய்து கொள்ளுங்கள். ஏதேனும் தவறான புரிதல்களைத் தீர்ப்பதில் உங்கள் தர்க்கரீதியான வாதங்கள் சிறந்த கருவியாக இருக்கும்."
                ]
            ],
            'Jupiter' => [
                'en' => [
                    "Expansion, wisdom, and profound growth are the hallmarks of Jupiter's influence in this {$mainPlanet} phase. You will feel a deep sense of optimism and may find yourself naturally drawn to spiritual pursuits, higher learning, and seeking guidance from mentors. This is an excellent time for long-term planning, as your vision is clear and broad. Financial prospects are likely to improve, potentially through legacy, investment gains, or professional advancements. Your generosity and helpful nature will attract positive opportunities and influential allies into your life. Embrace this period of grace to enrich your soul and your surroundings.",
                    "Under Jupiter's expansive energy during {$mainPlanet}, you will experience fulfillment in family matters and may celebrate auspicious events like marriages or births. Your reputation in society will grow, and you will be respected for your wisdom and ethical conduct. It is a favorable time for undertaking long-distance travels, especially to holy sites or places of historical importance. Your ability to inspire others will be at its peak, making it an ideal time for teaching, coaching, or leadership roles. Stay grounded and continue to practice gratitude to ensure the continued flow of these divine blessings."
                ],
                'ta' => [
                    "விரிவாக்கம், ஞானம் மற்றும் ஆழமான வளர்ச்சி ஆகியவை இந்த {$mainPlanet} காலத்தில் குருவின் செல்வாக்கின் அடையாளங்களாகும். நீங்கள் ஆழ்ந்த நேர்மறை எண்ணங்களை உணர்வீர்கள் மற்றும் ஆன்மீகத் தேடல்கள், உயர்கல்வி மற்றும் மூத்தவர்களிடம் இருந்து வழிகாட்டுதல் பெறுவதில் இயற்கையாகவே ஈர்க்கப்படுவீர்கள். உங்கள் பார்வை தெளிவாகவும் விரிவாகவும் இருப்பதால், நீண்ட கால திட்டமிடலுக்கு இது ஒரு சிறந்த நேரமாகும். முதலீட்டு ஆதாயங்கள் அல்லது தொழில்முறை முன்னேற்றங்கள் மூலம் நிதி நிலை மேம்பட வாய்ப்புள்ளது. உங்கள் தாராள மனப்பான்மை மற்றும் உதவும் குணம் உங்கள் வாழ்க்கையில் நேர்மறையான வாய்ப்புகளை ஈர்க்கும்.",
                    "{$mainPlanet} திசையில் இந்த குரு புத்தி காலமானது குடும்ப விஷயங்களில் நிறைவையும் மகிழ்ச்சியையும் தருகிறது. திருமணம் அல்லது குழந்தை பிறப்பு போன்ற சுப நிகழ்ச்சிகள் உங்கள் இல்லத்தில் நடைபெறலாம். சமூகத்தில் உங்கள் நற்பெயர் வளரும், உங்கள் ஞானம் மற்றும் நெறிமுறை நடத்தைக்காக நீங்கள் மதிக்கப்படுவீர்கள். தூரப் பயணங்களை மேற்கொள்வதற்கு, குறிப்பாக புனிதத் தலங்களுக்குச் செல்ல இது சாதகமான நேரமாகும். மற்றவர்களை ஊக்குவிக்கும் உங்கள் திறன் உச்சத்தில் இருக்கும், இது கற்பித்தல் அல்லது தலைமைத்துவப் பொறுப்புகளுக்கு ஏற்ற நேரமாகும். நன்றியுணர்வுடன் இருப்பது தெய்வீக ஆசிகளைத் தொடரச் செய்யும்."
                ]
            ],
            'Venus' => [
                'en' => [
                    "Beauty, arts, and luxury will take center stage in your life during this Venus phase under {$mainPlanet}. You will find yourself more drawn to aesthetic pursuits, home decoration, and personal grooming. Relationships will flourish, and if single, there are strong chances of meeting a significant romantic partner. Financial stability will improve, often through partnerships, creative ventures, or luxury-related businesses. Your charm and diplomacy will help you resolve conflicts and build lasting social connections. Indulge in artistic hobbies and enjoy the finer things in life during this harmonious period.",
                    "The graceful energy of Venus in this {$mainPlanet} period brings harmony to your professional and personal interactions. You will excel in roles that require diplomacy, negotiation, and creative thinking. It is an auspicious time for marriage, engagements, and celebrating life's milestones with loved ones. Addition of luxury items, vehicles, or jewelry to your life is indicated. Your ability to appreciate beauty in everything will bring you deep inner peace and satisfaction. Ensure you maintain balance and avoid overspending on luxury to sustain this long-term comfort and happiness."
                ],
                'ta' => [
                    "{$mainPlanet} ஆதிக்கத்தின் கீழ் இந்த சுக்கிர காலத்தில் அழகு, கலைகள் மற்றும் ஆடம்பரம் உங்கள் வாழ்க்கையில் முக்கிய இடத்தைப் பிடிக்கும். அழகியல் சார்ந்த விஷயங்கள், வீட்டு அலங்காரம் மற்றும் சுய பராமரிப்பில் நீங்கள் அதிக ஈடுபாடு கொள்வீர்கள். உறவுகள் செழிக்கும், மேலும் திருமணமானவர்களுக்கு மகிழ்ச்சி கூடும். கூட்டாண்மை, ஆக்கபூர்வமான முயற்சிகள் அல்லது ஆடம்பரம் தொடர்பான வணிகங்கள் மூலம் நிதி நிலை மேம்படும். உங்கள் வசீகரமான பேச்சு மோதல்களைத் தீர்க்கவும் நீடித்த சமூக உறவுகளை உருவாக்கவும் உதவும். வாழ்க்கையின் இனிமையான தருணங்களை அனுபவிக்க இது ஒரு இனிய காலமாகும்.",
                    "இந்த {$mainPlanet} காலத்தில் சுக்கிரனின் நளினமான ஆற்றல் உங்கள் தொழில்முறை மற்றும் தனிப்பட்ட உறவுகளில் இணக்கத்தைக் கொண்டுவருகிறது. பேச்சுவார்த்தை மற்றும் ஆக்கபூர்வமான சிந்தனை தேவைப்படும் பணிகளில் நீங்கள் சிறந்து விளங்குவீர்கள். திருமணம், நிச்சயதார்த்தம் மற்றும் அன்புக்குரியவர்களுடன் கொண்டாட்டங்களுக்கு இது ஒரு மங்களகரமான நேரமாகும். புதிய வாகனங்கள் அல்லது ஆபரணங்கள் வாங்கும் யோகம் உண்டு. எல்லாவற்றிலும் அழகைக் காணும் உங்கள் திறன் உங்களுக்கு ஆழ்ந்த மன அமைதியையும் திருப்தியையும் தரும். இந்த மகிழ்ச்சியைத் தக்கவைக்க ஆடம்பரச் செலவுகளில் நிதானத்தைக் கடைப்பிடிக்கவும்."
                ]
            ],
            'Saturn' => [
                'en' => [
                    "This Saturn phase within the {$mainPlanet} period demands discipline, patience, and persistent hard work. While progress might seem slow or initially delayed, the foundations you build now will lead to long-term stability and success. You may face challenges that test your resilience and integrity, but overcoming them will bring significant wisdom and professional maturity. It is a time for serious planning, organizing your life, and taking responsibility for your actions. Respecting time and following a regular routine will help you navigate this transit effectively. Support from subordinates or elderly people will be beneficial.",
                    "Under the stern yet fair gaze of Saturn in this {$mainPlanet} dasha, you will learn valuable life lessons through structured effort. Professional growth is indicated for those who are willing to put in the hours and maintain honesty. Gains through property, legacy, or long-term investments are possible. You may find yourself involved in social service or helping the less fortunate, which will bring you deep spiritual satisfaction and inner peace. Avoid shortcuts and focus on fulfilling your duties towards family and society to ensure a smooth passage through this transformative phase. Success through hard labor is guaranteed."
                ],
                'ta' => [
                    "இந்த {$mainPlanet} காலத்திற்குள் இந்த சனி கட்டமானது ஒழுக்கம், பொறுமை மற்றும் விடாமுயற்சியுடன் கூடிய கடின உழைப்பைக் கோருகிறது. முன்னேற்றம் மெதுவாகத் தெரிந்தாலும், நீங்கள் இப்போது உருவாக்கும் அடித்தளம் நீண்ட கால வெற்றிக்கும் ஸ்திரத்தன்மைக்கும் வழிவகுக்கும். உங்கள் குணாதிசயத்தைச் சோதிக்கும் சவால்களை நீங்கள் சந்திக்க நேரிடலாம், ஆனால் அவற்றை வெல்வது உங்களுக்கு மிகுந்த ஞானத்தையும் மனவலிமையையும் தரும். இது தீவிரமான திட்டமிடல் மற்றும் உங்கள் செயல்களுக்குப் பொறுப்பேற்க வேண்டிய நேரமாகும். நேரத்தை மதிப்பது மற்றும் வழக்கமான நடைமுறையைப் பின்பற்றுவது இந்த காலத்தை வெற்றிகரமாகக் கடக்க உதவும். தொழிலாளர்கள் மற்றும் கீழ்நிலை ஊழியர்களின் ஒத்துழைப்பு கிட்டும்.",
                    "இந்த {$mainPlanet} திசையில் சனியின் தீவிரமான அதேசமயம் நியாயமான பார்வையின் கீழ், திட்டமிடப்பட்ட முயற்சியின் மூலம் மதிப்புமிக்க வாழ்க்கைப் பாடங்களைக் கற்றுக்கொள்வீர்கள். உழைக்கத் தயாராக இருப்பவர்களுக்கும் நேர்மையாக இருப்பவர்களுக்கும் தொழில் வளர்ச்சி நிச்சயம். சொத்துக்கள் அல்லது நீண்ட கால சேமிப்பு மூலம் நிதி ஆதாயங்கள் வரலாம். நீங்கள் சமூக சேவையில் ஈடுபடலாம் அல்லது வசதி குறைந்தவர்களுக்கு உதவலாம், இது உங்களுக்கு ஆத்மார்த்தமான திருப்தியைத் தரும். குறுக்கு வழிகளைத் தவிர்த்து, குடும்பம் மற்றும் சமூகத்திற்கான உங்கள் கடமைகளை நிறைவேற்றுவதில் கவனம் செலுத்துங்கள். பழைய சொத்துக்கள் மூலம் ஆதாயம் உண்டாகலாம்."
                ]
            ],
            'Rahu' => [
                'en' => [
                    "Rahu brings a period of intense desires and unconventional growth during this {$mainPlanet} phase. You will feel a strong drive to achieve success quickly and may explore innovative, tech-driven, or foreign-related opportunities. Sudden changes in your career path, location, or social status are likely, which can lead to significant expansion if handled with awareness. It is a time of high ambition and breaking traditional barriers, but be cautious of illusions and deceptive promises from new associates. Staying grounded and avoiding excessive risk-taking or unethical shortcuts will help you harness Rahu's power for your ultimate benefit. Foreign travel is possible.",
                    "This Rahu sub-period within {$mainPlanet} signifies a phase of breaking old patterns and exploring unknown territories in your life. You may develop a deep interest in modern technology, occult sciences, research, or international collaborations that yield high results. Your social circle will expand to include diverse, unconventional, and influential individuals who challenge your thinking. While material gains can be substantial and sudden, ensure you maintain your moral compass and avoid greed-driven practices. Practice daily meditation to keep your mind calm amidst the fast-paced changes and stay focused on your true long-term path without getting distracted."
                ],
                'ta' => [
                    "இந்த {$mainPlanet} காலத்தில் ராகு தீவிரமான ஆசைகளையும் அசாதாரண வளர்ச்சியையும் கொண்டு வருகிறார். வெற்றியை விரைவாக அடைய வேண்டும் என்ற வலுவான உந்துதலை நீங்கள் உணர்வீர்கள் மற்றும் புதுமையான அல்லது வெளிநாடு தொடர்பான வாய்ப்புகளை ஆராயலாம். உங்கள் தொழில் அல்லது இருப்பிடத்தில் திடீர் மாற்றங்கள் ஏற்பட வாய்ப்புள்ளது, இது விழிப்புணர்வுடன் கையாளப்பட்டால் குறிப்பிடத்தக்க வளர்ச்சிக்கு வழிவகுக்கும். இது அதிக லட்சியம் கொண்ட காலம், ஆனால் மாயைகள் மற்றும் ஏமாற்றும் வாக்குறுதிகளில் கவனமாக இருங்கள். நிதானத்தைக் கடைப்பிடிப்பதும் அதிகப்படியான இடர்களைத் தவிர்ப்பதும் ராகுவின் சக்தியை உங்களுக்கு சாதகமாகப் பயன்படுத்த உதவும். திடீர் பணவரவுக்கும் வாய்ப்புண்டு.",
                    "{$mainPlanet} திசையில் இந்த ராகு புத்தியானது தடைகளை உடைத்து அறியப்படாதவற்றை ஆராயும் ஒரு கட்டத்தைக் குறிக்கிறது. தொழில்நுட்பம், மறைபொருள் அறிவியல் அல்லது சர்வதேச ஒத்துழைப்புகளில் உங்களுக்கு ஆர்வம் ஏற்படலாம். உங்கள் சமூக வட்டம் பலதரப்பட்ட மற்றும் செல்வாக்கு மிக்க நபர்களை உள்ளடக்கி விரிவடையும். பொருள் ஆதாயங்கள் கணிசமாக இருந்தாலும், உங்கள் அறநெறிகளைத் தவறவிடாமல் இருப்பதையும் நெறிமுறையற்ற செயல்களைத் தவிர்ப்பதையும் உறுதிப்படுத்திக் கொள்ளுங்கள். வேகமான மாற்றங்களுக்கு இடையில் உங்கள் மனதை அமைதியாக வைத்திருக்க தினமும் தியானம் செய்யுங்கள். மற்றவர் வியக்கும் வண்ணம் உங்கள் வளர்ச்சி இருக்கும்."
                ]
            ],
            'Ketu' => [
                'en' => [
                    "The Ketu phase in this {$mainPlanet} period invites deep introspection and a significant turn toward spiritual growth and detachment. You may feel a natural detachment from material pursuits and a strong desire to seek the deeper meaning of your existence. This is a powerful time for healing past emotional traumas, practicing yoga, and letting go of what no longer serves your spiritual evolution. Your intuition and psychic abilities will be sharpened, allowing you to gain unique insights into complex situations that others might miss. Embrace solitude when needed and focus on internal transformation, self-realization, and connecting with nature.",
                    "Under Ketu's mystical influence in this {$mainPlanet} dasha, you will find success in activities related to research, spirituality, healing arts, or traditional knowledge. It is a period of shedding the old self and making way for new spiritual dimensions and philosophical clarity in your life. You may visit pilgrimage sites, stay in ashrams, or engage in deep study of ancient philosophical texts. While material progress might seem secondary or slow, you will gain an inner peace and wisdom that far outweighs worldly achievements. Avoid getting lost in confusion or isolation; instead, use this time to connect with enlightened mentors who can guide your journey."
                ],
                'ta' => [
                    "இந்த {$mainPlanet} காலத்தில் கேதுவின் கட்டமானது ஆழ்ந்த சுயபரிசோதனை மற்றும் ஆன்மீக வளர்ச்சியை நோக்கிய மாற்றத்தை அழைக்கிறது. நீங்கள் உலகாயதத் தேடல்களில் இருந்து விலகியிருக்கலாம் மற்றும் வாழ்க்கையின் ஆழமான அர்த்தத்தைத் தேடும் ஆசையை உணரலாம். கடந்த கால கவலைகளைக் குணப்படுத்தவும், இனி உங்களுக்குத் தேவையில்லாதவற்றை விட்டுவிடவும் இது ஒரு சக்திவாய்ந்த நேரமாகும். உங்கள் உள்ளுணர்வு மேம்படும், இது சிக்கலான சூழ்நிலைகளில் தனித்துவமான நுண்ணறிவுகளைப் பெற உங்களை அனுமதிக்கும். தேவைப்படும்போது தனிமையை ஏற்றுக்கொண்டு, உள்மாற்றம் மற்றும் சுய உணர்தலில் கவனம் செலுத்துங்கள். மருத்துவச் செலவுகளில் கவனம் தேவை.",
                    "இந்த {$mainPlanet} திசையில் கேதுவின் செல்வாக்கின் கீழ், ஆராய்ச்சி, ஆன்மீகம் அல்லது குணப்படுத்தும் கலைகள் தொடர்பான நடவடிக்கைகளில் நீங்கள் வெற்றியைக் காண்பீர்கள். இது பழையவற்றை அகற்றி உங்கள் வாழ்க்கையில் புதிய ஆன்மீக பரிமாணங்களுக்கு வழி வகுக்கும் ஒரு காலமாகும். நீங்கள் புனிதத் தலங்களுக்குச் செல்லலாம் அல்லது தத்துவ நூல்களில் ஆழ்ந்த படிப்பில் ஈடுபடலாம். பொருள் முன்னேற்றம் இரண்டாம் பட்சமாகத் தெரிந்தாலும், உலக சாதனைகளை விட மிஞ்சிய ஒரு உள் அமைதியை நீங்கள் பெறுவீர்கள். குழப்பம் அல்லது தனிமையில் தொலைந்து போவதைத் தவிர்க்கவும்; அதற்குப் பதிலாக உங்களுக்கு வழிகாட்டக்கூடிய சிறந்த நபர்களுடன் தொடர்புகொள்ளுங்கள். ஆத்ம ஞானம் பெருகும்."
                ]
            ]
        ];

        // Deterministically pick one of the two variations based on planet names to keep it stable
        $seed = crc32($planet . $mainPlanet);
        $vIdx = abs($seed) % 2;

        return $variations[$planet][$lang][$vIdx] ?? ($variations[$planet]['en'][$vIdx] ?? 'Positive influences expected during this planetary phase.');
    }

    private function getDashaInfoTranslated($planet, $lang)
    {
        $content = [
            'Sun' => [
                'ta' => [
                    'main' => "சூரிய தசை: இது உங்கள் வாழ்க்கையில் அதிகாரம், கௌரவம் மற்றும் தலைமைத்துவத்தின் பொற்காலம் (6 ஆண்டுகள்). இந்த காலத்தில் அரசு வழி ஆதாயங்கள் மற்றும் சமூகத்தில் அந்தஸ்து உயரும். தந்தை அல்லது தந்தை வழி உறவினர்களிடமிருந்து பெரும் ஆதரவு கிடைக்கும். உங்கள் ஆளுமைத் திறன் அனைவராலும் பாராட்டப்படும். உடல் ஆரோக்கியம் சிறப்பாக இருக்கும், குறிப்பாக கண் பார்வை மற்றும் எலும்பு வலிமை மேம்படும்.",
                    'dos' => ['தினமும் சூரிய நமஸ்காரம் செய்யவும்', 'தந்தை மற்றும் பெரியோர்களை மதிக்கவும்', 'அதிகாலையில் எழுந்து தியானம் செய்யவும்', 'ஞாயிற்றுக்கிழமைகளில் தானம் செய்யவும்', 'சிவபெருமான் வழிபாடு செய்யவும்'],
                    'donts' => ['ஆணவமாக பேசுவதைத் தவிர்க்கவும்', 'யாரையும் ஏளனமாகப் பார்க்காதீர்கள்', 'முரட்டுத்தனமான முடிவுகளைத் தவிர்க்கவும்', 'சோம்பலாக இருக்க வேண்டாம்']
                ],
                'en' => [
                    'main' => "Sun Dasha: This is a golden period of authority, prestige, and leadership in your life (6 years). During this dasha, you will receive benefits through government channels and your status in society will significantly rise. You will get great support from your father or paternal relatives. Your administrative and personality skills will be appreciated by everyone. Your physical health will be excellent, especially eye vision and bone strength will improve.",
                    'dos' => ['Perform Surya Namaskar daily', 'Respect father and elders', 'Wake up early and meditate', 'Donate on Sundays', 'Worship Lord Shiva'],
                    'donts' => ['Avoid speaking with arrogance', 'Do not look down upon anyone', 'Avoid impulsive or harsh decisions', 'Do not stay idle or lazy']
                ]
            ],
            'Moon' => [
                'ta' => [
                    'main' => "சந்திர தசை: மன அமைதி, மகிழ்ச்சி மற்றும் உணர்ச்சிவசமான வளர்ச்சியின் காலம் (10 ஆண்டுகள்). இந்த காலத்தில் தாயின் ஆதரவு மற்றும் பெண்களால் நன்மை உண்டாகும். கலை, கற்பனை மற்றும் படைப்பாற்றல் துறைகளில் இருப்பவர்களுக்கு இது மிகச் சிறந்த காலம். வெளிநாடு அல்லது நீர் சார்ந்த பயணங்கள் ஏற்படலாம். உங்கள் உள்ளுணர்வு வலுவடையும், இது சரியான முடிவுகளை எடுக்க உதவும்.",
                    'dos' => ['தாய்க்கு உதவியாக இருக்கவும்', 'திங்கட்கிழமை தோறும் அம்மன் வழிபாடு செய்யவும்', 'வெள்ளை நிற ஆடைகளை அணியவும்', 'முதியவர்களுக்கு பால் தானம் செய்யவும்', 'தியானம் மற்றும் யோகா பயிற்சி செய்யவும்'],
                    'donts' => ['தேவையற்ற மனக்கவலைகளைத் தவிர்க்கவும்', 'இரவில் குளிர்ந்த உணவுகளைத் தவிர்க்கவும்', 'தனிமையைத் தவிர்க்கவும்', 'மற்றவர்களின் உணர்வுகளைப் புண்படுத்தாதீர்கள்']
                ],
                'en' => [
                    'main' => "Moon Dasha: A period of mental peace, happiness, and emotional growth (10 years). During this time, support from mother and benefits through women are indicated. This is an excellent time for those in arts, imagination, and creative fields. Foreign travels or trips near water bodies may occur. Your intuition will be strong, helping you make the right decisions.",
                    'dos' => ['Be helpful to your mother', 'Worship Goddess Amman on Mondays', 'Wear white clothes', 'Donate milk to the elderly', 'Practice meditation and yoga'],
                    'donts' => ['Avoid unnecessary mental worries', 'Avoid cold foods at night', 'Avoid being isolated', 'Do not hurt others\' emotions']
                ]
            ],
            'Mars' => [
                'ta' => [
                    'main' => "செவ்வாய் தசை: ஆற்றல், தைரியம் மற்றும் நிலபுலன்கள் சேர்க்கைக்கான காலம் (7 ஆண்டுகள்). இந்த காலத்தில் நீங்கள் மிகவும் சுறுசுறுப்பாகவும் சவால்களை எதிர்கொள்ளும் துணிவுடனும் இருப்பீர்கள். ரியல் எஸ்டேட், பொறியியல் அல்லது சீருடைப் பணியில் இருப்பவர்களுக்கு பதவி உயர்வு கிடைக்கும். சகோதரர்களுடன் இருந்த கருத்து வேறுபாடுகள் நீங்கி ஒற்றுமை பலப்படும்.",
                    'dos' => ['முருகப்பெருமானை வழிபாடு செய்யவும்', 'உடற்பயிற்சி மற்றும் யோகா செய்யவும்', 'சகோதரர்களுக்கு உதவியாக இருக்கவும்', 'செவ்வாய்க்கிழமை விரதம் இருக்கவும்', 'ரத்த தானம் செய்வது நல்லது'],
                    'donts' => ['கோபத்தைக் கட்டுப்படுத்தவும்', 'கூர்மையான ஆயுதங்களைக் கையாளும் போது கவனம் தேவை', 'தேவையற்ற விவாதங்களைத் தவிர்க்கவும்', 'அவசரப்பட்டு நிலம் வாங்குவதைத் தவிர்க்கவும்']
                ],
                'en' => [
                    'main' => "Mars Dasha: A period for energy, courage, and accumulation of property (7 years). During this time, you will be very active and courageous in facing challenges. Promotions are likely for those in real estate, engineering, or uniform services. Misunderstandings with brothers will be resolved, strengthening unity.",
                    'dos' => ['Worship Lord Muruga', 'Engage in exercise and yoga', 'Be helpful to your brothers', 'Fast on Tuesdays', 'Blood donation is beneficial'],
                    'donts' => ['Control your anger', 'Be careful while handling sharp tools', 'Avoid unnecessary arguments', 'Avoid buying land in haste']
                ]
            ],
            'Mercury' => [
                'ta' => [
                    'main' => "புதன் தசை: அறிவுத்திறன், வியாபார வெற்றி மற்றும் தகவல் தொடர்பு மேம்படும் காலம் (17 ஆண்டுகள்). புதிய விஷயங்களைக் கற்றுக்கொள்வதற்கும் கல்வியில் சிறந்து விளங்குவதற்கும் இது ஏற்ற காலம். உங்கள் பேச்சாற்றல் மூலம் பல காரியங்களைச் சாதிப்பீர்கள். புதிய நண்பர்கள் மற்றும் உறவினர்களின் வருகை மகிழ்ச்சி தரும். கணக்கு மற்றும் கணிதம் சார்ந்த துறைகளில் லாபம் உண்டு.",
                    'dos' => ['மகாவிஷ்ணுவை வழிபாடு செய்யவும்', 'பசுவுக்கு பசுந்தீவனம் அல்லது புல் அளிக்கவும்', 'மாணவர்களுக்கு கல்வி உதவி செய்யவும்', 'புதன்கிழமை பச்சை நிற ஆடை அணியவும்', 'புதிய கலைகளைக் கற்கவும்'],
                    'donts' => ['உறுதியற்ற பேச்சுகளைத் தவிர்க்கவும்', 'மற்றவர்களின் ரகசியங்களைப் பகிராதீர்கள்', 'சதித் திட்டங்களில் ஈடுபடாதீர்கள்', 'வாக்குவாதங்களைத் தவிர்க்கவும்']
                ],
                'en' => [
                    'main' => "Mercury Dasha: A period of intellectual growth, business success, and improved communication (17 years). This is an ideal time for learning new things and excelling in education. You will achieve many things through your speech. The arrival of new friends and relatives will bring joy. Profits are indicated in fields related to accounts and mathematics.",
                    'dos' => ['Worship Lord Mahavishnu', 'Feed green grass to cows', 'Help students with their education', 'Wear green on Wednesdays', 'Learn new arts/skills'],
                    'donts' => ['Avoid indecisive talk', 'Do not share others\' secrets', 'Do not get involved in conspiracies', 'Avoid arguments']
                ]
            ],
            'Jupiter' => [
                'ta' => [
                    'main' => "குரு தசை: செல்வம், ஆன்மீகம் மற்றும் ஞானம் பெருகும் பொற்காலம் (16 ஆண்டுகள்). உங்கள் வாழ்க்கையில் பெரிய மாற்றங்கள் மற்றும் சுப நிகழ்ச்சிகள் நடைபெறும். பெரியோர்கள் மற்றும் குருமார்களின் ஆசிகள் கிடைக்கும். குழந்தைகளின் முன்னேற்றம் மகிழ்ச்சி தரும். பொருளாதார நிலை உயரும், புதிய முதலீடுகள் லாபம் தரும்.",
                    'dos' => ['தட்சிணாமூர்த்தி அல்லது குரு பகவானை வழிபாடு செய்யவும்', 'பெரியோர்கள் மற்றும் ஆசிரியர்களை மதிக்கவும்', 'வியாழக்கிழமை தானம் செய்யவும்', 'மஞ்சள் நிற ஆடைகளை அணியவும்', 'ஆன்மீக யாத்திரைகள் செல்லவும்'],
                    'donts' => ['அகந்தையைத் தவிர்க்கவும்', 'மற்றவர்களைக் குறை சொல்லாதீர்கள்', 'நேர்மையற்ற வழியில் பணம் சம்பாதிப்பதைத் தவிர்க்கவும்', 'உணவு விஷயத்தில் கவனம் தேவை']
                ],
                'en' => [
                    'main' => "Jupiter Dasha: A golden period for wealth, spirituality, and wisdom (16 years). Major positive changes and auspicious events will happen in your life. You will receive blessings from elders and gurus. Progress of children will bring joy. Financial status will improve, and new investments will be profitable.",
                    'dos' => ['Worship Lord Dakshinamurthy or Guru Bhagavan', 'Respect elders and teachers', 'Donate on Thursdays', 'Wear yellow clothes', 'Go on spiritual pilgrimages'],
                    'donts' => ['Avoid arrogance', 'Do not criticize others', 'Avoid earning money through dishonest means', 'Be careful with your diet']
                ]
            ],
            'Venus' => [
                'ta' => [
                    'main' => "சுக்கிர தசை: சொகுசு வாழ்க்கை, கலை நயம் மற்றும் திருமண மகிழ்ச்சி தரும் காலம் (20 ஆண்டுகள்). வாகனம், ஆபரணங்கள் மற்றும் புதிய வீடு வாங்கும் யோகம் உண்டு. இல்லற வாழ்க்கை மிகவும் இனிமையாக இருக்கும். கலைத்துறை மற்றும் அழகு சார்ந்த துறைகளில் இருப்பவர்களுக்கு பெரும் வெற்றி கிடைக்கும். வெளிநாட்டு பயணங்கள் மகிழ்ச்சி தரும்.",
                    'dos' => ['மகாலட்சுமியை வழிபாடு செய்யவும்', 'பெண்களை மதிக்கவும்', 'சுத்தமாகவும் நேர்த்தியாகவும் இருக்கவும்', 'வெள்ளிக்கிழமை மொச்சை தானம் செய்யவும்', 'வாசனை திரவியங்களைப் பயன்படுத்தவும்'],
                    'donts' => ['வீண் ஆடம்பரச் செலவுகளைத் தவிர்க்கவும்', 'பெண்களை அவமதிக்காதீர்கள்', 'முறையற்ற உறவுகளைத் தவிர்க்கவும்', 'நேரத்தை வீணாக்காதீர்கள்']
                ],
                'en' => [
                    'main' => "Venus Dasha: A period of luxury, artistic excellence, and marital happiness (20 years). Opportunities to buy vehicles, jewelry, and new houses will arise. Domestic life will be very pleasant. Great success is indicated for those in arts and beauty-related fields. Foreign travels will bring joy.",
                    'dos' => ['Worship Goddess Mahalakshmi', 'Respect women', 'Maintain cleanliness and neatness', 'Donate on Fridays', 'Use pleasant fragrances'],
                    'donts' => ['Avoid unnecessary luxury spending', 'Do not disrespect women', 'Avoid improper relationships', 'Do not waste time']
                ]
            ],
            'Saturn' => [
                'ta' => [
                    'main' => "சனி தசை: பொறுமை, கடின உழைப்பு மற்றும் ஒழுக்கம் தேவைப்படும் காலம் (19 ஆண்டுகள்). இந்த காலத்தில் நீங்கள் எடுக்கும் முயற்சிகள் மெதுவாக பலன் தந்தாலும், அவை நிலையானதாக இருக்கும். நீதி, நேர்மை மற்றும் உழைப்பின் மூலம் பெரிய வெற்றிகளை அடைவீர்கள். பழைய சொத்துக்கள் அல்லது இரும்பு சார்ந்த தொழில்களில் லாபம் உண்டு.",
                    'dos' => ['சனிக்கிழமை தோறும் ஆஞ்சநேயர் அல்லது சனீஸ்வரரை வழிபாடு செய்யவும்', 'ஏழைகள் மற்றும் உடல் ஊனமுற்றோருக்கு உதவவும்', 'நேர்மையாகவும் உண்மையாகவும் இருக்கவும்', 'எறும்பு மற்றும் காகத்திற்கு உணவளிக்கவும்', 'கடுமையாக உழைக்கவும்'],
                    'donts' => ['சோம்பலைத் தவிர்க்கவும்', 'மது மற்றும் போதைப் பழக்கங்களைத் தவிர்க்கவும்', 'மற்றவர்களை ஏமாற்றாதீர்கள்', 'நேரத்தை வீணாக்காதீர்கள்']
                ],
                'en' => [
                    'main' => "Saturn Dasha: A period requiring patience, hard work, and discipline (19 years). Although efforts may yield slow results, they will be stable and long-lasting. You will achieve great success through justice, honesty, and labor. Profits from ancestral property or iron-related industries are indicated.",
                    'dos' => ['Worship Lord Anjaneya or Lord Shani on Saturdays', 'Help the poor and physically challenged', 'Be honest and truthful', 'Feed ants and crows', 'Work hard with dedication'],
                    'donts' => ['Avoid laziness', 'Stay away from alcohol and addictions', 'Do not deceive others', 'Do not waste time']
                ]
            ],
            'Rahu' => [
                'ta' => [
                    'main' => "ராகு தசை: எதிர்பாராத திருப்பங்கள் மற்றும் வெளிநாட்டுத் தொடர்புகள் தரும் காலம் (18 ஆண்டுகள்). இந்த காலத்தில் வழக்கத்திற்கு மாறான வழிகளில் வெற்றி பெறுவீர்கள். ஆன்மீகம் மற்றும் நவீன தொழில்நுட்பங்களில் ஆர்வம் கூடும். திடீர் அதிர்ஷ்டம் மற்றும் புதிய கண்டுபிடிப்புகள் மூலம் லாபம் உண்டு. வெளிநாட்டு பயணங்கள் சாதகமாக இருக்கும்.",
                    'dos' => ['குலதெய்வ வழிபாடு மற்றும் துர்க்கை வழிபாடு செய்யவும்', 'விலங்குகள் மற்றும் பறவைகளுக்கு உணவளிக்கவும்', 'தினமும் தியானம் செய்யவும்', 'பெரியவர்களின் ஆலோசனையைக் கேட்கவும்', 'சனிக்கிழமை ராகு காலத்தில் விளக்கேற்றவும்'],
                    'donts' => ['குறுக்கு வழியில் பணம் சம்பாதிப்பதைத் தவிர்க்கவும்', 'புதிய நபர்களை எளிதில் நம்பாதீர்கள்', 'அதிகப்படியான ஆசையைத் தவிர்க்கவும்', 'சட்டவிரோத செயல்களில் ஈடுபடாதீர்கள்']
                ],
                'en' => [
                    'main' => "Rahu Dasha: A period of unexpected turns and foreign connections (18 years). You will achieve success in unconventional ways during this time. Interest in spirituality and modern technology will increase. Profits from sudden luck and new inventions are indicated. Foreign travels will be favorable.",
                    'dos' => ['Worship your clan deity and Goddess Durga', 'Feed animals and birds', 'Practice daily meditation', 'Listen to elders\' advice', 'Light lamps during Rahu Kaal on Saturdays'],
                    'donts' => ['Avoid earning money through shortcuts', 'Do not trust new people easily', 'Avoid excessive greed', 'Do not engage in illegal activities']
                ]
            ],
            'Ketu' => [
                'ta' => [
                    'main' => "கேது தசை: ஞானம், ஆன்மீகம் மற்றும் உள்நோக்கிய தேடல் தரும் காலம் (7 ஆண்டுகள்). இந்த காலத்தில் உலகாயதப் பற்று குறைந்து ஆன்மீகத்தில் ஈடுபாடு கூடும். யோகா, தியானம் மற்றும் மருத்துவத் துறையில் இருப்பவர்களுக்கு இது சாதகமான காலம். குடும்பத்தில் இருந்த சிக்கல்கள் விலகும். ஆன்மீக யாத்திரைகள் மன அமைதி தரும்.",
                    'dos' => ['விநாயகப் பெருமானை வழிபாடு செய்யவும்', 'நாய்களுக்கு உணவளிக்கவும்', 'ஆன்மீகப் புத்தகங்களைப் படிக்கவும்', 'தனிமையில் தியானம் செய்யவும்', 'மருத்துவ உதவி தேவைப்படுவோருக்கு உதவவும்'],
                    'donts' => ['உலகாயத இன்பங்களில் அதிகம் ஈடுபடாதீர்கள்', 'தனிமையைத் தவிர்க்கவும்', 'தேவையற்ற கவலைகளைத் தவிர்க்கவும்', 'மற்றவர்களிடம் இருந்து விலகி இருக்காதீர்கள்']
                ],
                'en' => [
                    'main' => "Ketu Dasha: A period of wisdom, spirituality, and internal searching (7 years). During this time, material attachment will decrease and spiritual involvement will increase. This is a favorable period for those in yoga, meditation, and medicine. Family issues will be resolved. Spiritual pilgrimages will bring mental peace.",
                    'dos' => ['Worship Lord Ganesha', 'Feed dogs', 'Read spiritual books', 'Practice meditation in solitude', 'Help those in need of medical assistance'],
                    'donts' => ['Do not indulge too much in material pleasures', 'Avoid being completely isolated', 'Avoid unnecessary worries', 'Do not stay disconnected from others']
                ]
            ]
        ];
        return $content[$planet][$lang] ?? ($content[$planet]['en'] ?? ['main' => "{$planet} period.", 'dos' => [], 'donts' => []]);
    }

    private function getBhuktiDescTranslated($planet, $mainPlanet, $lang)
    {
        $variations = [
            'Sun' => [
                'en' => [
                    "A period of focus on career and authority within the {$mainPlanet} dasha.",
                    "Time to shine and take leadership roles in your professional sphere."
                ],
                'ta' => [
                    "{$mainPlanet} திசையில் சூரிய புத்தி: தொழில் மற்றும் அதிகாரத்தில் கவனம் செலுத்தும் காலம்.",
                    "உங்கள் துறையில் தலைமைத்துவப் பொறுப்புகளை ஏற்க இதுவே சரியான நேரம்."
                ]
            ],
            'Moon' => [
                'en' => [
                    "Emotional stability and domestic peace are highlighted now.",
                    "Focus on mental wellness and family happiness during this sub-period."
                ],
                'ta' => [
                    "மன அமைதி மற்றும் குடும்ப மகிழ்ச்சிக்கு முக்கியத்துவம் அளிக்கும் காலம்.",
                    "மன ஆரோக்கியம் மற்றும் குடும்ப உறவுகளில் கவனம் செலுத்த வேண்டிய நேரம்."
                ]
            ],
            'Mars' => [
                'en' => [
                    "High energy period. Good for completing pending tasks.",
                    "Drive and ambition are high. Avoid impulsive decisions."
                ],
                'ta' => [
                    "அதிக ஆற்றல் கொண்ட காலம். நிலுவையில் உள்ள பணிகளை முடிக்க ஏற்றது.",
                    "லட்சியங்கள் நிறைவேறும் காலம். அவசர முடிவுகளைத் தவிர்க்கவும்."
                ]
            ],
            'Mercury' => [
                'en' => [
                    "Intellectual pursuits and business communications are favored.",
                    "Great time for learning new skills and commercial networking."
                ],
                'ta' => [
                    "அறிவுத்திறன் மற்றும் வணிகத் தொடர்புகளுக்கு சாதகமான காலம்.",
                    "புதிய திறன்களைக் கற்கவும் வணிக உறவுகளை மேம்படுத்தவும் சிறந்த நேரம்."
                ]
            ],
            'Jupiter' => [
                'en' => [
                    "Expansion and wisdom. Spiritual growth is indicated.",
                    "Favorable for wealth and gaining knowledge from mentors."
                ],
                'ta' => [
                    "வளர்ச்சி மற்றும் ஞானம் பெருகும் காலம். ஆன்மீக முன்னேற்றம் உண்டு.",
                    "செல்வம் சேரவும் பெரியோர்களிடம் இருந்து அறிவு பெறவும் சாதகமான நேரம்."
                ]
            ],
            'Venus' => [
                'en' => [
                    "Harmonious relationships and artistic enjoyment.",
                    "Focus on comfort, luxury, and creative self-expression."
                ],
                'ta' => [
                    "உறவுகளில் இணக்கமும் கலை நயமும் மிக்க காலம்.",
                    "வசதி, ஆடம்பரம் மற்றும் படைப்பாற்றலில் கவனம் செலுத்தும் நேரம்."
                ]
            ],
            'Saturn' => [
                'en' => [
                    "Period of hard work and building long-term stability.",
                    "Patience and discipline will bring substantial rewards."
                ],
                'ta' => [
                    "கடின உழைப்பு மற்றும் நீண்ட கால ஸ்திரத்தன்மையை உருவாக்கும் காலம்.",
                    "பொறுமை மற்றும் ஒழுக்கம் பெரும் பலன்களைத் தரும்."
                ]
            ],
            'Rahu' => [
                'en' => [
                    "Unexpected opportunities and unconventional growth.",
                    "Broaden your horizons and embrace new technologies."
                ],
                'ta' => [
                    "எதிர்பாராத வாய்ப்புகள் மற்றும் அசாதாரண வளர்ச்சி தரும் காலம்.",
                    "புதிய தொழில்நுட்பங்களை ஏற்றுக்கொண்டு உங்கள் அறிவை விரிவுபடுத்துங்கள்."
                ]
            ],
            'Ketu' => [
                'en' => [
                    "Inward journey and spiritual insights are highlighted.",
                    "Detach from material worries and focus on self-realization."
                ],
                'ta' => [
                    "உள்நோக்கிய பயணம் மற்றும் ஆன்மீக ஞானம் கிடைக்கும் காலம்.",
                    "உலகாயதக் கவலைகளில் இருந்து விடுபட்டு சுய உணர்தலில் கவனம் செலுத்துங்கள்."
                ]
            ]
        ];

        $seed = crc32($planet . $mainPlanet . 'bhukti');
        $vIdx = abs($seed) % 2;

        $desc = $variations[$planet][$lang][$vIdx] ?? ($variations[$planet]['en'][$vIdx] ?? "Special focus on {$planet} energy.");
        return ['main' => $desc];
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

    private function calculateTaraBala($birthStarIdx, $currentStarIdx, $lang)
    {
        // (Today's Star Index - Birth Star Index + 1) % 9
        // Note: Star indices are 0-26 (27 stars)
        $diff = ($currentStarIdx - $birthStarIdx + 27) % 27;
        $taraIdx = ($diff % 9) + 1;

        $taraData = [
            1 => [
                'name' => ['en' => 'Janma', 'ta' => 'ஜன்ம', 'hi' => 'जन्म'],
                'effect' => ['en' => 'Average/Stressful', 'ta' => 'மிதமான பலன் / மன அழுத்தம்', 'hi' => 'औसत/तनावपूर्ण'],
                'desc' => [
                    'en' => 'This is Janma Tara. It indicates a day of mental pressure or physical strain. Avoid major decisions.',
                    'ta' => 'இது ஜன்ம தாரை. மன அழுத்தம் அல்லது உடல் சோர்வைக் குறிக்கிறது. முக்கிய முடிவுகளைத் தவிர்க்கவும்.',
                    'hi' => 'यह जन्म तारा है। यह मानसिक दबाव या शारीरिक तनाव का संकेत देता है। बड़े फैसलों से बचें।'
                ]
            ],
            2 => [
                'name' => ['en' => 'Sampat', 'ta' => 'சம்பத்', 'hi' => 'सम्पत'],
                'effect' => ['en' => 'Very Auspicious', 'ta' => 'மிகவும் சுபமான பலன்', 'hi' => 'बहुत शुभ'],
                'desc' => [
                    'en' => 'Sampat Tara brings wealth and prosperity. Excellent day for financial transactions and new ventures.',
                    'ta' => 'சம்பத் தாரை செல்வம் மற்றும் செழிப்பைக் கொண்டுவருகிறது. நிதி பரிவர்த்தனைகள் மற்றும் புதிய முயற்சிகளுக்கு இது சிறந்த நாள்.',
                    'hi' => 'सम्पत तारा धन और समृद्धि लाता है। वित्तीय लेनदेन और नए उद्यमों के लिए उत्कृष्ट दिन।'
                ]
            ],
            3 => [
                'name' => ['en' => 'Vipat', 'ta' => 'விபத்', 'hi' => 'विपत'],
                'effect' => ['en' => 'Inauspicious', 'ta' => 'தடை மற்றும் இடையூறுகள்', 'hi' => 'अशुभ'],
                'desc' => [
                    'en' => 'Vipat Tara indicates obstacles and potential losses. Be careful while traveling and talking to others.',
                    'ta' => 'விபத் தாரை தடைகள் மற்றும் இழப்புகளைக் குறிக்கிறது. பயணம் செய்யும் போதும் மற்றவர்களிடம் பேசும் போதும் கவனமாக இருக்கவும்.',
                    'hi' => 'विपत तारा बाधाओं और संभावित नुकसान का संकेत देता है। यात्रा करते समय और दूसरों से बात करते समय सावधान रहें।'
                ]
            ],
            4 => [
                'name' => ['en' => 'Kshema', 'ta' => 'க்ஷேம', 'hi' => 'क्षेम'],
                'effect' => ['en' => 'Favorable', 'ta' => 'நலமும் வளமும்', 'hi' => 'अनुकूल'],
                'desc' => [
                    'en' => 'Kshema Tara ensures well-being and protection. A good day for family activities and general tasks.',
                    'ta' => 'க்ஷேம தாரை நல்வாழ்வையும் பாதுகாப்பையும் உறுதி செய்கிறது. குடும்ப நடவடிக்கைகள் மற்றும் பொதுவான பணிகளுக்கு ஒரு நல்ல நாள்.',
                    'hi' => 'क्षेम तारा कल्याण और सुरक्षा सुनिश्चित करता है। पारिवारिक गतिविधियों और सामान्य कार्यों के लिए एक अच्छा दिन।'
                ]
            ],
            5 => [
                'name' => ['en' => 'Pratyak', 'ta' => 'பிரத்யக்', 'hi' => 'प्रत्यक'],
                'effect' => ['en' => 'Obstacles', 'ta' => 'எதிர்ப்புகள் மற்றும் தடைகள்', 'hi' => 'बाधाएं'],
                'desc' => [
                    'en' => 'Pratyak Tara may bring unexpected opposition. Stay calm and avoid arguments today.',
                    'ta' => 'பிரத்யக் தாரை எதிர்பாராத எதிர்ப்புகளைக் கொண்டு வரலாம். இன்று அமைதியாக இருங்கள் மற்றும் விவாதங்களைத் தவிர்க்கவும்.',
                    'hi' => 'प्रत्यक तारा अप्रत्याशित विरोध ला सकता है। शांत रहें और आज बहस से बचें।'
                ]
            ],
            6 => [
                'name' => ['en' => 'Sadhana', 'ta' => 'சாதன', 'hi' => 'साधना'],
                'effect' => ['en' => 'Success', 'ta' => 'வெற்றி மற்றும் லாபம்', 'hi' => 'सफलता'],
                'desc' => [
                    'en' => 'Sadhana Tara is excellent for achieving goals. Your efforts will yield positive results today.',
                    'ta' => 'சாதன தாரை இலக்குகளை அடைய சிறந்தது. உங்கள் முயற்சிகள் இன்று நேர்மறையான முடிவுகளைத் தரும்.',
                    'hi' => 'साधना तारा लक्ष्यों को प्राप्त करने के लिए उत्कृष्ट है। आपके प्रयास आज सकारात्मक परिणाम देंगे।'
                ]
            ],
            7 => [
                'name' => ['en' => 'Naidhana', 'ta' => 'நைதன', 'hi' => 'नैधन'],
                'effect' => ['en' => 'Dangerous', 'ta' => 'கண்டம் மற்றும் கண்ட பலன்', 'hi' => 'खतरनाक'],
                'desc' => [
                    'en' => 'Naidhana Tara indicates danger or severe stress. Strictly avoid new beginnings or risky activities.',
                    'ta' => 'நைதன தாரை ஆபத்து அல்லது கடுமையான அழுத்தத்தைக் குறிக்கிறது. புதிய தொடக்கங்கள் அல்லது ஆபத்தான செயல்களைத் தவிர்க்கவும்.',
                    'hi' => 'नैधन तारा खतरे या गंभीर तनाव का संकेत देता है। नई शुरुआत या जोखिम भरी गतिविधियों से सख्ती से बचें।'
                ]
            ],
            8 => [
                'name' => ['en' => 'Mitra', 'ta' => 'மித்ர', 'hi' => 'मित्र'],
                'effect' => ['en' => 'Friendly', 'ta' => 'நட்பான மற்றும் மகிழ்ச்சி', 'hi' => 'अनुकूल'],
                'desc' => [
                    'en' => 'Mitra Tara brings support from friends and relatives. A pleasant day for social interactions.',
                    'ta' => 'மித்ர தாரை நண்பர்கள் மற்றும் உறவினர்களிடமிருந்து ஆதரவைக் கொண்டுவருகிறது. சமூக தொடர்புகளுக்கு ஒரு இனிமையான நாள்.',
                    'hi' => 'मित्र तारा मित्रों और रिश्तेदारों से सहयोग दिलाता है। सामाजिक मेलजोल के लिए सुखद दिन।'
                ]
            ],
            9 => [
                'name' => ['en' => 'Parama Mitra', 'ta' => 'பரம மித்ர', 'hi' => 'परम मित्र'],
                'effect' => ['en' => 'Very Good', 'ta' => 'மிகவும் உன்னதமான பலன்', 'hi' => 'बहुत अच्छा'],
                'desc' => [
                    'en' => 'Parama Mitra Tara is highly favorable. Deep satisfaction and help from unexpected sources are likely.',
                    'ta' => 'பரம மித்ர தாரை மிகவும் சாதகமானது. ஆழமான திருப்தி மற்றும் எதிர்பாராத இடங்களிலிருந்து உதவி கிடைக்க வாய்ப்புள்ளது.',
                    'hi' => 'परम मित्र तारा अत्यधिक अनुकूल है। गहरे संतोष और अप्रत्याशित स्रोतों से मदद मिलने की संभावना है।'
                ]
            ]
        ];

        return [
            'idx' => $taraIdx,
            'name' => $taraData[$taraIdx]['name'][$lang] ?? $taraData[$taraIdx]['name']['en'],
            'effect' => $taraData[$taraIdx]['effect'][$lang] ?? $taraData[$taraIdx]['effect']['en'],
            'desc' => $taraData[$taraIdx]['desc'][$lang] ?? $taraData[$taraIdx]['desc']['en']
        ];
    }

    private function getGocharSummary($signIdx, $lang)
    {
        // Simple Gochar logic based on current year/month transits (Mocked to follow actual 2024-2025 transits)
        // Saturn is in Aquarius (Kumbam - 10)
        // Jupiter is in Taurus (Rishabam - 1)
        
        $saturnPos = 10;
        $jupiterPos = 1;

        $diffSat = ($saturnPos - $signIdx + 12) % 12; // House position from Moon
        $diffJup = ($jupiterPos - $signIdx + 12) % 12;

        $saturnEffects = [
            0 => ['en' => 'Janma Sani (First phase of Sade Sati). High pressure.', 'ta' => 'ஜன்ம சனி. அதிக வேலைப்பளு மற்றும் மன அழுத்தம்.', 'hi' => 'जन्म शनि (साढ़े साती का प्रथम चरण)। उच्च दबाव।'],
            1 => ['en' => 'Pathala Sani. Focus on family and health.', 'ta' => 'பாதாள சனி. குடும்பம் மற்றும் ஆரோக்கியத்தில் கவனம் தேவை.', 'hi' => 'पाताल शनि। परिवार और स्वास्थ्य पर ध्यान दें।'],
            7 => ['en' => 'Ashtama Sani. Be very cautious in all dealings.', 'ta' => 'அஷ்டம சனி. எல்லா காரியங்களிலும் மிகவும் எச்சரிக்கையாக இருக்கவும்.', 'hi' => 'अष्टम शनि। सभी व्यवहारों में बहुत सावधान रहें।'],
            11 => ['en' => 'Viraya Sani (Starting of Sade Sati). Increased expenses.', 'ta' => 'விரய சனி. தேவையற்ற செலவுகள் அதிகரிக்க வாய்ப்பு.', 'hi' => 'व्यय शनि (साढ़े साती का आरंभ)। खर्चों में वृद्धि।'],
            2 => ['en' => 'Second phase of Sade Sati. Challenging but growth-oriented.', 'ta' => 'ஏழரை சனியின் இரண்டாம் கட்டம். சவாலான ஆனால் வளர்ச்சி தரும் காலம்.', 'hi' => 'साढ़े साती का दूसरा चरण। चुनौतीपूर्ण लेकिन विकासोन्मुख।']
        ];

        $jupiterEffects = [
            1 => ['en' => 'Jupiter in 2nd house. Wealth and family happiness.', 'ta' => 'குரு 2-ல் உள்ளார். தன வரவு மற்றும் குடும்ப மகிழ்ச்சி.', 'hi' => 'द्वितीय भाव में गुरु। धन और पारिवारिक सुख।'],
            4 => ['en' => 'Jupiter in 5th house. Gain through children and luck.', 'ta' => 'குரு 5-ல் உள்ளார். புத்திர பாக்கியம் மற்றும் அதிர்ஷ்டம்.', 'hi' => 'पंचम भाव में गुरु। संतान और भाग्य के माध्यम से लाभ।'],
            6 => ['en' => 'Jupiter in 7th house. Success in partnership and marriage.', 'ta' => 'குரு 7-ல் உள்ளார். கூட்டு தொழில் மற்றும் திருமண வாழ்வில் வெற்றி.', 'hi' => 'सप्तम भाव में गुरु। साझेदारी और विवाह में सफलता।'],
            8 => ['en' => 'Jupiter in 9th house. Divine grace and long travel.', 'ta' => 'குரு 9-ல் உள்ளார். தெய்வ அருள் மற்றும் நீண்ட தூர பயணம்.', 'hi' => 'नवम भाव में गुरु। दैवीय कृपा और लंबी यात्रा।'],
            10 => ['en' => 'Jupiter in 11th house. Financial gains and fulfilling desires.', 'ta' => 'குரு 11-ல் உள்ளார். லாபங்கள் மற்றும் விருப்பங்கள் நிறைவேறும்.', 'hi' => 'एकादश भाव में गुरु। वित्तीय लाभ और इच्छाओं की पूर्ति।']
        ];

        $satStr = $saturnEffects[$diffSat][$lang] ?? ($lang == 'ta' ? 'சனி சாதகமான நிலையில் உள்ளார்.' : 'Saturn is in a neutral position.');
        $jupStr = $jupiterEffects[$diffJup][$lang] ?? ($lang == 'ta' ? 'குரு பகவான் மிதமான பலன் தருவார்.' : 'Jupiter is providing average results.');

        return [
            'saturn' => $satStr,
            'jupiter' => $jupStr,
            'combined' => "$satStr $jupStr"
        ];
    }

    public function getHoroscope($signIdx, $period, $lang = 'en', $birthStarIdx = null)
    {
        $rasis = [
            'en' => ['Mesham', 'Rishabam', 'Mithunam', 'Kadagam', 'Simmam', 'Kanni', 'Thulaam', 'Virutchigam', 'Dhanusu', 'Magaram', 'Kumbam', 'Meenam'],
            'ta' => ['மேஷம்', 'ரிஷபம்', 'மிதுனம்', 'கடகம்', 'சிம்மம்', 'கன்னி', 'துலாம்', 'விருச்சிகம்', 'தனுசு', 'மகரம்', 'கும்பம்', 'மீனம்'],
            'hi' => ['मेष', 'वृषभ', 'मिथुन', 'कर्क', 'सिंह', 'कन्या', 'तुला', 'वृश्चिक', 'धनु', 'मकर', 'कुंभ', 'मीन']
        ];
        
        $today = new \DateTime();
        $dayOfWeek = (int) $today->format('w');
        
        $rahuTimings = ["16:30 - 18:00", "07:30 - 09:00", "15:00 - 16:30", "12:00 - 13:30", "13:30 - 15:00", "10:30 - 12:00", "09:00 - 10:30"];
        $yamaTimings = ["12:00 - 13:30", "10:30 - 12:00", "09:00 - 10:30", "07:30 - 09:00", "06:00 - 07:30", "15:00 - 16:30", "13:30 - 15:00"];
        $gulikaTimings = ["15:00 - 16:30", "13:30 - 15:00", "12:00 - 13:30", "10:30 - 12:00", "09:00 - 10:30", "07:30 - 09:00", "06:00 - 07:30"];

        $nowDetails = $this->getDetails((int) $today->format('d'), (int) $today->format('m'), (int) $today->format('Y'), (int) $today->format('H'), (int) $today->format('i'), 0);

        $gochar = $this->getGocharSummary($signIdx, $lang);
        $taraBala = null;
        if ($birthStarIdx !== null) {
            $currentStarIdx = $nowDetails['nakshatra_idx'];
            $taraBala = $this->calculateTaraBala((int) $birthStarIdx, (int) $currentStarIdx, $lang);
        }

        if ($period === 'monthly') {
            $monthOffset = (int) $today->format('n') - 1;
            $prediction = $this->getMonthlyVariation($signIdx, $monthOffset, $lang);
        } else {
            // Enhanced Daily Prediction (Dina Palan)
            if ($taraBala) {
                $prediction = $taraBala['desc'];
            } else {
                $predictions = [
                    'daily' => [
                        'en' => "Today's planetary alignment brings clarity and new opportunities for {$rasis['en'][$signIdx]}. Stay focused on your goals.",
                        'ta' => "இன்றைய கிரக நிலைகள் {$rasis['ta'][$signIdx]} ராசிக்கு தெளிவையும் புதிய வாய்ப்புகளையும் வழங்கும். உங்கள் குறிக்கோளில் கவனமாக இருங்கள்.",
                        'hi' => "आज का ग्रहों का संरेखण {$rasis['hi'][$signIdx]} के लिए स्पष्टता और नए अवसर लाता है। अपने लक्ष्यों पर केंद्रित रहें।"
                    ]
                ];
                $prediction = $predictions['daily'][$lang];
            }
        }

        $seed = (int) $today->format('Ymd') + $signIdx;
        srand($seed);

        $timeline = null;
        if ($period === 'monthly') {
            $monthNum = (int) $today->format('n');
            $timeline = $this->getMonthlyTimeline($signIdx, $monthNum, $lang);
        }

        return [
            'sign' => $rasis[$lang][$signIdx],
            'prediction' => $prediction,
            'transit_analysis' => $gochar['combined'],
            'monthly_timeline' => $timeline,
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
                'ritual' => ($lang === 'ta') ? "சூரிய வழிபாடு செய்யவும் மற்றும் பசுவிற்கு உணவளிக்கவும்." : "Worship the Sun god and feed a cow."
            ],
            'timings' => [
                'auspicious' => "10:30 AM - 12:00 PM",
                'rahu' => $rahuTimings[$dayOfWeek],
                'yama' => $yamaTimings[$dayOfWeek],
                'gulika' => $gulikaTimings[$dayOfWeek]
            ],
            'panchangam' => [
                'tithi' => $nowDetails['tithi_name'],
                'nakshatra' => $nowDetails['nakshatra'],
                'yoga' => $nowDetails['yoga'],
                'karana' => $nowDetails['karana']
            ],
            'tara_bala' => $taraBala,
            'sani_report' => $gochar['saturn'],
            'dasha_analysis' => [
                'en' => "You are currently running a favorable planetary period. Focus on discipline and consistency.",
                'ta' => "நீங்கள் தற்போது சாதகமான திசை-புத்தி காலத்தில் இருக்கிறீர்கள். ஒழுக்கம் மற்றும் விடாமுயற்சியில் கவனம் செலுத்துங்கள்.",
                'hi' => "आप वर्तमान में एक अनुकूल ग्रह अवधि चला रहे हैं। अनुशासन और निरंतरता पर ध्यान दें।"
            ]
        ];
    }

    private function getMonthlyTimeline($signIdx, $monthNum, $lang)
    {
        // Generate a 5-6 line timeline for the month
        $weeks = [
            'en' => ['Week 1: New Beginnings', 'Week 2: Financial Focus', 'Week 3: Career Growth', 'Week 4: Relationship Harmony', 'Week 5: Self-Care & Reflection'],
            'ta' => ['வாரம் 1: புதிய தொடக்கங்கள்', 'வாரம் 2: நிதி கவனம்', 'வாரம் 3: தொழில் வளர்ச்சி', 'வாரம் 4: உறவுகளில் இணக்கம்', 'வாரம் 5: சுய பாதுகாப்பு மற்றும் சிந்தனை'],
            'hi' => ['सप्ताह 1: नई शुरुआत', 'सप्ताह 2: वित्तीय फोकस', 'सप्ताह 3: करियर विकास', 'सप्ताह 4: संबंधों में सद्भाव', 'सप्ताह 5: आत्म-देखभाल और चिंतन']
        ];

        $results = [];
        srand($signIdx + $monthNum);
        
        $count = rand(5, 6);
        for ($i = 0; $i < $count; $i++) {
            $baseTitle = $weeks[$lang][$i] ?? ($lang == 'ta' ? "முக்கிய நிகழ்வு " . ($i + 1) : "Key Event " . ($i + 1));
            
            // Randomly pick a variation but keep it consistent for the month
            $desc = $this->getMonthlyVariation($signIdx, ($monthNum + $i) % 12, $lang);
            // Take just one or two sentences for the timeline item
            $sentences = explode('. ', $desc);
            $shortDesc = $sentences[rand(0, count($sentences) - 1)];

            $results[] = [
                'title' => $baseTitle,
                'description' => $shortDesc
            ];
        }
        return $results;
    }

    private function getMonthlyVariation($signIdx, $monthOffset, $lang)
    {
        $variations = [
            'en' => [
                "Focus on financial growth and stable investments this month. New opportunities for secondary income may arise through professional networking. It is a period to consolidate your assets and plan for long-term security. Your analytical skills will be your greatest strength in identifying profitable ventures. Stay disciplined with your spending to ensure a prosperous end to the month.",
                "Professional success is on the horizon. Your hard work over the past few months will finally be recognized by superiors. Expect new responsibilities that will showcase your leadership potential. This is an ideal time for career transitions or seeking promotions. Maintain your momentum and avoid office politics to ensure smooth professional growth.",
                "A period of vibrant social life and meaningful connections. You will find yourself at the center of attention in group activities and social gatherings. Now is the perfect time to strengthen existing friendships and forge new, influential bonds. Your charismatic personality will attract positive energy and supportive allies into your life. Embrace collaboration over competition.",
                "Emotional balance and domestic harmony take precedence. This month is ideal for resolving any lingering family issues and creating a peaceful sanctuary at home. Your intuition will be heightened, helping you understand the deeper needs of your loved ones. Focus on self-care and activities that nourish your soul. A peaceful home environment will provide the foundation for your external success.",
                "Creativity and self-expression are your themes for the month. Engage in artistic hobbies or creative projects that allow your unique voice to shine. You will find deep satisfaction in expressing your authentic self and inspiring others. This is also a favorable period for romance and joyful celebrations. Let your light shine brightly in everything you do.",
                "Focus on health and wellness. This month encourages you to adopt a more disciplined routine regarding your physical and mental health. Small, consistent changes in your lifestyle will lead to significant improvements in your vitality. It is a good time for a detox or starting a new fitness regime. Listen to your body and prioritize rest alongside your professional duties.",
                "Partnerships and collaborations are highly favored. Whether in business or personal life, working with others will yield better results than going solo. Focus on finding common ground and building bridges. Your diplomatic skills will be essential in resolving any conflicts and ensuring a win-win outcome for everyone involved. Trust and transparency are your keys to success.",
                "A month of transformation and deep introspection. You may feel a strong urge to shed old habits and embrace a new version of yourself. This is a powerful time for research, occult studies, or exploring the mysteries of life. Trust your gut feelings as they will guide you through complex situations. Embrace the changes as they are paving the way for your future evolution.",
                "Travel and expansion of horizons are indicated. This could be through physical travel to new locations or intellectual exploration of new philosophies and cultures. Your curiosity will lead you to fascinating discoveries and broaden your perspective on life. It is an excellent time for higher education, teaching, or sharing your wisdom with a larger audience.",
                "Career focus and public reputation are at their peak. You will be recognized for your professional achievements and ethical conduct. This is a period of peak productivity and achieving significant milestones. Your administrative skills will be appreciated, and you may find yourself in a position of greater authority. Stay grounded and continue to work with integrity.",
                "Focus on community and collective goals. Your contribution to group projects or social causes will be highly valued. This is a period to think beyond individual success and consider how you can benefit the larger society. Networking within your community will bring unexpected opportunities and a sense of belonging. Your idealism will inspire those around you.",
                "A phase of closure and spiritual preparation. Use this time to wrap up pending tasks and reflect on your journey over the past year. Meditation and spiritual practices will bring you deep inner peace and clarity. It is a stage of shedding what no longer serves you to make room for a new cycle of growth. Trust the universe as it prepares you for a rewarding future."
            ],
            'ta' => [
                "இந்த மாதம் நிதி வளர்ச்சி மற்றும் நிலையான முதலீடுகளில் கவனம் செலுத்துங்கள். தொழில்முறை தொடர்புகள் மூலம் இரண்டாம் நிலை வருமானத்திற்கான புதிய வாய்ப்புகள் உருவாகலாம். உங்கள் சொத்துக்களை ஒருங்கிணைக்கவும் நீண்ட கால பாதுகாப்பிற்காக திட்டமிடவும் இது ஒரு சிறந்த காலமாகும். லாபகரமான முயற்சிகளை அடையாளம் காண்பதில் உங்கள் பகுப்பாய்வு திறன் உங்கள் மிகப்பெரிய பலமாக இருக்கும்.",
                "தொழில்முறை வெற்றி அடிவானத்தில் உள்ளது. கடந்த சில மாதங்களாக நீங்கள் செய்த கடின உழைப்பு இறுதியாக மேலதிகாரிகளால் அங்கீகரிக்கப்படும். உங்கள் தலைமைத்துவ திறனை வெளிப்படுத்தும் புதிய பொறுப்புகளை எதிர்பார்க்கலாம். தொழில் மாற்றம் அல்லது பதவி உயர்வு பெற இது ஒரு சிறந்த நேரம். அலுவலக அரசியலைத் தவிர்த்து உங்கள் முன்னேற்றத்தில் கவனம் செலுத்துங்கள்.",
                "துடிப்பான சமூக வாழ்க்கை மற்றும் அர்த்தமுள்ள தொடர்புகளின் காலம். குழு செயல்பாடுகள் மற்றும் சமூகக் கூட்டங்களில் நீங்கள் கவனத்தின் மையமாக இருப்பீர்கள். ஏற்கனவே உள்ள நட்பை வலுப்படுத்தவும் புதிய செல்வாக்குமிக்க பிணைப்புகளை உருவாக்கவும் இப்போது சரியான நேரம். உங்கள் வசீகரமான ஆளுமை உங்கள் வாழ்க்கையில் நேர்மறை ஆற்றலையும் ஆதரவான கூட்டாளிகளையும் ஈர்க்கும்.",
                "உணர்ச்சி சமநிலை மற்றும் குடும்ப இணக்கம் முன்னுரிமை பெறுகிறது. நீடித்த குடும்பப் பிரச்சினைகளைத் தீர்ப்பதற்கும் வீட்டில் அமைதியான சூழலை உருவாக்குவதற்கும் இந்த மாதம் ஏற்றது. உங்கள் உள்ளுணர்வு மேம்படும், இது உங்கள் அன்புக்குரியவர்களின் ஆழமான தேவைகளைப் புரிந்துகொள்ள உதவும். சுய பாதுகாப்பு மற்றும் உங்கள் ஆன்மாவை வளர்க்கும் செயல்களில் கவனம் செலுத்துங்கள்.",
                "படைப்பாற்றல் மற்றும் சுய வெளிப்பாடு ஆகியவை இந்த மாதத்திற்கான உங்கள் கருப்பொருள்கள். உங்கள் தனித்துவமான குரல் பிரகாசிக்க அனுமதிக்கும் கலை பொழுதுபோக்குகள் அல்லது படைப்புத் திட்டங்களில் ஈடுபடுங்கள். உங்கள் உண்மையான சுயத்தை வெளிப்படுத்துவதிலும் மற்றவர்களை ஊக்குவிப்பதிலும் நீங்கள் ஆழ்ந்த திருப்தியைக் காண்பீர்கள். இது காதல் மற்றும் மகிழ்ச்சியான கொண்டாட்டங்களுக்கும் சாதகமான காலமாகும்.",
                "ஆரோக்கியம் மற்றும் நல்வாழ்வில் கவனம் செலுத்துங்கள். உங்கள் உடல் மற்றும் மன ஆரோக்கியம் குறித்து மிகவும் ஒழுக்கமான வழக்கத்தை கடைபிடிக்க இந்த மாதம் உங்களை ஊக்குவிக்கிறது. உங்கள் வாழ்க்கைமுறையில் சிறிய, சீரான மாற்றங்கள் உங்கள் உயிர்ச்சக்தியில் குறிப்பிடத்தக்க முன்னேற்றங்களுக்கு வழிவகுக்கும். உங்கள் உடலின் மொழியைக் கேட்டு ஓய்வுக்கும் முக்கியத்துவம் கொடுங்கள்.",
                "கூட்டாண்மை மற்றும் ஒத்துழைப்புகள் மிகவும் சாதகமானவை. வணிகமாக இருந்தாலும் சரி, தனிப்பட்ட வாழ்க்கையாக இருந்தாலும் சரி, மற்றவர்களுடன் இணைந்து பணியாற்றுவது தனியாகச் செய்வதை விட சிறந்த பலனைத் தரும். பொதுவான கருத்துக்களைக் கண்டறிந்து பாலங்களை உருவாக்குவதில் கவனம் செலுத்துங்கள். உங்கள் இராஜதந்திர திறன்கள் மோதல்களைத் தீர்ப்பதில் அவசியமாக இருக்கும்.",
                "மாற்றம் மற்றும் ஆழ்ந்த சுயபரிசோதனைக்கான மாதம். பழைய பழக்கங்களை விட்டுவிட்டு உங்களின் புதிய பதிப்பை ஏற்றுக்கொள்வதற்கான வலுவான தூண்டுதலை நீங்கள் உணரலாம். ஆராய்ச்சி அல்லது வாழ்க்கையின் ரகசியங்களை ஆராய்வதற்கு இது ஒரு சக்திவாய்ந்த நேரமாகும். சிக்கலான சூழ்நிலைகளில் உங்கள் உள்ளுணர்வு உங்களை வழிநடத்தும். மாற்றங்களை ஏற்றுக்கொள்ளுங்கள்.",
                "பயணம் மற்றும் எல்லைகளை விரிவுபடுத்துதல் ஆகியவை குறிக்கப்படுகின்றன. இது புதிய இடங்களுக்குச் செல்வதாகவோ அல்லது புதிய தத்துவங்கள் மற்றும் கலாச்சாரங்களை ஆராய்வதாகவோ இருக்கலாம். உங்கள் ஆர்வம் உங்களை கவர்ச்சிகரமான கண்டுபிடிப்புகளுக்கு அழைத்துச் செல்லும் மற்றும் வாழ்க்கையைப் பற்றிய உங்கள் பார்வையை விரிவுபடுத்தும். உயர்கல்வி அல்லது கற்பித்தலுக்கு இது ஒரு சிறந்த நேரமாகும்.",
                "தொழில் கவனம் மற்றும் பொது நற்பெயர் உச்சத்தில் உள்ளன. உங்கள் தொழில்முறை சாதனைகள் மற்றும் நெறிமுறை நடத்தைக்காக நீங்கள் அங்கீகரிக்கப்படுவீர்கள். இது உற்பத்தித்திறன் மற்றும் குறிப்பிடத்தக்க மைல்கற்களை எட்டும் காலமாகும். உங்கள் நிர்வாகத் திறன்கள் பாராட்டப்படும், மேலும் நீங்கள் அதிக அதிகாரத்தைப் பெறலாம். நேர்மையுடன் தொடர்ந்து பணியாற்றுங்கள்.",
                "சமூகம் மற்றும் கூட்டு இலக்குகளில் கவனம் செலுத்துங்கள். குழு திட்டங்கள் அல்லது சமூக காரணங்களுக்காக உங்கள் பங்களிப்பு மிகவும் மதிக்கப்படும். தனிப்பட்ட வெற்றியைத் தாண்டி பெரிய சமுதாயத்திற்கு நீங்கள் எவ்வாறு பயனடையலாம் என்பதைப் பற்றி சிந்திக்க வேண்டிய காலம் இது. சமூகத்திற்குள் தொடர்புகளை ஏற்படுத்துவது எதிர்பாராத வாய்ப்புகளைக் கொண்டுவரும்.",
                "நிறைவு மற்றும் ஆன்மீகத் தயாரிப்புக்கான ஒரு கட்டம். நிலுவையில் உள்ள பணிகளை முடிக்கவும், கடந்த ஒரு வருடத்தில் உங்கள் பயணத்தைப் பற்றி சிந்திக்கவும் இந்த நேரத்தைப் பயன்படுத்துங்கள். தியானம் மற்றும் ஆன்மீகப் பயிற்சிகள் உங்களுக்கு ஆழ்ந்த உள் அமைதியையும் தெளிவையும் தரும். உங்களுக்கு இனி உதவாதவற்றை விட்டுவிட்டு புதிய வளர்ச்சிக்கு வழி வகுக்கும் நிலை இதுவாகும்."
            ],
            'hi' => [
                "इस महीने वित्तीय विकास और स्थिर निवेश पर ध्यान दें। व्यावसायिक नेटवर्किंग के माध्यम से माध्यमिक आय के नए अवसर पैदा हो सकते हैं। यह आपकी संपत्ति को मजबूत करने और दीर्घकालिक सुरक्षा की योजना बनाने की अवधि है। लाभदायक उद्यमों की पहचान करने में आपका विश्लेषणात्मक कौशल आपकी सबसे बड़ी ताकत होगा। खर्चों में अनुशासन बनाए रखें।",
                "व्यावसायिक सफलता क्षितिज पर है। पिछले कुछ महीनों में आपकी कड़ी मेहनत को अंततः वरिष्ठों द्वारा मान्यता दी जाएगी। नई जिम्मेदारियों की अपेक्षा करें जो आपकी नेतृत्व क्षमता को प्रदर्शित करेंगी। करियर परिवर्तन या पदोन्नति चाहने के लिए यह एक आदर्श समय है। अपनी गति बनाए रखें और सुचारू विकास सुनिश्चित करने के लिए कार्यालय की राजनीति से बचें।",
                "जीवंत सामाजिक जीवन और सार्थक संबंधों की अवधि। आप समूह गतिविधियों और सामाजिक समारोहों में ध्यान का केंद्र होंगे। अब मौजूदा दोस्ती को मजबूत करने और नए, प्रभावशाली संबंध बनाने का सही समय है। आपका करिश्माई व्यक्तित्व आपके जीवन में सकारात्मक ऊर्जा और सहायक सहयोगियों को आकर्षित करेगा। प्रतिस्पर्धा के बजाय सहयोग को अपनाएं।",
                "भावनात्मक संतुलन और घरेलू सद्भाव को प्राथमिकता दी जाती है। यह महीना परिवार के किसी भी पुराने मुद्दे को सुलझाने और घर पर एक शांतिपूर्ण अभयारण्य बनाने के लिए आदर्श है। आपकी अंतर्दृष्टि बढ़ी हुई होगी, जिससे आपको अपने प्रियजनों की गहरी जरूरतों को समझने में मदद मिलेगी। आत्म-देखभाल और उन गतिविधियों पर ध्यान दें जो आपकी आत्मा को पोषण देती हैं।",
                "रचनात्मकता और आत्म-अभिव्यक्ति इस महीने के आपके विषय हैं। कलात्मक शौक या रचनात्मक परियोजनाओं में संलग्न हों जो आपकी अनूठी आवाज को चमकने दें। आपको अपने प्रामाणिक स्वरूप को व्यक्त करने और दूसरों को प्रेरित करने में गहरा संतोष मिलेगा। यह रोमांस और आनंदमय उत्सवों के लिए भी एक अनुकूल अवधि है। जो कुछ भी आप करते हैं उसमें अपनी रोशनी चमकने दें।",
                "स्वास्थ्य और कल्याण पर ध्यान दें। यह महीना आपको अपने शारीरिक और मानसिक स्वास्थ्य के संबंध में अधिक अनुशासित दिनचर्या अपनाने के लिए प्रोत्साहित करता है। आपकी जीवनशैली में छोटे, निरंतर बदलाव आपकी जीवनशक्ति में महत्वपूर्ण सुधार लाएंगे। यह डिटॉक्स या नया फिटनेस शासन शुरू करने का अच्छा समय है। अपने शरीर की सुनें और आराम को प्राथमिकता दें।",
                "साझेदारी और सहयोग अत्यधिक अनुकूल हैं। चाहे व्यवसाय में हो या व्यक्तिगत जीवन में, दूसरों के साथ काम करने से अकेले काम करने की तुलना में बेहतर परिणाम मिलेंगे। सामान्य आधार खोजने और पुल बनाने पर ध्यान दें। किसी भी संघर्ष को सुलझाने और सभी के लिए जीत की स्थिति सुनिश्चित करने में आपका कूटनीतिक कौशल आवश्यक होगा। पारदर्शिता ही सफलता की कुंजी है।",
                "परिवर्तन और गहरी आत्मनिरीक्षण का महीना। आप पुरानी आदतों को छोड़ने और खुद के एक नए संस्करण को अपनाने की तीव्र इच्छा महसूस कर सकते हैं। यह अनुसंधान, गुप्त अध्ययन या जीवन के रहस्यों की खोज के लिए एक शक्तिशाली समय है। अपनी अंतरात्मा की आवाज पर भरोसा करें क्योंकि वे जटिल स्थितियों में आपका मार्गदर्शन करेंगी। परिवर्तनों को स्वीकार करें।",
                "यात्रा और क्षितिज का विस्तार संकेतित है। यह नए स्थानों की भौतिक यात्रा या नए दर्शन और संस्कृतियों की बौद्धिक खोज के माध्यम से हो सकता है। आपकी जिज्ञासा आपको आकर्षक खोजों की ओर ले जाएगी और जीवन पर आपके दृष्टिकोण को व्यापक बनाएगी। यह उच्च शिक्षा, शिक्षण या व्यापक दर्शकों के साथ अपना ज्ञान साझा करने का एक उत्कृष्ट समय है।",
                "करियर फोकस और सार्वजनिक प्रतिष्ठा अपने चरम पर है। आपको अपनी व्यावसायिक उपलब्धियों और नैतिक आचरण के लिए पहचाना जाएगा। यह चरम उत्पादकता और महत्वपूर्ण मील के पत्थर हासिल करने की अवधि है। आपके प्रशासनिक कौशल की सराहना की जाएगी, और आप अधिक अधिकार की स्थिति में हो सकते हैं। जमीन से जुड़े रहें और ईमानदारी के साथ काम करना जारी रखें।",
                "समुदाय और सामूहिक लक्ष्यों पर ध्यान दें। समूह परियोजनाओं या सामाजिक कार्यों में आपका योगदान अत्यधिक मूल्यवान होगा। यह व्यक्तिगत सफलता से परे सोचने और यह विचार करने की अवधि है कि आप बड़े समाज को कैसे लाभ पहुंचा सकते हैं। अपने समुदाय के भीतर नेटवर्किंग अप्रत्याशित अवसर और अपनेपन की भावना लाएगी। आपका आदर्शवाद आपके आसपास के लोगों को प्रेरित करेगा।",
                "समापन और आध्यात्मिक तैयारी का एक चरण। लंबित कार्यों को पूरा करने और पिछले वर्ष की अपनी यात्रा पर विचार करने के लिए इस समय का उपयोग करें। ध्यान और आध्यात्मिक अभ्यास आपको गहरी आंतरिक शांति और स्पष्टता प्रदान करेंगे। यह उस चीज को छोड़ने का चरण है जो अब आपकी सेवा नहीं करती है ताकि विकास के एक नए चक्र के लिए जगह बन सके। ब्रह्मांड पर भरोसा रखें।"
            ]
        ];

        $idx = ($signIdx + $monthOffset) % 12;
        return $variations[$lang][$idx] ?? ($variations['en'][$idx] ?? "Favorable influences expected this month.");
    }

    public function getFuturePrediction($day, $month, $year, $hour, $minute, $category, $lang = 'en', $qIdx = 0)
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
        $rasiIdx = array_search($rasi, $this->rasis['en']);

        // --- Planet Friendliness to Lagna ---
        $planetFriends = [
            'Mesham' => ['Sun', 'Mars', 'Jupiter'],
            'Rishabam' => ['Mercury', 'Venus', 'Saturn'],
            'Mithunam' => ['Venus', 'Mercury', 'Saturn'],
            'Kadagam' => ['Moon', 'Mars', 'Jupiter'],
            'Simmam' => ['Sun', 'Mars', 'Jupiter'],
            'Kanni' => ['Mercury', 'Venus'],
            'Thulaam' => ['Venus', 'Mercury', 'Saturn'],
            'Virutchigam' => ['Mars', 'Moon', 'Jupiter'],
            'Dhanusu' => ['Jupiter', 'Sun', 'Mars'],
            'Magaram' => ['Saturn', 'Venus', 'Mercury'],
            'Kumbam' => ['Saturn', 'Venus', 'Mercury'],
            'Meenam' => ['Jupiter', 'Moon', 'Mars']
        ];

        $isFriendly = in_array($currentDasha, $planetFriends[$lagna] ?? []);
        
        $templates = [
            'career' => [
                0 => [ // When will I get a new job?
                    'en' => $isFriendly ? "The transition is very near. Your {$currentDasha} Dasha lord is positioning itself in a way that creates a new career path within the next few months." : "Expect a slight delay. The current {$currentDasha} period suggests you should refine your skills before a major opportunity arrives.",
                    'ta' => $isFriendly ? "புதிய வேலை வாய்ப்பு மிக அருகில் உள்ளது. உங்கள் {$currentDasha} திசை நாதன் அடுத்த சில மாதங்களில் புதிய தொழில் பாதையை உருவாக்குகிறார்." : "சிறிது தாமதம் ஏற்படலாம். தற்போதைய {$currentDasha} காலம் ஒரு பெரிய வாய்ப்பு வருவதற்கு முன்பு உங்கள் திறமைகளை வளர்த்துக் கொள்ள வேண்டும் என்று அறிவுறுத்துகிறது."
                ],
                1 => [ // Will I be successful in my business?
                    'en' => "Success in business is strongly indicated by the presence of {$currentBhukti} in your career house. Expansion plans will yield good returns.",
                    'ta' => "உங்கள் தொழில் ஸ்தானத்தில் {$currentBhukti} இருப்பதால் தொழிலில் வெற்றி நிச்சயம். விரிவாக்கத் திட்டங்கள் நல்ல பலனைத் தரும்."
                ],
                2 => [ // When will my financial status improve?
                    'en' => "Your financial growth is linked to the movement of Jupiter. As you are in {$currentDasha} dasha, expect wealth accumulation to start after the current quarter.",
                    'ta' => "உங்கள் நிதி வளர்ச்சி குருவின் இயக்கத்துடன் தொடர்புடையது. நீங்கள் {$currentDasha} திசையில் இருப்பதால், இந்த காலாண்டிற்குப் பிறகு செல்வம் சேரத் தொடங்கும்."
                ]
            ],
            'love' => [
                0 => [ // When will I meet my soulmate?
                    'en' => "The 7th house lord is becoming active in your chart. The {$currentBhukti} phase between now and next year is prime for meeting your soulmate.",
                    'ta' => "உங்கள் ஜாதகத்தில் 7-ம் வீட்டு அதிபதி வலுப்பெறுகிறார். இப்போது முதல் அடுத்த ஆண்டு வரையிலான {$currentBhukti} காலம் உங்கள் ஆத்மார்த்தமான துணையைச் சந்திக்க ஏற்றது."
                ],
                1 => [ // Love or arranged?
                    'en' => "The influence of Venus and Rahu in your chart points towards a relationship that combines traditional values with modern personal choice.",
                    'ta' => "உங்கள் ஜாதகத்தில் சுக்கிரன் மற்றும் ராகுவின் தாக்கம் பாரம்பரிய விழுமியங்களுடன் தனிப்பட்ட விருப்பத்தையும் இணைக்கும் ஒரு உறவைக் குறிக்கிறது."
                ]
            ]
            // Add more as needed, but let's provide a default fallback
        ];

        // Fallback logic if qIdx or Category is not explicitly defined in the detailed templates
        if (!isset($templates[$category][$qIdx])) {
            $prediction = ($lang == 'ta') 
                ? "உங்கள் {$lagna} லக்னத்திற்கு இந்த {$currentDasha} காலம் மாற்றங்களைக் கொண்டு வரும். {$currentBhukti} புத்தி உங்கள் விருப்பங்களை நிறைவேற்ற உதவும்."
                : "For your {$lagna} ascendant, this {$currentDasha} period brings significant shifts. The {$currentBhukti} bhukti will help fulfill your desires.";
        } else {
            $prediction = $templates[$category][$qIdx][$lang] ?? $templates[$category][$qIdx]['en'];
        }

        return [
            'success' => true,
            'category' => $category,
            'prediction' => $prediction,
            'dasha' => $currentDasha,
            'bhukti' => $currentBhukti,
            'lagna' => $lagna,
            'rasi' => $rasi,
            'human_explanation' => $this->generateHumanExplanation($category, $currentDasha, $lang, $lagna, $isFriendly),
            'remedy' => $this->getCategoryRemedy($category, $lang)
        ];
    }

    private function generateHumanExplanation($category, $dasha, $lang, $lagna = '', $isFriendly = false)
    {
        $status = $isFriendly ? ($lang == 'ta' ? 'சாதகமான' : 'supportive') : ($lang == 'ta' ? 'மிதமான' : 'challenging');
        
        $texts = [
            'en' => "Based on your birth chart, the current period is governed by {$dasha}. This planet is particularly {$status} for your {$lagna} ascendant, meaning your energy should be channeled towards {$category} for maximum results.",
            'ta' => "உங்கள் ஜாதகப்படி, தற்போதைய காலம் {$dasha}-ஆல் நிர்வகிக்கப்படுகிறது. உங்கள் {$lagna} லக்னத்திற்கு இந்த கிரகம் {$status} பலன்களைத் தருகிறது. எனவே, சிறந்த முடிவுகளுக்கு உங்கள் ஆற்றலை {$category} சார்ந்து செலுத்துவது நல்லது.",
            'hi' => "आपकी जन्म कुंडली के आधार पर, वर्तमान अवधि {$dasha} द्वारा शासित है। यह ग्रह आपके {$lagna} लग्न के लिए विशेष रूप से {$status} है, जिसका अर्थ है कि अधिकतम परिणामों के लिए आपकी ऊर्जा को {$category} की ओर निर्देशित किया जाना चाहिए।"
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
