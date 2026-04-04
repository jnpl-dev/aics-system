@props([
    'prefix',
    'name',
    'label' => 'Address',
    'value' => '',
])

<div data-ph-address-selector class="space-y-3">
    <label class="block text-sm/6 font-medium text-gray-900">{{ $label }}</label>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label class="block text-xs/5 font-medium text-gray-700">Region</label>
            <select data-role="region" required class="mt-1 block w-full rounded-md bg-white py-1.5 pr-8 pl-3 text-sm text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-[#176334]"></select>
        </div>
        <div>
            <label class="block text-xs/5 font-medium text-gray-700">Province</label>
            <select data-role="province" required class="mt-1 block w-full rounded-md bg-white py-1.5 pr-8 pl-3 text-sm text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-[#176334]"></select>
        </div>
        <div>
            <label class="block text-xs/5 font-medium text-gray-700">City / Municipality</label>
            <select data-role="city" required class="mt-1 block w-full rounded-md bg-white py-1.5 pr-8 pl-3 text-sm text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-[#176334]"></select>
        </div>
        <div>
            <label class="block text-xs/5 font-medium text-gray-700">Barangay</label>
            <select data-role="barangay" required class="mt-1 block w-full rounded-md bg-white py-1.5 pr-8 pl-3 text-sm text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-[#176334]"></select>
        </div>
    </div>

    <input type="hidden" name="{{ $name }}" value="{{ $value }}" data-role="composed-address" />

    <div>
        <label class="block text-xs/5 font-medium text-gray-700">Composed address</label>
        <input type="text" value="{{ $value }}" readonly data-role="address-preview" class="mt-1 block w-full rounded-md bg-gray-50 px-3 py-1.5 text-sm text-gray-700 outline-1 -outline-offset-1 outline-gray-200" />
    </div>

    <p data-role="status" class="text-xs text-gray-500"></p>
</div>

@once
<script>
    (() => {
        if (window.__phAddressSelectorInitialized) {
            return;
        }

        window.__phAddressSelectorInitialized = true;
        const PSGC_API_BASE = 'https://psgc.gitlab.io/api';

        const toOption = (item) => ({
            code: item.code ?? item.id ?? '',
            name: item.name ?? item.regionName ?? item.provinceName ?? item.cityName ?? item.cityOrMunicipalityName ?? item.municipalityName ?? item.barangayName ?? '',
        });

        const fetchLocations = async (path) => {
            const response = await fetch(`${PSGC_API_BASE}${path}`, { headers: { Accept: 'application/json' } });
            if (!response.ok) {
                throw new Error(`Failed to load locations (${response.status})`);
            }

            const payload = await response.json();
            return Array.isArray(payload)
                ? payload.map(toOption).filter((item) => item.code && item.name)
                : [];
        };

        const populateSelect = (selectElement, options, placeholder) => {
            selectElement.innerHTML = '';

            const placeholderOption = document.createElement('option');
            placeholderOption.value = '';
            placeholderOption.textContent = placeholder;
            selectElement.appendChild(placeholderOption);

            options.forEach((option) => {
                const optionElement = document.createElement('option');
                optionElement.value = option.code;
                optionElement.textContent = option.name;
                selectElement.appendChild(optionElement);
            });
        };

        const setupAddressSelector = (wrapper) => {
            const regionSelect = wrapper.querySelector('[data-role="region"]');
            const provinceSelect = wrapper.querySelector('[data-role="province"]');
            const citySelect = wrapper.querySelector('[data-role="city"]');
            const barangaySelect = wrapper.querySelector('[data-role="barangay"]');
            const hiddenAddressInput = wrapper.querySelector('[data-role="composed-address"]');
            const addressPreviewInput = wrapper.querySelector('[data-role="address-preview"]');
            const statusElement = wrapper.querySelector('[data-role="status"]');

            if (!regionSelect || !provinceSelect || !citySelect || !barangaySelect || !hiddenAddressInput || !addressPreviewInput || !statusElement) {
                return;
            }

            const getSelectedText = (selectElement) => {
                const selectedOption = selectElement.options[selectElement.selectedIndex];
                return selectedOption && selectedOption.value ? selectedOption.textContent.trim() : '';
            };

            const updateComposedAddress = () => {
                const parts = [
                    getSelectedText(barangaySelect),
                    getSelectedText(citySelect),
                    provinceSelect.disabled ? '' : getSelectedText(provinceSelect),
                    getSelectedText(regionSelect),
                ].filter(Boolean);

                const composedAddress = parts.join(', ');
                hiddenAddressInput.value = composedAddress;
                addressPreviewInput.value = composedAddress;
            };

            const setDisabled = (selectElement, disabled, placeholder) => {
                selectElement.disabled = disabled;
                if (disabled) {
                    populateSelect(selectElement, [], placeholder);
                }
            };

            const loadBarangays = async (cityCode) => {
                setDisabled(barangaySelect, true, 'Loading barangays...');
                const barangays = await fetchLocations(`/cities-municipalities/${cityCode}/barangays/`);
                populateSelect(barangaySelect, barangays, 'Select barangay');
                barangaySelect.disabled = false;
            };

            const loadCitiesByRegion = async (regionCode) => {
                setDisabled(citySelect, true, 'Loading cities/municipalities...');
                const cities = await fetchLocations(`/regions/${regionCode}/cities-municipalities/`);
                populateSelect(citySelect, cities, 'Select city / municipality');
                citySelect.disabled = false;
            };

            const loadCitiesByProvince = async (provinceCode) => {
                setDisabled(citySelect, true, 'Loading cities/municipalities...');
                const cities = await fetchLocations(`/provinces/${provinceCode}/cities-municipalities/`);
                populateSelect(citySelect, cities, 'Select city / municipality');
                citySelect.disabled = false;
            };

            regionSelect.addEventListener('change', async () => {
                hiddenAddressInput.value = '';
                addressPreviewInput.value = '';
                setDisabled(provinceSelect, true, 'Select province');
                setDisabled(citySelect, true, 'Select city / municipality');
                setDisabled(barangaySelect, true, 'Select barangay');

                if (!regionSelect.value) {
                    return;
                }

                try {
                    statusElement.textContent = 'Loading locations...';
                    const provinces = await fetchLocations(`/regions/${regionSelect.value}/provinces/`);

                    if (provinces.length > 0) {
                        populateSelect(provinceSelect, provinces, 'Select province');
                        provinceSelect.disabled = false;
                        provinceSelect.required = true;
                    } else {
                        provinceSelect.required = false;
                        setDisabled(provinceSelect, true, 'No province (NCR / independent city)');
                        await loadCitiesByRegion(regionSelect.value);
                    }

                    statusElement.textContent = '';
                } catch (error) {
                    statusElement.textContent = 'Unable to load location data right now. Please refresh and try again.';
                }
            });

            provinceSelect.addEventListener('change', async () => {
                hiddenAddressInput.value = '';
                addressPreviewInput.value = '';
                setDisabled(citySelect, true, 'Select city / municipality');
                setDisabled(barangaySelect, true, 'Select barangay');

                if (!provinceSelect.value) {
                    return;
                }

                try {
                    statusElement.textContent = 'Loading locations...';
                    await loadCitiesByProvince(provinceSelect.value);
                    statusElement.textContent = '';
                } catch (error) {
                    statusElement.textContent = 'Unable to load city/municipality options.';
                }
            });

            citySelect.addEventListener('change', async () => {
                hiddenAddressInput.value = '';
                addressPreviewInput.value = '';
                setDisabled(barangaySelect, true, 'Select barangay');

                if (!citySelect.value) {
                    return;
                }

                try {
                    statusElement.textContent = 'Loading locations...';
                    await loadBarangays(citySelect.value);
                    statusElement.textContent = '';
                } catch (error) {
                    statusElement.textContent = 'Unable to load barangay options.';
                }
            });

            barangaySelect.addEventListener('change', updateComposedAddress);
            citySelect.addEventListener('change', updateComposedAddress);
            provinceSelect.addEventListener('change', updateComposedAddress);
            regionSelect.addEventListener('change', updateComposedAddress);

            const initialize = async () => {
                try {
                    statusElement.textContent = 'Loading regions...';
                    const regions = await fetchLocations('/regions/');
                    populateSelect(regionSelect, regions, 'Select region');
                    regionSelect.disabled = false;
                    statusElement.textContent = '';
                } catch (error) {
                    statusElement.textContent = 'Unable to load Philippine address data. Please refresh and try again.';
                    setDisabled(regionSelect, true, 'Address data unavailable');
                    setDisabled(provinceSelect, true, 'Address data unavailable');
                    setDisabled(citySelect, true, 'Address data unavailable');
                    setDisabled(barangaySelect, true, 'Address data unavailable');
                }

                if (hiddenAddressInput.value) {
                    addressPreviewInput.value = hiddenAddressInput.value;
                }
            };

            setDisabled(regionSelect, true, 'Loading regions...');
            setDisabled(provinceSelect, true, 'Select province');
            setDisabled(citySelect, true, 'Select city / municipality');
            setDisabled(barangaySelect, true, 'Select barangay');
            initialize();
        };

        const initAll = () => {
            document.querySelectorAll('[data-ph-address-selector]').forEach(setupAddressSelector);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAll);
        } else {
            initAll();
        }
    })();
</script>
@endonce
