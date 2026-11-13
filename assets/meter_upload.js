(function () {
    'use strict';
    var form = document.getElementById('meter-upload-form');
    if (!form) { return; }
    var site = document.getElementById('meter-upload-site');
    var file = document.getElementById('meter-upload-file');
    var preview = document.getElementById('meter-upload-preview');
    var save = document.getElementById('meter-upload-import');
    var view = document.getElementById('meter-upload-view');
    var message = document.getElementById('meter-upload-message');
    var results = document.getElementById('meter-upload-results');
    var summary = document.getElementById('meter-upload-summary');
    var rows = document.getElementById('meter-upload-rows');
    var validated = null;
    var busy = false;
    var hasSites = site.options.length > 1;
    var gatewayMode = form.getAttribute('data-entity') === 'gateway';
    var noun = gatewayMode ? 'gateway' : 'meter';
    var plural = noun + 's';

    function notify(text, kind) {
        message.hidden = false;
        message.className = 'alert alert-' + kind;
        message.textContent = text;
    }

    function controls() {
        site.disabled = busy || !hasSites;
        file.disabled = busy || !hasSites;
        preview.disabled = busy || !hasSites;
        save.disabled = busy || !validated;
        form.setAttribute('aria-busy', busy ? 'true' : 'false');
    }

    function reset() {
        validated = null;
        results.hidden = true;
        view.hidden = true;
        rows.textContent = '';
        message.textContent = '';
        message.hidden = true;
        controls();
    }

    function cell(row, value) {
        var td = document.createElement('td');
        td.textContent = value === null || value === undefined ? '' : String(value);
        row.appendChild(td);
    }

    function render(report) {
        results.hidden = false;
        rows.textContent = '';
        summary.textContent = (report.dry_run ? 'Preview' : 'Import') +
            ' — Total: ' + report.total + ' | Eligible: ' + report.would_insert +
            ' | Inserted: ' + report.inserted + ' | Skipped: ' + report.skipped +
            ' | Invalid: ' + report.invalid +
            (gatewayMode ? '' : ' | Unmatched gateways: ' + report.unmatched_gateways);
        var fragment = document.createDocumentFragment();
        report.rows.forEach(function (item) {
            var row = document.createElement('tr');
            row.className = item.errors.length ? 'danger' : item.warnings.length ? 'warning' : '';
            cell(row, item.sheet + ' / ' + item.row);
            if (gatewayMode) {
                cell(row, item.rtu_sn_number);
                cell(row, item.mac_addr);
                cell(row, item.rtu_physical_location);
                cell(row, item.phone_no_or_ip_address);
                cell(row, [item.idf_number, item.switch_name, item.idf_port].join(' / '));
            } else {
                cell(row, item.meter_name);
                cell(row, item.gateway_serial);
                cell(row, item.rtu_sn_number_id);
            }
            cell(row, item.status === 'ready' ? 'Eligible (not saved)' : item.status);
            cell(row, item.errors.concat(item.warnings).join(' '));
            fragment.appendChild(row);
        });
        rows.appendChild(fragment);
    }

    async function request(url, options) {
        var response = await fetch(url, Object.assign({
            credentials: 'same-origin', cache: 'no-store', redirect: 'error'
        }, options || {}));
        var body;
        try { body = await response.json(); }
        catch (error) { throw new Error('The server did not return an import response. Check your login and try again.'); }
        if (!body || typeof body !== 'object') {
            throw new Error('Unexpected server response. Reload the page and try again.');
        }
        if (!response.ok && !(response.status === 422 && Array.isArray(body.rows))) {
            throw new Error(body.error || 'Request failed. Check your login and try again.');
        }
        return body;
    }

    async function upload(dryRun) {
        if (busy) { return; }
        var selectedFile = file.files[0];
        var selectedSite = site.value;
        if (!selectedSite || !selectedFile) {
            notify('Select a destination site and an Excel workbook.', 'warning');
            return;
        }
        if (!/\.xlsx$/i.test(selectedFile.name) || selectedFile.size > 5242880) {
            reset();
            notify('Choose an .xlsx workbook no larger than 5 MiB.', 'warning');
            return;
        }
        if (!dryRun) {
            if (!validated || validated.file !== selectedFile || validated.site !== selectedSite) {
                reset();
                return;
            }
            var label = site.options[site.selectedIndex].text;
            if (!window.confirm('Import ' + validated.count + ' new ' + noun + '(s) into ' + label +
                '? Review all warnings first. Existing ' + plural + ' will not be overwritten.')) {
                return;
            }
        }
        validated = null;
        view.hidden = true;
        busy = true;
        controls();
        notify(dryRun ? 'Validating workbook. No ' + plural + ' are being saved…'
            : 'Importing ' + plural + '. Please wait…', 'info');
        try {
            var token = await request(form.getAttribute('data-token-url'));
            if (typeof token.csrf_token !== 'string' || !token.csrf_token) {
                throw new Error('Unable to obtain an upload token. Log in again and retry.');
            }
            var data = new FormData();
            data.append('site_id', selectedSite);
            data.append('file', selectedFile);
            data.append('dry_run', dryRun ? '1' : '0');
            var report = await request(form.getAttribute('data-import-url'), {
                method: 'POST', headers: { 'X-CSRF-Token': token.csrf_token }, body: data
            });
            if (!Array.isArray(report.rows)) { throw new Error('Unexpected import results. Please validate again.'); }
            render(report);
            if (report.invalid) {
                notify('Validation failed. No ' + plural + ' were saved. Correct the listed errors and preview again.', 'danger');
            } else if (dryRun && report.would_insert > 0) {
                validated = { file: selectedFile, site: selectedSite, count: report.would_insert };
                notify('Preview complete. No ' + plural + ' were saved. Review warnings, then click Import ' +
                    (gatewayMode ? 'Gateways.' : 'Meters.'), 'warning');
            } else if (dryRun) {
                notify('Preview complete. No new ' + plural + ' to import; existing ' + plural + ' were skipped.', 'info');
            } else {
                notify('Import complete. ' + report.inserted + ' ' + noun + '(s) inserted; ' + report.skipped + ' skipped.', 'success');
                view.href = form.getAttribute('data-meter-url') + '/' + encodeURIComponent(selectedSite) + (gatewayMode ? '' : '/0');
                view.hidden = false;
            }
        } catch (error) {
            results.hidden = true;
            notify((error.message || 'Request failed.') + (dryRun
                ? ' No ' + plural + ' were saved by this preview.'
                : ' If the connection failed, the import may have completed. Preview again before retrying; existing ' + plural + ' will be skipped.'), 'danger');
        } finally {
            busy = false;
            controls();
        }
    }

    form.addEventListener('submit', function (event) { event.preventDefault(); upload(true); });
    save.addEventListener('click', function () { upload(false); });
    site.addEventListener('change', reset);
    file.addEventListener('change', reset);
    if (!window.fetch || !window.FormData) {
        notify('Use a current browser with file-upload support to import meters.', 'warning');
        return;
    }
    controls();
}());
