/* Content drafts are local to the signed-in administrator, site and entry. */
(function () {
    'use strict';
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('entry-workflow');
        if (!form) return;
        var messages = JSON.parse(document.getElementById('entry-workflow-messages').textContent);
        function t(key, values) {
            var text = messages[key] || key;
            Object.keys(values || {}).forEach(function (name) { text = text.replace(':' + name, values[name]); });
            return text;
        }
        var base = form.dataset.draftKey;
        var prefix = base + ':draft:';
        var key = base;
        document.getElementById('entry-draft-id').value = 'current';
        var status = document.getElementById('entry-save-state');
        var recovery = document.getElementById('entry-recovery');
        var mode = document.getElementById('entry-status');
        var date = document.getElementById('entry-published-at');
        var primary = document.getElementById('entry-save-action');
        var summary = document.getElementById('entry-publish-summary');
        var submitting = false;
        var candidate = null;
        var storageFailed = false;
        var lifetime = 7 * 86400000;
        var candidates = [];
        var lastStored = '';

        function fields() {
            if (window.tinymce) {
                tinymce.get().forEach(function (editor) {
                    if (editor.initialized) editor.save();
                });
            }
            return Array.from(form.elements).filter(function (el) {
                return el.name && !['_token', '_method', 'submit_action', 'draft_id', 'recovered_draft_id', 'edit_version'].includes(el.name)
                    && !['file', 'submit', 'button'].includes(el.type);
            }).map(function (el) {
                return {name: el.name, type: el.type, value: el.value, checked: !!el.checked};
            });
        }
        var baseline = JSON.stringify(fields());
        function dirty() { return JSON.stringify(fields()) !== baseline; }
        function removeDraft(draftKey) {
            try { localStorage.removeItem(draftKey); } catch (_) { storageFailed = true; }
        }
        function removeOwnDraft() { removeDraft(key); }
        function remember() {
            if (submitting) return;
            if (!dirty()) {
                status.textContent = t('local_clean');
                if (recovery.hidden) removeOwnDraft();
                lastStored = '';
                return;
            }
            try {
                var values = fields();
                var serialized = JSON.stringify(values);
                if (serialized === lastStored && localStorage.getItem(key)) return;
                var savedAt = Date.now();
                localStorage.setItem(key, JSON.stringify({fields: values, savedAt: savedAt, serverVersion: form.dataset.updatedAt, owner: 'current'}));
                lastStored = serialized;
                status.textContent = t('local_saved', {time: new Date(savedAt).toLocaleTimeString(document.documentElement.lang)});
            } catch (_) {
                storageFailed = true;
                status.textContent = t('local_failed');
            }
        }
        function publishSummary() {
            var scheduled = mode.value === 'scheduled';
            date.required = scheduled;
            date.disabled = !scheduled;
            var live = form.dataset.wasPublished === '1';
            var labels = {draft: t('保存草稿'), pending: live ? t('提交修改稿审核') : t('提交审核'), scheduled: live ? t('保存修改稿') : t('保存定时发布'),
                published: live ? t('保存修改稿') : t('立即发布')};
            primary.textContent = labels[mode.value] || t('保存草稿');
            summary.textContent = live && mode.value !== 'draft'
                ? t('保存修改稿不会替换线上内容。预览和送审也只针对修改稿，点「发布到线上」才替换。')
                : (mode.value === 'draft' ? t('draft_summary')
                : mode.value === 'pending' ? t('pending_summary')
                : scheduled ? t('schedule_summary')
                : t('published_summary'));
            if (!live || mode.value === 'draft') {
                summary.textContent += t('preview_summary');
            }
        }
        publishSummary();
        baseline = JSON.stringify(fields());
        function clearEntryDrafts(sourceBase) {
            removeDraft(sourceBase);
            try {
                for (var i = localStorage.length - 1; i >= 0; i--) {
                    var storedKey = localStorage.key(i);
                    if (storedKey && storedKey.startsWith(sourceBase + ':draft:')) localStorage.removeItem(storedKey);
                }
            } catch (_) { storageFailed = true; }
        }
        if (form.dataset.saved === '1') {
            clearEntryDrafts(form.dataset.created === '1' ? form.dataset.newDraftKey : base);
            status.textContent = t('server_saved');
        }
        try {
            for (var i = localStorage.length - 1; i >= 0; i--) {
                var storedKey = localStorage.key(i);
                if (!storedKey || !storedKey.startsWith('cms-entry:')) continue;
                try {
                    var stored = JSON.parse(localStorage.getItem(storedKey));
                    if (!stored || !Number.isFinite(stored.savedAt) || Date.now() - stored.savedAt > lifetime || !Array.isArray(stored.fields)
                        || !stored.fields.every(function (field) { return field && typeof field.name === 'string' && typeof field.type === 'string' && typeof field.value === 'string'; })) {
                        localStorage.removeItem(storedKey);
                        continue;
                    }
                    if (storedKey === base || storedKey.startsWith(prefix)) {
                        stored.key = storedKey;
                        stored.id = storedKey === base ? 'legacy' : storedKey.slice(prefix.length);
                        candidates.push(stored);
                    }
                } catch (_) { localStorage.removeItem(storedKey); }
            }
        } catch (_) { storageFailed = true; }
        candidates.sort(function (a, b) { return b.savedAt - a.savedAt; });
        if (candidates.length > 1) {
            candidates.slice(1).forEach(function (item) { removeDraft(item.key); });
            candidates = [candidates[0]];
        }
        if (candidates[0] && candidates[0].key !== key) {
            try {
                localStorage.setItem(key, localStorage.getItem(candidates[0].key));
                removeDraft(candidates[0].key);
                candidates[0].key = key;
                candidates[0].id = 'current';
            } catch (_) { storageFailed = true; }
        }
        candidate = candidates[0] || null;
        recovery.hidden = !candidate;
        document.getElementById('entry-recover').addEventListener('click', function () {
            if (!candidate) return;
            try {
                var latest = JSON.parse(localStorage.getItem(candidate.key));
                if (!latest || !Array.isArray(latest.fields)) {
                    document.getElementById('entry-recovery-message').textContent = t('draft_missing');
                    return;
                }
                candidate.fields = latest.fields;
            } catch (_) {
                document.getElementById('entry-recovery-message').textContent = t('draft_unreadable');
                return;
            }
            var saved = candidate.fields;
            document.getElementById('entry-recovered-draft-id').value = candidate.id;
            Array.from(form.elements).forEach(function (el) {
                var found = saved.find(function (item) {
                    return item.name === el.name && item.type === el.type
                        && (!['checkbox', 'radio'].includes(el.type) || item.value === el.value);
                });
                if (!found) return;
                if (['checkbox', 'radio'].includes(el.type)) el.checked = found.checked;
                else if (!['file', 'submit', 'button'].includes(el.type) && !['_token', '_method', 'draft_id', 'recovered_draft_id', 'edit_version'].includes(el.name)) el.value = found.value;
                if (el.classList.contains('cms-editor')) {
                    const editor = window.tinymce && tinymce.get(el.id);
                    const content = el.value;
                    if (editor && editor.initialized) editor.setContent(content);
                    else if (editor) editor.once('init', function () { editor.setContent(content); });
                }
                el.dispatchEvent(new Event('input', {bubbles: true}));
                el.dispatchEvent(new Event('change', {bubbles: true}));
            });
            candidates.forEach(function (item) {
                if (item !== candidate) removeDraft(item.key);
            });
            candidates = candidate ? [candidate] : [];
            candidate = null;
            recovery.hidden = true;
            publishSummary();
            remember();
        });
        document.getElementById('entry-discard-recovery').addEventListener('click', function () {
            candidates.forEach(function (item) { removeDraft(item.key); });
            candidates = [];
            candidate = null;
            recovery.hidden = true;
            remember();
        });
        mode.addEventListener('change', publishSummary);
        var timer;
        form.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(remember, 500);
        });
        form.addEventListener('change', function () { publishSummary(); remember(); });
        // Rich text edits happen inside an iframe and do not bubble to the form.
        setInterval(remember, 3000);
        window.addEventListener('beforeunload', function (event) {
            if (!submitting && dirty()) {
                remember();
                event.preventDefault();
                event.returnValue = '';
            }
        });
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) remember();
        });
        form.addEventListener('submit', function () {
            remember();
            submitting = true;
            // Keep every window's local copy until the server confirms that specific save.
        });
        window.addEventListener('pageshow', function () { submitting = false; });
        if (storageFailed) status.textContent = t('local_unavailable');
        if (form.dataset.previewUrl) window.location.assign(form.dataset.previewUrl);
    });
})();
