@props([
    'prefix',
    'name',
    'label' => 'Address',
    'value' => '',
])

<div data-ph-address-selector class="space-y-3">
    <label class="block text-sm/6 font-medium text-gray-900">{{ $label }}</label>

    <div class="rounded-md bg-amber-50 border border-amber-200 px-3 py-2 mb-3">
        <p class="text-sm text-amber-800">
            <strong>Important:</strong> Only residents of General Mamerto Natividad, Nueva Ecija, Central Luzon may apply for AICS assistance.
        </p>
    </div>

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
    <input type="hidden" name="{{ $prefix }}[region]" value="" data-role="region-value" />
    <input type="hidden" name="{{ $prefix }}[province]" value="" data-role="province-value" />
    <input type="hidden" name="{{ $prefix }}[municipality]" value="" data-role="municipality-value" />
    <input type="hidden" name="{{ $prefix }}[baranggay]" value="" data-role="barangay-value" />

    <div>
        <label class="block text-xs/5 font-medium text-gray-700">Composed address</label>
        <input type="text" value="{{ $value }}" readonly data-role="address-preview" class="mt-1 block w-full rounded-md bg-gray-50 px-3 py-1.5 text-sm text-gray-700 outline-1 -outline-offset-1 outline-gray-200" />
    </div>

    <p data-role="status" class="text-xs text-gray-500"></p>
    <p data-role="error" class="text-xs text-red-600 hidden"></p>
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
            if (!response.ok) throw new Error(`Failed to load (${response.status})`);
            const payload = await response.json();
            return Array.isArray(payload) ? payload.map(toOption).filter((item) => item.code && item.name) : [];
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
            const errorElement = wrapper.querySelector('[data-role="error"]');

            const hiddenRegion = wrapper.querySelector('[data-role="region-value"]');
            const hiddenProvince = wrapper.querySelector('[data-role="province-value"]');
            const hiddenMunicipality = wrapper.querySelector('[data-role="municipality-value"]');
            const hiddenBarangay = wrapper.querySelector('[data-role="barangay-value"]');

            if (!regionSelect || !provinceSelect || !citySelect || !barangaySelect) return;

            const getSelectedText = (selectElement) => {
                const selectedOption = selectElement.options[selectElement.selectedIndex];
                return selectedOption && selectedOption.value ? selectedOption.textContent.trim() : '';
            };

            const updateComposedAddress = () => {
                const parts = [getSelectedText(barangaySelect), getSelectedText(citySelect), getSelectedText(provinceSelect), getSelectedText(regionSelect)].filter(Boolean);
                const composedAddress = parts.join(', ');
                hiddenAddressInput.value = composedAddress;
                addressPreviewInput.value = composedAddress;
                
                hiddenRegion.value = getSelectedText(regionSelect);
                hiddenProvince.value = getSelectedText(provinceSelect);
                hiddenMunicipality.value = getSelectedText(citySelect);
                hiddenBarangay.value = getSelectedText(barangaySelect);
            };

            const loadBarangays = async (cityCode) => {
                barangaySelect.disabled = true;
                barangaySelect.innerHTML = '<option value="">Loading barangays...</option>';
                try {
                    const barangays = await fetchLocations(`/cities-municipalities/${cityCode}/barangays/`);
                    populateSelect(barangaySelect, barangays, 'Select barangay');
                    barangaySelect.disabled = false;
                } catch (error) {
                    barangaySelect.innerHTML = '<option value="">Unable to load</option>';
                }
            };

            [barangaySelect, citySelect, provinceSelect, regionSelect].forEach(el => el.addEventListener('change', updateComposedAddress));

            const initialize = async () => {
                try {
                    statusElement.textContent = 'Loading regions...';
                    const regions = await fetchLocations('/regions/');
                    populateSelect(regionSelect, regions, 'Select region');
                    regionSelect.disabled = false;
                    statusElement.textContent = '';
                } catch (error) {
                    statusElement.textContent = 'Unable to load address data. Please refresh.';
                }
            };

            [regionSelect, provinceSelect, citySelect, barangaySelect].forEach(el => {
                el.disabled = true;
                el.innerHTML = '<option value="">Loading...</option>';
            });

            regionSelect.addEventListener('change', async () => {
                hiddenAddressInput.value = '';
                addressPreviewInput.value = '';
                [provinceSelect, citySelect, barangaySelect].forEach(el => {
                    el.disabled = true;
                    el.innerHTML = '<option value="">Select</option>';
                });
                if (regionSelect.value) {
                    statusElement.textContent = 'Loading...';
                    const provinces = await fetchLocations(`/regions/${regionSelect.value}/provinces/`);
                    populateSelect(provinceSelect, provinces, 'Select province');
                    provinceSelect.disabled = false;
                    statusElement.textContent = '';
                }
            });

            provinceSelect.addEventListener('change', async () => {
                hiddenAddressInput.value = '';
                addressPreviewInput.value = '';
                [citySelect, barangaySelect].forEach(el => {
                    el.disabled = true;
                    el.innerHTML = '<option value="">Select</option>';
                });
                if (provinceSelect.value) {
                    statusElement.textContent = 'Loading...';
                    const cities = await fetchLocations(`/provinces/${provinceSelect.value}/cities-municipalities/`);
                    populateSelect(citySelect, cities, 'Select municipality');
                    citySelect.disabled = false;
                    statusElement.textContent = '';
                }
            });

            citySelect.addEventListener('change', async () => {
                hiddenAddressInput.value = '';
                addressPreviewInput.value = '';
                barangaySelect.disabled = true;
                barangaySelect.innerHTML = '<option value="">Select</option>';
                if (citySelect.value) {
                    statusElement.textContent = 'Loading...';
                    await loadBarangays(citySelect.value);
                    statusElement.textContent = '';
                    updateComposedAddress();
                }
            });

            initialize();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('[data-ph-address-selector]').forEach(setupAddressSelector));
        } else {
            document.querySelectorAll('[data-ph-address-selector]').forEach(setupAddressSelector);
        }
    })();
</script>
@endonce
