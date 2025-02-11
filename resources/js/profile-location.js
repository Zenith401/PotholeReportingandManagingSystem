document.addEventListener("DOMContentLoaded", function () {
    console.log("Script initialized.");

    const countryDropdown = document.getElementById("country");
    const stateDropdown = document.getElementById("state");
    const cityDropdown = document.getElementById("city");
    const zipDropdown = document.getElementById("zip");

    // Reset a dropdown with a placeholder
    const resetDropdown = (dropdown, placeholder) => {
        console.log(`Resetting ${dropdown.id} dropdown.`);
        dropdown.innerHTML = `<option value="" disabled selected>${placeholder}</option>`;
        dropdown.disabled = true;
    };

    // Populate a dropdown dynamically
    const populateDropdown = (dropdown, items, placeholder) => {
        console.log(`Populating ${dropdown.id} dropdown.`);
        resetDropdown(dropdown, placeholder);
        items.forEach((item) => {
            const option = document.createElement("option");
            option.value = item.id || item; // Handle array of objects or simple array
            option.textContent = item.name || item; // Handle array of objects or simple array
            dropdown.appendChild(option);
        });
        dropdown.disabled = false;
    };

    // Country change
    countryDropdown.addEventListener("change", async function () {
        const countryIso2 = this.value;
        console.log(`Country selected: ${countryIso2}`);
        resetDropdown(stateDropdown, "Select State");
        resetDropdown(cityDropdown, "Select City");
        resetDropdown(zipDropdown, "Select ZIP Code");

        if (!countryIso2) return;

        const states = await fetch(`/get-states/${countryIso2}`)
            .then((res) => res.json())
            .catch((error) => {
                console.error("Error fetching states:", error);
                return [];
            });

        console.log("States data:", states);
        populateDropdown(stateDropdown, states, "Select State");
    });

    // State change
    stateDropdown.addEventListener("change", async function () {
        const stateId = this.value;
        console.log(`State selected: ${stateId}`);
        resetDropdown(cityDropdown, "Select City");
        resetDropdown(zipDropdown, "Select ZIP Code");

        if (!stateId) return;

        const cities = await fetch(`/get-cities/${stateId}`)
            .then((res) => res.json())
            .catch((error) => {
                console.error("Error fetching cities:", error);
                return [];
            });

        console.log("Cities data:", cities);
        populateDropdown(cityDropdown, cities, "Select City");
    });

    // City change
    cityDropdown.addEventListener("change", async function () {
        const cityId = this.value;
        console.log(`City selected: ${cityId}`);
        resetDropdown(zipDropdown, "Select ZIP Code");

        if (!cityId) return;

        const zipResponse = await fetch(`/get-zip-codes/${cityId}`)
            .then((res) => res.json())
            .catch((error) => {
                console.error("Error fetching ZIP codes:", error);
                return [];
            });

        const zipCodes = zipResponse.zipCodes || []; // Extract array from response
        console.log("ZIP codes data:", zipCodes);
        populateDropdown(zipDropdown, zipCodes, "Select ZIP Code");
    });
});
