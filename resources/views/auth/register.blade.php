<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!--First Name -->
        <div>
            <x-input-label for="fname" :value="__('First Name')" />
            <x-text-input 
                id="fname" 
                class="block mt-1 w-full" 
                type="text" 
                name="fname" 
                :value="old('fname')" 
                required 
                autofocus 
                autocomplete="first name" 
            />
            <x-input-error :messages="$errors->get('fname')" class="mt-2" />
        </div>

        <!--Last Name -->
        <div class="mt-4">
            <x-input-label for="lname" :value="__('Last Name')" />
            <x-text-input 
                id="lname" 
                class="block mt-1 w-full" 
                type="text" 
                name="lname" 
                :value="old('lname')" 
                required 
                autocomplete="last name" 
            />
            <x-input-error :messages="$errors->get('lname')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input 
                id="email" 
                class="block mt-1 w-full" 
                type="email" 
                name="email" 
                :value="old('email')" 
                required 
                autocomplete="username" 
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input 
                id="password" 
                class="block mt-1 w-full"
                type="password"
                name="password"
                required 
                autocomplete="new-password" 
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input 
                id="password_confirmation" 
                class="block mt-1 w-full"
                type="password"
                name="password_confirmation" 
                required 
                autocomplete="new-password" 
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            <!-- Error Message Div -->
            <div id="password-error" class="text-red-500 text-sm mt-2" style="display: none;">
                Passwords do not match.
            </div>
        </div>

        

        <!-- Country -->
        <div class="mt-4">
            <x-input-label for="country" :value="__('Country')" />
            <select 
                id="country" 
                name="country" 
                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 
                       focus:ring-indigo-500 rounded-md shadow-sm"
            >
                <option value="" selected disabled>Choose your country</option>
                @foreach($countries as $country)
                    <option 
                        value="{{ $country->id }}" 
                        @selected(old('country') == $country->id)
                    >
                        {{ $country->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('country')" class="mt-2" />
        </div>

        <!-- State -->
        <div class="mt-4">
            <x-input-label for="state" :value="__('State')" />
            <select 
                id="state" 
                name="state" 
                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 
                       focus:ring-indigo-500 rounded-md shadow-sm"
                disabled
            >
                <option value="" selected disabled>Choose your state</option>
            </select>
            <x-input-error :messages="$errors->get('state')" class="mt-2" />
        </div>

        <!-- County
        <div class="mt-4">
            <x-input-label for="county" :value="__('County')" />
            <select 
                id="county" 
                name="county" 
                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 
                       focus:ring-indigo-500 rounded-md shadow-sm"
                disabled
            >
                <option value="" selected disabled>Choose your county</option>
            </select>
            <x-input-error :messages="$errors->get('county')" class="mt-2" />
        </div>
        -->

        <!-- City -->
        <div class="mt-4">
            <x-input-label for="city" :value="__('City')" />
            <select 
                id="city" 
                name="city" 
                class="block mt-1 w-full border-gray-300 
                       focus:border-indigo-500 focus:ring-indigo-500 
                       rounded-md shadow-sm"
                disabled
            >
                <option value="" selected disabled>Choose your city</option>
            </select>
            <x-input-error :messages="$errors->get('city')" class="mt-2" />
        </div>

        <!-- Zip -->
        <div class="mt-4">
            <x-input-label for="zip" :value="__('Zip')" />
            <select 
                id="zip" 
                name="zip" 
                class="block mt-1 w-full border-gray-300 
                       focus:border-indigo-500 focus:ring-indigo-500 
                       rounded-md shadow-sm"
                disabled
            >
                <option value="" selected disabled>Choose your Zip Code</option>
            </select>
            <x-input-error :messages="$errors->get('city')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a 
                class="underline text-sm text-gray-600 hover:text-gray-900 
                       rounded-md focus:outline-none focus:ring-2 
                       focus:ring-offset-2 focus:ring-indigo-500" 
                href="{{ route('login') }}"
            >
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    <!-- JAVASCRIPT for Dependent Dropdowns -->
    <script>
        // 1) When Country is selected, fetch states
        document.getElementById('country').addEventListener('change', function() {
            const countryId = this.value;

            // Reset state & city
            const stateSelect = document.getElementById('state');
            const citySelect = document.getElementById('city');

            stateSelect.innerHTML = '<option value="" disabled selected>Choose your state</option>';
            citySelect.innerHTML = '<option value="" disabled selected>Choose your city</option>';

            // Re-disable them
            stateSelect.disabled = true;
            citySelect.disabled = true;

            // Fetch states via AJAX
            fetch(`/states/${countryId}`)
                .then(response => response.json())
                .then(states => {
                    if (states.length > 0) {
                        states.forEach(state => {
                            let option = document.createElement('option');
                            option.value = state.id;
                            option.textContent = state.name;
                            stateSelect.appendChild(option);
                        });
                        stateSelect.disabled = false;
                    } else {
                        // No states found
                        stateSelect.disabled = true;
                    }
                })
                .catch(err => console.error('Error fetching states:', err));
        });

        // 2) When State is selected, fetch cities
        document.getElementById('state').addEventListener('change', function() {
            const stateId = this.value;

            const citySelect = document.getElementById('city');
            citySelect.innerHTML = '<option value="" disabled selected>Choose your city</option>';

            citySelect.disabled = true;

            fetch(`/cities/${stateId}`)
                .then(response => response.json())
                .then(cities => {
                    if (cities.length > 0) {
                        cities.forEach(city => {
                            let option = document.createElement('option');
                            option.value = city.id;
                            option.textContent = city.name;
                            citySelect.appendChild(option);
                        });
                        citySelect.disabled = false;
                    } else {
                        citySelect.disabled = true;
                    }
                })
                .catch(err => console.error('Error fetching cities:', err));
        });
        // 3) When City is selected, fetch the zip codes of that city
        // If we are unable to fetch the zip codes for their city then skip. 
        // List of supported countries by the API we use are here https://api.zippopotam.us/#where
        // Currently our most valuable users live in the US, the API should not fail for them 
        document.getElementById('city').addEventListener('change', function () {
            const cityId = this.value;
            const zipSelect = document.getElementById('zip');

            zipSelect.innerHTML = '<option value="" disabled selected>Loading ZIP codes...</option>';
            zipSelect.disabled = true;

            fetch(`/get-zip-codes/${cityId}`)
                .then(response => {
                    console.log(response);
                    if (!response.ok) {
                        console.log("Response is not okay?");
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    console.log("Response is okay!");
                    return response.json();
                })
                .then(data => {
                    if (data.zipCodes) {
                        populateZipDropdown(data.zipCodes);
                    } else {
                        console.error('Error fetching zip codes:', data.error);
                        alert(`Error: ${data.error}`);
                    }
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    alert('An error occurred while fetching zip codes.');
                });
        });

        function populateZipDropdown(zipCodes) {
            const zipSelect = document.getElementById('zip');
            zipSelect.innerHTML = '<option value="" disabled selected>Choose your Zip Code</option>';
            zipCodes.forEach(zip => {
                const option = document.createElement('option');
                option.value = zip;
                option.textContent = zip;
                zipSelect.appendChild(option);
            });
            zipSelect.disabled = false;
        }



        function saveZipCodes(cityId, zipCodes) {
            // Post zip codes to the backend for saving
            fetch('/save-zip-codes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ cityId, zipCodes }),
            }).catch(err => console.error('Error saving zip codes to database:', err));
        }


        // If the User does not repeat their password then lets notify them before they enroll
        document.getElementById('password_confirmation').addEventListener('input', function() {
            const confPassword = this.value;
            const origPasswordValue = document.getElementById('password').value;
            const errorDiv = document.getElementById('password-error');

            if (origPasswordValue !== confPassword) {
                // Show the error message
                errorDiv.style.display = 'block';
            } else {
                // Hide the error message
                errorDiv.style.display = 'none';
            }
        });

    </script>

</x-guest-layout>
