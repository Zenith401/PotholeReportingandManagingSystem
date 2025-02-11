<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Update Profile Information -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                @include('profile.partials.update-profile-information-form')
            </div>

            <!-- Update Password -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                @include('profile.partials.update-password-form')
            </div>

            <!-- Update Location -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                @include('profile.partials.update-location-form')
            </div>

            <!-- Delete Account -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>

    @vite(['resources/js/profile-location.js'])

    <!-- Add Email Confirmation Popup -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const emailInput = document.getElementById("email");
            const form = document.querySelector("form[action='{{ route('profile.update') }}']");
            
            if (!emailInput || !form) {
                console.error("Email input or form not found.");
                return;
            }

            let originalEmail = emailInput.value;

            emailInput.addEventListener("change", function () {
                if (emailInput.value !== originalEmail) {
                    const confirmChange = confirm(
                        "{{ __('You are about to update your email address. This will require you to verify your new email address before accessing certain features. Do you wish to proceed?') }}"
                    );

                    if (!confirmChange) {
                        emailInput.value = originalEmail; // Revert the email input
                    }
                }
            });
        });
    </script>
</x-app-layout>
