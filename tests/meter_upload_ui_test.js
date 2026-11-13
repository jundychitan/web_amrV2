// Run with: node tests/meter_upload_ui_test.js
'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const script = fs.readFileSync(path.join(__dirname, '../assets/meter_upload.js'), 'utf8');

class Element {
    constructor() {
        this.listeners = {};
        this.attributes = {};
        this.children = [];
        this.disabled = false;
        this.hidden = true;
        this.value = '';
        this.files = [];
    }
    set textContent(value) { this.text = String(value); this.children = []; }
    get textContent() { return this.text || ''; }
    set innerHTML(value) { throw new Error('Untrusted results must not use innerHTML'); }
    addEventListener(event, handler) { this.listeners[event] = handler; }
    setAttribute(key, value) { this.attributes[key] = value; }
    getAttribute(key) { return this.attributes[key]; }
    appendChild(child) { this.children.push(child); }
    dispatch(event) { this.listeners[event]({ preventDefault() {} }); }
}

function setup(hasSites = true, mode = 'meter') {
    const ids = ['form', 'site', 'file', 'preview', 'import', 'view', 'message', 'results', 'summary', 'rows'];
    const elements = Object.fromEntries(ids.map(id => [id, new Element()]));
    elements.site.options = hasSites ? [{ text: 'Choose' }, { text: 'Test Site' }] : [{ text: 'Choose' }];
    elements.site.selectedIndex = 1;
    elements.site.value = '7';
    elements.file.files = [{ name: 'meters.xlsx', size: 123 }];
    elements.form.attributes = {
        'data-token-url': '/token', 'data-import-url': '/import', 'data-meter-url': '/meters',
        'data-entity': mode
    };
    elements.preview.disabled = true;
    elements.import.disabled = true;
    const calls = [];
    const queue = [];
    const confirms = [];
    let confirm = true;
    const context = {
        document: {
            getElementById(id) { return elements[id.replace('meter-upload-', '')]; },
            createElement() { return new Element(); },
            createDocumentFragment() { return new Element(); }
        },
        window: {
            fetch: true, FormData: true,
            confirm(text) { confirms.push(text); return confirm; }
        },
        async fetch(url, options) {
            calls.push({ url, options });
            assert.ok(queue.length, 'unexpected request');
            return queue.shift()(url, options);
        },
        FormData: class {
            constructor() { this.fields = {}; }
            append(key, value) { this.fields[key] = value; }
        }
    };
    vm.runInNewContext(script, context, { filename: 'meter_upload.js' });
    return {
        elements, calls, confirms, queue,
        cancel(value) { confirm = !value; },
        response(body, status = 200) {
            queue.push(() => ({ ok: status < 400, status, async json() { return body; } }));
        }
    };
}

const malicious = '<img src=x onerror=alert(1)>';
function report(overrides = {}) {
    return Object.assign({
        dry_run: true, total: 1, would_insert: 1, inserted: 0, skipped: 0, invalid: 0, unmatched_gateways: 0,
        rows: [{ sheet: 'Asbuilt', row: 9, meter_name: malicious, gateway_serial: 'GW',
            rtu_sn_number_id: 42, status: 'ready', warnings: [malicious], errors: [] }]
    }, overrides);
}
async function flush() {
    await new Promise(resolve => setImmediate(resolve));
}
async function preview(app, body = report(), status = 200) {
    app.response({ csrf_token: 'fixture-token' });
    app.response(body, status);
    app.elements.form.dispatch('submit');
    assert.equal(app.elements.preview.disabled, true, 'busy controls disabled');
    await flush();
}

async function main() {
    const app = setup();
    const el = app.elements;
    assert.equal(el.import.disabled, true);
    assert.equal(el.preview.disabled, false);
    await preview(app);
    assert.equal(app.calls[1].options.body.fields.dry_run, '1');
    assert.equal(app.calls[1].options.body.fields.site_id, '7');
    assert.equal(app.calls[1].options.headers['X-CSRF-Token'], 'fixture-token');
    assert.equal(app.calls[1].options.credentials, 'same-origin');
    assert.equal(el.import.disabled, false);
    assert.equal(el.results.hidden, false);
    assert.equal(el.message.hidden, false, 'preview feedback remains visible');
    assert.equal(el.rows.children[0].children[0].children[1].textContent, malicious, 'results stay literal text');
    app.cancel(true);
    el.import.dispatch('click');
    await flush();
    assert.equal(app.calls.length, 2, 'cancel never writes');
    assert.equal(el.import.disabled, false);
    app.cancel(false);
    app.response({ csrf_token: 'fixture-token' });
    app.response(report({ dry_run: false, inserted: 1 }));
    el.import.dispatch('click');
    el.import.dispatch('click');
    await flush();
    assert.equal(app.calls.length, 4, 'double click submits once');
    assert.equal(app.calls[3].options.body.fields.dry_run, '0');
    assert.equal(el.import.disabled, true, 'cannot import again without preview');
    assert.equal(el.view.hidden, false);
    assert.equal(el.view.href, '/meters/7/0');
    assert.match(el.message.textContent, /Import complete/);
    await preview(app);
    el.site.dispatch('change');
    assert.equal(el.import.disabled, true, 'site change invalidates preview');
    assert.equal(el.results.hidden, true);
    assert.equal(el.message.hidden, true, 'selection changes do not show explainer banners');
    await preview(app);
    el.file.dispatch('change');
    assert.equal(el.import.disabled, true, 'file change invalidates preview');
    await preview(app);
    el.file.files = [{ name: 'changed.xlsx', size: 12 }];
    const before = app.calls.length;
    el.import.dispatch('click');
    await flush();
    assert.equal(app.calls.length, before, 'changed file cannot reuse preview');
    await preview(app, report({ invalid: 1, would_insert: 0 }), 422);
    assert.equal(el.import.disabled, true);
    assert.match(el.message.textContent, /No meters were saved/);
    await preview(app, report({ would_insert: 0, skipped: 1 }));
    assert.equal(el.import.disabled, true, 'all duplicates cannot be imported');
    el.file.files = [{ name: 'bad.csv', size: 1 }];
    el.form.dispatch('submit');
    await flush();
    assert.match(el.message.textContent, /\.xlsx/);
    el.file.files = [{ name: 'huge.xlsx', size: 5242881 }];
    el.form.dispatch('submit');
    await flush();
    assert.match(el.message.textContent, /5 MiB/);
    el.file.files = [{ name: 'valid.xlsx', size: 1 }];
    app.response({ error: 'Admin login required.' }, 401);
    el.form.dispatch('submit');
    await flush();
    assert.match(el.message.textContent, /Admin login required/);
    assert.equal(el.import.disabled, true);
    await preview(app);
    app.queue.push(() => { throw new Error('Network interrupted'); });
    el.import.dispatch('click');
    await flush();
    assert.match(el.message.textContent, /may have completed/);
    assert.equal(el.import.disabled, true, 'network failure requires new preview');
    assert.equal(el.preview.disabled, false);
    const empty = setup(false);
    assert.equal(empty.elements.preview.disabled, true);
    assert.equal(empty.elements.site.disabled, true);
    const gateways = setup(true, 'gateway');
    const gatewayReport = report({
        rows: [{ sheet: 'Asbuilt', row: 1, rtu_sn_number: 'GW-01', mac_addr: 'aa:bb:cc:dd:ee:01',
            rtu_physical_location: malicious, phone_no_or_ip_address: '192.0.2.10',
            idf_number: 'IDF', switch_name: 'SW', idf_port: '01', status: 'ready', errors: [], warnings: [] }]
    });
    await preview(gateways, gatewayReport);
    assert.match(gateways.elements.message.textContent, /Import Gateways/);
    assert.equal(gateways.elements.summary.textContent.includes('Unmatched'), false);
    const gatewayCells = gateways.elements.rows.children[0].children[0].children;
    assert.equal(gatewayCells[1].textContent, 'GW-01');
    assert.equal(gatewayCells[3].textContent, malicious, 'gateway locations use safe text');
    gateways.response({ csrf_token: 'fixture-token' });
    gateways.response(Object.assign({}, gatewayReport, { dry_run: false, inserted: 1 }));
    gateways.elements.import.dispatch('click');
    await flush();
    assert.match(gateways.confirms[0], /new gateway/);
    assert.match(gateways.elements.message.textContent, /gateway\(s\) inserted/);
    assert.equal(gateways.elements.view.href, '/meters/7', 'gateway management link has only site argument');
    await preview(gateways, Object.assign({}, gatewayReport, { invalid: 1 }), 422);
    assert.match(gateways.elements.message.textContent, /No gateways were saved/);
    assert.equal(gateways.elements.import.disabled, true);
    console.log('PASS: Meter/Gateway Upload UI preview/import, CSRF, safe rendering, confirmation, invalidation and error states.');
}
main().catch(error => { console.error(error); process.exitCode = 1; });
