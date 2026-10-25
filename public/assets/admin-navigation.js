(function () {
    'use strict';
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.getElementById('admin-nav-search');
        var nav = document.getElementById('admin-navigation');
        if (!input || !nav) return;
        var empty = document.getElementById('admin-nav-search-empty');
        var links = Array.from(nav.querySelectorAll('a'));
        var folders = Array.from(nav.querySelectorAll('details'));
        var openBeforeSearch = null;
        function filter() {
            var query = input.value.trim().toLocaleLowerCase();
            if (query && !openBeforeSearch) openBeforeSearch = folders.map(function (folder) { return folder.open; });
            var count = 0;
            nav.querySelectorAll('[data-nav-search-only]').forEach(function (group) { group.hidden = !query; });
            links.forEach(function (link) {
                link.hidden = (!query && !!link.closest('[data-nav-search-only]'))
                    || (!!query && !link.textContent.toLocaleLowerCase().includes(query));
                if (!link.hidden) count++;
            });
            folders.forEach(function (folder, index) {
                folder.hidden = !!query && !Array.from(folder.querySelectorAll('a')).some(function (link) { return !link.hidden; });
                if (query) folder.open = !folder.hidden;
                else if (openBeforeSearch) folder.open = openBeforeSearch[index];
            });
            nav.querySelectorAll('.nav-header').forEach(function (header) {
                var next = header.nextElementSibling;
                var visible = false;
                while (next && !next.classList.contains('nav-header')) {
                    if (next.matches('a') && !next.hidden) visible = true;
                    if (!next.hidden && Array.from(next.querySelectorAll('a')).some(function (link) { return !link.hidden; })) visible = true;
                    next = next.nextElementSibling;
                }
                header.hidden = !!query && !visible;
            });
            empty.hidden = count !== 0;
            if (!query) openBeforeSearch = null;
        }
        input.addEventListener('input', filter);
        window.addEventListener('pageshow', filter);
        input.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') { input.value = ''; filter(); }
        });
        document.addEventListener('keydown', function (event) {
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                document.dispatchEvent(new Event('cms:open-nav'));
                input.focus();
                input.select();
            }
        });
    });
})();
