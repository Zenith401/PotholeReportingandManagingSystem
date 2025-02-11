<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Update Location') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Update your location details.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update-location') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <!-- Country -->
        <div>
            <x-input-label for="country" :value="__('Country')" />
            <select id="country" name="country" class="block mt-1 w-full" required>
                <option value="" disabled {{ !$user->country ? 'selected' : '' }}>
                    {{ __('Select Country') }}
                </option>
                <option value="US" {{ old('country', $user->country) === 'US' ? 'selected' : '' }}>
                    United States
                </option>
                @foreach ($countries as $country)
                    @if ($country->iso2 !== 'US')
                        <option value="{{ $country->iso2 }}" {{ old('country', $user->country) === $country->iso2 ? 'selected' : '' }}>
                            {{ $country->name }}
                        </option>
                    @endif
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('country')" />
        </div>

        <!-- State -->
        <div>
            <x-input-label for="state" :value="__('State')" />
            <select id="state" name="state" class="block mt-1 w-full" required>
                <option value="" disabled {{ !$user->state ? 'selected' : '' }}>
                    {{ __('Select State') }}
                </option>
                @foreach ($states as $state)
                    <option value="{{ $state->iso2 }}" {{ old('state', $user->state) === $state->iso2 ? 'selected' : '' }}>
                        {{ $state->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('state')" />
        </div>

        <!-- City -->
        <div>
            <x-input-label for="city" :value="__('City')" />
            <select id="city" name="city" class="block mt-1 w-full" required>
                <option value="" disabled {{ !$user->city ? 'selected' : '' }}>
                    {{ __('Select City') }}
                </option>
                @foreach ($cities as $city)
                    <option value="{{ $city->id }}" {{ old('city', $user->city) === $city->name ? 'selected' : '' }}>
                        {{ $city->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('city')" />
        </div>

        <!-- ZIP Code -->
        <div>
            <x-input-label for="zip" :value="__('ZIP Code')" />
            <x-text-input id="zip" name="zip" type="text" class="block mt-1 w-full" :value="old('zip', $user->zip_code)" required />
            <x-input-error :messages="$errors->get('zip')" />
        </div>

        <!-- Submit Button -->
        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Update Location') }}</x-primary-button>
            @if (session('status') === 'location-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
