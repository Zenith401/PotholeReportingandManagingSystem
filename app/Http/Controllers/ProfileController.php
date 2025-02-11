<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        $countries = DB::table('countries')
            ->select('id', 'name', 'iso2')
            ->orderByRaw('(name = "United States") DESC, name ASC')
            ->get();

        $states = $user->country
            ? DB::table('states')
                ->select('id', 'name', 'iso2')
                ->where('country_code', $user->country)
                ->orderBy('name')
                ->get()
            : collect();

        $cities = $user->state
            ? DB::table('cities')
                ->select('id', 'name', 'zip_codes')
                ->where('state_code', $user->state)
                ->orderBy('name')
                ->get()
            : collect();

        return view('profile.edit', compact('user', 'countries', 'states', 'cities'));
    }

    public function updateLocation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'country' => 'required|exists:countries,iso2',
            'state'   => 'required|exists:states,iso2',
            'city'    => 'required|exists:cities,id',
            'zip'     => 'required|string|max:10',
        ]);
    
        $user = $request->user();
    
        $cityName = DB::table('cities')->where('id', $validated['city'])->value('name');
    
        if (!$cityName) {
            return Redirect::route('profile.edit')->withErrors(['city' => 'Invalid city selected.']);
        }
    
        $user->update([
            'country'  => $validated['country'],
            'state'    => $validated['state'],
            'city'     => $cityName,
            'zip_code' => $validated['zip'],
        ]);
    
        return Redirect::route('profile.edit')->with('status', 'location-updated');
    }

    public function getStates($countryIso2): JsonResponse
    {
        $states = DB::table('states')
            ->select('id', 'name', 'iso2')
            ->where('country_code', $countryIso2)
            ->orderBy('name')
            ->get();

        return response()->json($states);
    }

    public function getCities($stateIso2): JsonResponse
    {
        $cities = DB::table('cities')
            ->select('id', 'name', 'zip_codes')
            ->where('state_code', $stateIso2)
            ->orderBy('name')
            ->get();
    
        return response()->json($cities);
    }

    public function getZipCodes($cityId): JsonResponse
    {
        try {
            $city = DB::table('cities')->where('id', $cityId)->first();
    
            if (!$city) {
                return response()->json(['error' => 'City not found'], 404);
            }
    
            $zipCodes = $city->zip_codes ? explode(',', $city->zip_codes) : [];
    
            return response()->json($zipCodes);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $request->user()->id,
        ]);
    
        $user = $request->user();
    
        // Check if the email has been changed
        if ($validated['email'] !== $user->email) {
            $user->email_verified_at = null; // Mark email as unverified
            session()->flash('email_update', true); // Set this flag for the email update
            $user->sendEmailVerificationNotification(); // Send verification email
        }
    
        // Update user details
        $user->update($validated);
    
        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }
    
    
}
