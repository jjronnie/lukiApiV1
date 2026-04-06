import './bootstrap';
import './custom.js';
import Alpine from 'alpinejs';

import createUgLocaleModule from 'ug-locale';


window.Alpine = Alpine;

Alpine.start();

const createUgLocale = typeof createUgLocaleModule === 'function'
    ? createUgLocaleModule
    : typeof createUgLocaleModule?.default === 'function'
        ? createUgLocaleModule.default
        : null;

const ugLocale = createUgLocale ? createUgLocale() : null;

const setupAdminSidebar = () => {
    const sidebar = document.querySelector('[data-sidebar]');
    const overlay = document.querySelector('[data-sidebar-overlay]');
    const openButton = document.querySelector('[data-sidebar-open]');
    const closeButton = document.querySelector('[data-sidebar-close]');

    if (!sidebar || !overlay || !openButton || !closeButton) {
        return;
    }

    const openSidebar = () => {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('opacity-0', 'pointer-events-none');
        overlay.classList.add('opacity-100');
        document.body.classList.add('overflow-hidden');
    };

    const closeSidebar = () => {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('opacity-0', 'pointer-events-none');
        overlay.classList.remove('opacity-100');
        document.body.classList.remove('overflow-hidden');
    };

    openButton.addEventListener('click', openSidebar);
    closeButton.addEventListener('click', closeSidebar);
    overlay.addEventListener('click', closeSidebar);

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
            closeSidebar();
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.add('opacity-0', 'pointer-events-none');
            overlay.classList.remove('opacity-100');
            document.body.classList.remove('overflow-hidden');
        }
    });
};

const setupUgLocaleForms = () => {
    if (!ugLocale) {
        return;
    }

    document.querySelectorAll('[data-ug-locale-form]').forEach((form) => {
        const districtSelect = form.querySelector('[data-ug-district]');
        const countySelect = form.querySelector('[data-ug-county]');
        const subCountySelect = form.querySelector('[data-ug-sub-county]');
        const parishSelect = form.querySelector('[data-ug-parish]');
        const villageSelect = form.querySelector('[data-ug-village]');

        if (!districtSelect || !countySelect || !subCountySelect || !parishSelect || !villageSelect) {
            return;
        }

        const initialDistrictId = form.dataset.initialDistrict || '';
        const initialCountyId = form.dataset.initialCounty || '';
        const initialSubCountyId = form.dataset.initialSubCounty || '';
        const initialParishId = form.dataset.initialParish || '';
        const initialVillageId = form.dataset.initialVillage || '';

        const syncNameInput = (select) => {
            const hiddenInput = form.querySelector(`input[name="${select.name.replace('_id', '_name')}"]`);
            if (!hiddenInput) {
                return;
            }

            const selectedOption = select.options[select.selectedIndex];
            hiddenInput.value = selectedOption?.dataset?.name || '';
        };

        const populateSelect = (select, items, placeholder, selectedValue = '') => {
            const options = [`<option value="">${placeholder}</option>`];
            items.forEach((item) => {
                options.push(`<option value="${item.id}" data-name="${item.name}">${item.name}</option>`);
            });
            select.innerHTML = options.join('');
            select.disabled = items.length === 0;

            if (selectedValue) {
                select.value = selectedValue;
            }

            syncNameInput(select);
        };

        const resetSelect = (select, placeholder) => {
            populateSelect(select, [], placeholder, '');
        };

        const loadVillages = (selectedVillageId = '') => {
            const parishId = parishSelect.value;
            if (!parishId) {
                resetSelect(villageSelect, 'Select village');
                return;
            }

            populateSelect(
                villageSelect,
                ugLocale.villages(parishId),
                'Select village',
                selectedVillageId,
            );
        };

        const loadParishes = (selectedParishId = '', selectedVillageId = '') => {
            const subCountyId = subCountySelect.value;
            if (!subCountyId) {
                resetSelect(parishSelect, 'Select parish');
                resetSelect(villageSelect, 'Select village');
                return;
            }

            populateSelect(
                parishSelect,
                ugLocale.parishes(subCountyId),
                'Select parish',
                selectedParishId,
            );
            loadVillages(selectedVillageId);
        };

        const loadSubCounties = (selectedSubCountyId = '', selectedParishId = '', selectedVillageId = '') => {
            const countyId = countySelect.value;
            if (!countyId) {
                resetSelect(subCountySelect, 'Select sub-county');
                resetSelect(parishSelect, 'Select parish');
                resetSelect(villageSelect, 'Select village');
                return;
            }

            populateSelect(
                subCountySelect,
                ugLocale.subCounties(countyId),
                'Select sub-county',
                selectedSubCountyId,
            );
            loadParishes(selectedParishId, selectedVillageId);
        };

        const loadCounties = (selectedCountyId = '', selectedSubCountyId = '', selectedParishId = '', selectedVillageId = '') => {
            const districtId = districtSelect.value;
            if (!districtId) {
                resetSelect(countySelect, 'Select county');
                resetSelect(subCountySelect, 'Select sub-county');
                resetSelect(parishSelect, 'Select parish');
                resetSelect(villageSelect, 'Select village');
                return;
            }

            populateSelect(
                countySelect,
                ugLocale.counties(districtId),
                'Select county',
                selectedCountyId,
            );
            loadSubCounties(selectedSubCountyId, selectedParishId, selectedVillageId);
        };

        populateSelect(
            districtSelect,
            ugLocale.districts(),
            'Select district',
            initialDistrictId,
        );
        loadCounties(initialCountyId, initialSubCountyId, initialParishId, initialVillageId);

        districtSelect.addEventListener('change', () => loadCounties());
        countySelect.addEventListener('change', () => loadSubCounties());
        subCountySelect.addEventListener('change', () => loadParishes());
        parishSelect.addEventListener('change', () => loadVillages());

        [districtSelect, countySelect, subCountySelect, parishSelect, villageSelect].forEach((select) => {
            select.addEventListener('change', () => syncNameInput(select));
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    setupAdminSidebar();
    setupUgLocaleForms();
});
