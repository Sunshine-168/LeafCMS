(function () {
    'use strict';
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form[data-unsaved-guard]').forEach(function (form) {
            var submitting = false;
            function state() {
                return JSON.stringify(Array.from(form.elements).filter(function (field) {
                    return field.name && field.name !== '_token' && !['submit', 'button'].includes(field.type);
                }).map(function (field) {
                    return [field.name, field.value, field.checked, field.files ? Array.from(field.files).map(function (file) {
                        return [file.name, file.size, file.lastModified];
                    }) : null];
                }));
            }
            var baseline = state();
            window.addEventListener('beforeunload', function (event) {
                if (!submitting && baseline !== state()) {
                    event.preventDefault();
                    event.returnValue = '';
                }
            });
            form.addEventListener('submit', function () { submitting = true; });
            window.addEventListener('pageshow', function () { submitting = false; });
        });
    });
})();
