<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AstrologyService;
use App\Services\MarriageService;

class MarriageController extends Controller
{
    public function predict(Request $request)
    {
        $request->validate([
            'day'    => 'required|integer|min:1|max:31',
            'month'  => 'required|integer|min:1|max:12',
            'year'   => 'required|integer|min:1900|max:2100',
            'hour'   => 'required|integer|min:0|max:23',
            'minute' => 'required|integer|min:0|max:59',
            'gender' => 'sometimes|string|in:male,female',
        ]);

        $astro   = new AstrologyService();
        $service = new MarriageService();

        $jd       = $astro->getJulianDay($request->day, $request->month, $request->year, $request->hour, $request->minute, 0);
        $ayanamsa = $astro->getLahiriAyanamsa($jd);

        // Get sidereal positions of all planets
        $rawPositions = [
            'Sun'     => $astro->getSunLong($jd),
            'Moon'    => $astro->estimateMoonLongitude($jd),
            'Mars'    => $astro->getMarsLong($jd),
            'Mercury' => $astro->getMercuryLong($jd),
            'Jupiter' => $astro->getJupiterLong($jd),
            'Venus'   => $astro->getVenusLong($jd),
            'Saturn'  => $astro->getSaturnLong($jd),
            'Rahu'    => $astro->getRahuLong($jd),
        ];
        $rawPositions['Ketu'] = fmod($rawPositions['Rahu'] + 180, 360);

        // Apply ayanamsa
        $siderealPositions = [];
        foreach ($rawPositions as $planet => $long) {
            $siderealPositions[$planet] = fmod($long - $ayanamsa + 360, 360);
        }

        // Lagna (approximate)
        $sunSidereal = $siderealPositions['Sun'];
        $lagnaLong   = fmod($sunSidereal + ($request->hour + $request->minute / 60) * 15, 360);
        $lagnaRasiIdx = (int)($lagnaLong / 30);

        // Get basic birth info
        $moonSidereal = $siderealPositions['Moon'];
        $rasiIdx  = (int)($moonSidereal / 30);
        $starIdx  = (int)($moonSidereal / (360 / 27));
        $stars = ['Aswini','Bharani','Karthigai','Rohini','Mirugaseerisham','Thiruvathirai','Punarpusam','Poosam','Ayilyam','Magam','Pooram','Uthiram','Hastham','Chithirai','Swathi','Visakam','Anusham','Kettai','Moolam','Pooradam','Uthiradam','Thiruvonam','Avittam','Sadhayam','Pooratathi','Uthiratathi','Revathi'];
        $rasis = ['Mesha','Vrishabha','Mithuna','Karka','Simha','Kanni','Tula','Vrischika','Dhanusu','Makara','Kumbha','Meena'];

        $lang = $request->lang ?? 'en';
        $result = $service->predict($siderealPositions, $lagnaRasiIdx, $request->gender ?? 'male', $lang);
        $result['birth_info'] = [
            'rasi'      => $rasis[$rasiIdx],
            'nakshatra' => $stars[$starIdx],
            'lagna'     => $rasis[$lagnaRasiIdx],
            'padam'     => $astro->getPadam($moonSidereal),
        ];

        // Dasha-based marriage timing with peak muhurta dates
        $result['peak_marriage_days'] = $service->getPeakMarriageDays(
            $request->day, $request->month, $request->year,
            $moonSidereal,
            $result['seventh_lord'],
            $lang
        );

        return response()->json(['success' => true, 'data' => $result]);
    }
}
