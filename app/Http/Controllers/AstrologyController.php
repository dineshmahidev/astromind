<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AstrologyService;

class AstrologyController extends Controller
{
    protected $astrologyService;

    public function __construct(AstrologyService $astrologyService)
    {
        $this->astrologyService = $astrologyService;
    }

    public function getDetails(Request $request)
    {
        $request->validate([
            'year' => 'required|numeric',
            'month' => 'required|numeric|between:1,12',
            'day' => 'required|numeric|between:1,31',
            'hour' => 'required|numeric|between:0,23',
            'minute' => 'required|numeric|between:0,59',
            'second' => 'required|numeric|between:0,59',
        ]);

        $data = $this->astrologyService->getDetails(
            $request->day,
            $request->month,
            $request->year,
            $request->hour,
            $request->minute,
            $request->second
        );

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function getMatch(Request $request)
    {
        $request->validate([
            'girl_star' => 'required|string',
            'boy_star' => 'required|string',
            'lang' => 'nullable|string'
        ]);

        $poruthams = $this->astrologyService->getMatching(
            $request->girl_star,
            $request->boy_star,
            $request->lang ?? 'en'
        );

        return response()->json([
            'success' => true,
            'poruthams' => $poruthams
        ]);
    }

    public function getChart(Request $request)
    {
        try {
            $request->validate([
                'day' => 'required|numeric',
                'month' => 'required|numeric',
                'year' => 'required|numeric',
                'hour' => 'required|numeric',
                'minute' => 'required|numeric',
                'lat' => 'nullable|numeric',
                'lon' => 'nullable|numeric',
                'lang' => 'nullable|string'
            ]);

            $data = $this->astrologyService->getFullDetails(
                $request->day,
                $request->month,
                $request->year,
                $request->hour,
                $request->minute,
                0,
                $request->lang ?? 'en',
                $request->lat ?? 11.3410, // Default to Erode
                $request->lon ?? 77.7172
            );

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Kundli Validation Error: ', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Kundli Service Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Service error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getHoroscope(Request $request)
    {
        $request->validate([
            'sign_idx' => 'required|integer',
            'period' => 'required|string',
            'lang' => 'nullable|string'
        ]);

        $data = $this->astrologyService->getHoroscope(
            $request->sign_idx,
            $request->period,
            $request->lang ?? 'en'
        );

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function getPrediction(Request $request)
    {
        $request->validate([
            'day' => 'required|numeric',
            'month' => 'required|numeric',
            'year' => 'required|numeric',
            'hour' => 'required|numeric',
            'minute' => 'required|numeric',
            'category' => 'required|string',
            'lang' => 'nullable|string'
        ]);

        $data = $this->astrologyService->getFuturePrediction(
            $request->day,
            $request->month,
            $request->year,
            $request->hour,
            $request->minute,
            $request->category,
            $request->lang ?? 'en'
        );

        return response()->json($data);
    }
}
