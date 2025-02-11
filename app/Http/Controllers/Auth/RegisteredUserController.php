<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration form with the list of countries.
     */
    public function create()
    {
        $countries = DB::table('countries')
            ->select('id', 'name')
            ->orderByRaw("CASE WHEN name = 'United States' THEN 0 ELSE 1 END, name ASC")
            ->get();

        return view('auth.register', compact('countries'));
    }

    /**
     * Handle registration.
     */
    public function store(Request $request)
    {
        // Validate user input
        $request->validate([
            'fname'    => ['required', 'string', 'max:255'],
            'lname'    => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'country'  => ['required', 'exists:countries,id'],
            'state'    => ['required', 'exists:states,id'],
            'city'     => ['required', 'exists:cities,id'],
            'zip'      => ['required', 'string', 'max:10'],
        ]);

        // Retrieve country, state, and city names
        $countryName = DB::table('countries')->where('id', $request->country)->value('iso2');
        $stateName   = DB::table('states')->where('id', $request->state)->value('iso2');
        $cityName    = DB::table('cities')->where('id', $request->city)->value('name');

        // Create the user
        $user = User::create([
            'fname'    => $request->fname,
            'lname'    => $request->lname,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 1, // Assign a default role
            'country'  => $countryName,
            'state'    => $stateName,
            'city'     => $cityName,
            'zip_code' => $request->zip,
        ]);

        // Trigger email verification notification
        event(new Registered($user));

        // Log in the user
        Auth::login($user);

        // Redirect to the email verification page
        return redirect()->route('verification.notice')->with('verification_context', 'registration');
    }

    /**
     * AJAX: Fetch states by country.
     */
    public function getStates($countryId)
    {
        \Log::info('Fetching states for country: ' . $countryId);
        $states = DB::table('states')
            ->where('country_id', $countryId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($states);
    }

    /**
     * AJAX: Fetch cities by state.
     */
    public function getCities($stateId)
    {
        $cities = DB::table('cities')
            ->where('state_id', $stateId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($cities);
    }

    public function getZipCodes($cityId)
    {
        try {
            \Log::info('Fetching ZIP codes for city ID: ' . $cityId);
    
            // Fetch the zip_codes column from the cities table
            $city = DB::table('cities')->where('id', $cityId)->first();
    
            if (!$city) {
                \Log::warning('City not found for ID: ' . $cityId);
                return response()->json(['error' => 'City not found.'], 404);
            }
    
            // Parse the zip_codes column, assuming it contains a comma-separated list
            $zipCodes = explode(',', $city->zip_codes);
    
            if (empty($zipCodes)) {
                \Log::warning('No ZIP codes found for city ID: ' . $cityId);
                return response()->json(['error' => 'No ZIP codes found.'], 404);
            }
    
            return response()->json(['zipCodes' => $zipCodes]);
        } catch (\Exception $e) {
            \Log::error('Error fetching ZIP codes: ' . $e->getMessage());
            return response()->json(['error' => 'An internal server error occurred.'], 500);
        }
    }
    

}
