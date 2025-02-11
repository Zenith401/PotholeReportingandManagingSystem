<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse; // Ensure this is imported

class LocationController extends Controller
{
    /**
     * Return list of states based on country_id.
     */
    public function getStates($countryId): JsonResponse
    {
        $states = DB::table('states')
            ->select('id', 'name')
            ->where('country_id', $countryId) // Adjust field name if different
            ->orderBy('name')
            ->get();
    
        return response()->json($states);
    }
    
    

    /**
     * Return list of cities based on state_id.
     */
    public function getCities($stateId): JsonResponse
    {
        $cities = DB::table('cities')
            ->select('id', 'name')
            ->where('state_id', $stateId) // Ensure the column name matches your database schema
            ->orderBy('name')
            ->get();
    
        return response()->json($cities);
    }
    
    /**
     * Return zip codes for a city based on city_id.
     */
    public function getZipCodes($cityId)
    {
        try {
            // Fetch city, state, and country information
            $city = DB::table('cities')->where('id', $cityId)->first();

            // We offer the city choices to the user
            // It will be impossible for them to select a $city not in the database
            // If this statment occurs check database integrity
            if (!$city) {
                return response()->json(['error' => 'City not found'], 404);
            }

            $stateId = $city->state_id;
            $countryId = $city->country_id;

            // Fetch necessary values for the API
            $countryName = DB::table('countries')->where('id', $countryId)->value('iso2'); // Country ISO2 code
            $stateName   = DB::table('states')->where('id', $stateId)->value('iso2');     // State ISO2 code
            $cityName    = $city->name;                                                  // City name

            // Check if zip codes already exist in the database
            if (!empty($city->zip_codes)) {
                $zipCodes = explode(',', $city->zip_codes);
                return response()->json(['zipCodes' => $zipCodes]);
            }

            // Call Zippopotam.us API to fetch zip codes
            $response = Http::get("https://api.zippopotam.us/{$countryName}/{$stateName}/{$cityName}");

            if ($response->ok()) {
                // Filter places to only include matching city names
                $zipCodes = collect($response->json()['places'])
                    ->filter(fn($place) => strtolower($place['place name']) === strtolower($cityName))
                    ->pluck('post code')
                    ->toArray();

                // Save the zip codes to the database if any are found
                if (!empty($zipCodes)) {
                    $this->saveZipCodes($cityId, $zipCodes);
                }

                return response()->json(['zipCodes' => $zipCodes]);
            }

            return response()->json(['error' => 'Unable to fetch zip codes from the API'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Save zip codes for a city into the database.
     * We need to parse the response as the API gives us 'similar' results
     * Ex: "Orland, CA" should only return 95963
     * The API returns Orland (95963) and Westmorland (92281), Both contain the word "orland"
     */
    private function saveZipCodes($cityId, array $zipCodes)
    {
        $zipCodesString = implode(',', $zipCodes);
    
        DB::table('cities')
            ->where('id', $cityId)
            ->update(['zip_codes' => $zipCodesString]);
    }
    

    public function getCountries(): JsonResponse
    {
        try {
            $countries = DB::table('countries')
                ->select('id', 'name', 'iso2')
                ->orderByRaw('(name = "United States") DESC, name ASC') // Ensure "United States" is listed first
                ->get();
    
            return response()->json($countries);
        } catch (\Exception $e) {
            \Log::error('Error fetching countries:', ['exception' => $e]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
    
}