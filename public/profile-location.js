document.addEventListener("DOMContentLoaded", function () {
    const countryDropdown = document.getElementById("country");
    const stateDropdown = document.getElementById("state");
    const cityDropdown = document.getElementById("city");
    const zipDropdown = document.getElementById("zip");

    countryDropdown.addEventListener("change", function () {
        resetDropdown(stateDropdown, "Select State");
        resetDropdown(cityDropdown, "Select City");
        resetDropdown(zipDropdown, "Select ZIP Code");

        if (!this.value) return;

        fetch(`/states/${this.value}`)
            .then(handleResponse)
            .then(states => populateDropdown(stateDropdown, states, "Select State"))
            .catch(error => console.error("Error fetching states:", error));
    });

    stateDropdown.addEventListener("change", function () {
        resetDropdown(cityDropdown, "Select City");
        resetDropdown(zipDropdown, "Select ZIP Code");

        if (!this.value) return;

        fetch(`/cities/${this.value}`)
            .then(handleResponse)
            .then(cities => populateDropdown(cityDropdown, cities, "Select City"))
            .catch(error => console.error("Error fetching cities:", error));
    });

    cityDropdown.addEventListener("change", function () {
        resetDropdown(zipDropdown, "Select ZIP Code");

        if (!this.value) return;

        console.log("Fetching ZIP codes for city ID:", this.value); // Debugging log
        fetch(`/get-zip-codes/${this.value}`)
            .then(handleResponse)
            .then(zipCodes => {
                console.log("ZIP codes fetched:", zipCodes); // Debugging log
                populateDropdown(zipDropdown, zipCodes, "Select ZIP Code");
            })
            .catch(error => console.error("Error fetching ZIP codes:", error));
    });

    function resetDropdown(dropdown, placeholder) {
        dropdown.innerHTML = `<option value="" disabled selected>${placeholder}</option>`;
        dropdown.disabled = true;
    }

    function populateDropdown(dropdown, items, placeholder) {
        dropdown.innerHTML = `<option value="" disabled selected>${placeholder}</option>`;
        items.forEach(item => {
            const option = document.createElement("option");
            option.value = item; // ZIP code is a string
            option.textContent = item;
            dropdown.appendChild(option);
        });
        dropdown.disabled = false;
    }

    function handleResponse(response) {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    }
});
