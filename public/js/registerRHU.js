document.addEventListener('DOMContentLoaded', function () {
    const regionSelect = document.getElementById('region');
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');

    if (!regionSelect) {
        return;
    }

    async function loadRegions() {
        try {
            const response = await fetch('https://psgc.gitlab.io/api/regions/');
            if (!response.ok) throw new Error('Failed to load regions');
            const regions = await response.json();
            regionSelect.innerHTML = '<option value="">Select Region</option>';
            regions.forEach(region => {
                const option = document.createElement('option');
                option.value = region.code;
                option.textContent = region.name;
                regionSelect.appendChild(option);
            });
        } catch (error) {
            addFallbackRegions();
        }
    }

    async function loadProvinces(regionCode) {
        try {
            const response = await fetch(`https://psgc.gitlab.io/api/regions/${regionCode}/provinces/`);
            if (!response.ok) throw new Error('Failed to load provinces');
            const provinces = await response.json();
            provinceSelect.innerHTML = '<option value="">Select Province</option>';
            provinces.forEach(province => {
                const option = document.createElement('option');
                option.value = province.code;
                option.textContent = province.name;
                provinceSelect.appendChild(option);
            });
            provinceSelect.disabled = false;
        } catch (error) {
            provinceSelect.innerHTML = '<option value="">Error loading provinces</option>';
        }
    }

    async function loadCities(provinceCode) {
        try {
            const response = await fetch(`https://psgc.gitlab.io/api/provinces/${provinceCode}/cities-municipalities/`);
            if (!response.ok) throw new Error('Failed to load cities');
            const cities = await response.json();
            citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
            cities.forEach(city => {
                const option = document.createElement('option');
                option.value = city.code;
                option.textContent = city.name;
                citySelect.appendChild(option);
            });
            citySelect.disabled = false;
        } catch (error) {
            citySelect.innerHTML = '<option value="">Error loading cities</option>';
        }
    }

    function addFallbackRegions() {
        const fallbackRegions = [
            { code: 'NCR', name: 'National Capital Region (NCR)' },
            { code: 'CAR', name: 'Cordillera Administrative Region (CAR)' },
            { code: 'I', name: 'Region I (Ilocos Region)' },
            { code: 'II', name: 'Region II (Cagayan Valley)' },
            { code: 'III', name: 'Region III (Central Luzon)' },
            { code: 'IV-A', name: 'Region IV-A (CALABARZON)' },
            { code: 'IV-B', name: 'Region IV-B (MIMAROPA)' },
            { code: 'V', name: 'Region V (Bicol Region)' },
            { code: 'VI', name: 'Region VI (Western Visayas)' },
            { code: 'VII', name: 'Region VII (Central Visayas)' },
            { code: 'VIII', name: 'Region VIII (Eastern Visayas)' },
            { code: 'IX', name: 'Region IX (Zamboanga Peninsula)' },
            { code: 'X', name: 'Region X (Northern Mindanao)' },
            { code: 'XI', name: 'Region XI (Davao Region)' },
            { code: 'XII', name: 'Region XII (SOCCSKSARGEN)' },
            { code: 'XIII', name: 'Region XIII (Caraga)' },
            { code: 'BARMM', name: 'Bangsamoro Autonomous Region in Muslim Mindanao (BARMM)' }
        ];
        regionSelect.innerHTML = '<option value="">Select Region</option>';
        fallbackRegions.forEach(region => {
            const option = document.createElement('option');
            option.value = region.code;
            option.textContent = region.name;
            regionSelect.appendChild(option);
        });
    }

    regionSelect.addEventListener('change', function () {
        const regionCode = this.value;
        provinceSelect.innerHTML = '<option value="">Select Province</option>';
        citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
        provinceSelect.disabled = true;
        citySelect.disabled = true;
        if (regionCode) {
            loadProvinces(regionCode);
        }
    });

    provinceSelect.addEventListener('change', function () {
        const provinceCode = this.value;
        citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
        citySelect.disabled = true;
        if (provinceCode) {
            loadCities(provinceCode);
        }
    });

    loadRegions();
});