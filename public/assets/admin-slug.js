(function () {
    function slugify(value) {
        return value
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function bindSlugPair(source, target) {
        if (!source || !target || source.dataset.slugBound === '1') {
            return;
        }

        source.dataset.slugBound = '1';
        let lastGenerated = target.value;

        source.addEventListener('input', function () {
            const generated = slugify(source.value);

            if (target.value === '' || target.value === lastGenerated) {
                target.value = generated;
                lastGenerated = generated;
            }
        });
    }

    function initAutoSlug() {
        document.querySelectorAll('[data-slug-source]').forEach(function (source) {
            const key = source.getAttribute('data-slug-source');
            const target = document.querySelector('[data-slug-target="' + key + '"]');

            bindSlugPair(source, target);
        });
    }

    function improveAdminAccessibility() {
        document.querySelectorAll('.ea-clickable-row[role="link"]').forEach(function (row) {
            row.removeAttribute('role');
            row.removeAttribute('tabindex');
        });

        document.querySelectorAll('.form-batch-checkbox-all').forEach(function (checkbox) {
            checkbox.setAttribute('aria-label', 'Sélectionner toutes les lignes');
        });

        document.querySelectorAll('.form-batch-checkbox').forEach(function (checkbox) {
            checkbox.setAttribute('aria-label', 'Sélectionner cette ligne');
        });

        document.querySelectorAll('.dropdown-actions > a.dropdown-toggle').forEach(function (toggle) {
            toggle.setAttribute('aria-label', 'Ouvrir le menu des actions de la ligne');
        });

        document.querySelectorAll('td.has-switch .form-check-input').forEach(function (input) {
            var cell = input.closest('td');
            var label = cell ? cell.getAttribute('data-label') : null;

            if (label) {
                input.setAttribute('aria-label', label);
            }
        });
    }

    function initAdminEnhancements() {
        initAutoSlug();
        improveAdminAccessibility();
    }

    document.addEventListener('DOMContentLoaded', initAdminEnhancements);
    document.addEventListener('ea.collection.item-added', initAdminEnhancements);
})();
