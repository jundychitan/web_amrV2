<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading"><h1 class="panel-title">Gateway Upload</h1></div>
        <div class="panel-body">
            <p>Select a site and Excel workbook, preview the gateways, then confirm the import.</p>
            <div class="alert alert-info">
                Existing gateways are skipped, not overwritten. Conflicting serials or MAC addresses block the import.
                Defaults: connection type <strong>LAN</strong>; netmask, IP gateway and server IP <strong>n/a</strong>.
                Gateway description uses Gateway Location. The selected site's code supplies rtu_site_name.
                Remote-update flags remain disabled. No gateways are saved if any row has a validation error.
            </div>
            <?php if (!$sites): ?>
            <div class="alert alert-warning">No sites are available. Create a site under Maintenance &rarr; Site Management first.</div>
            <?php endif; ?>
            <noscript><div class="alert alert-warning">Enable JavaScript to validate and import a workbook.</div></noscript>
            <!-- Reuse the upload component and its preview/confirmation safeguards. -->
            <form id="meter-upload-form" data-entity="gateway" enctype="multipart/form-data"
                data-import-url="<?php echo htmlspecialchars(site_url('api/gateways/import'), ENT_QUOTES, 'UTF-8'); ?>"
                data-token-url="<?php echo htmlspecialchars(site_url('api/gateways/import/token'), ENT_QUOTES, 'UTF-8'); ?>"
                data-meter-url="<?php echo htmlspecialchars(site_url('admin/rtu_management'), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="form-group">
                    <label for="meter-upload-site">Destination site</label>
                    <select id="meter-upload-site" name="site_id" class="form-control" required>
                        <option value="">Select a site</option>
                        <?php foreach ($sites as $site): ?>
                        <option value="<?php echo (int) $site['id']; ?>"><?php
                            echo htmlspecialchars($site['site_name'].' ('.$site['site_code'].')', ENT_QUOTES, 'UTF-8');
                        ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="meter-upload-file">Excel workbook (.xlsx)</label>
                    <input id="meter-upload-file" name="file" type="file" class="form-control"
                        accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required
                        aria-describedby="gateway-upload-help">
                    <p id="gateway-upload-help" class="help-block">
                        Maximum 5 MiB. Reads the Asbuilt gateway metadata blocks (meter rows are ignored),
                        or a Gateway/single-sheet table. When both sheets exist, Asbuilt is used.
                        Columns: Gateway serial no, Gateway mac address, Gateway Location, Gateway IP address,
                        IDF Number, Switch, Port number. Missing IDF/switch/port values remain blank with warnings.
                        MAC addresses are saved in the schema's mac_addr column.
                    </p>
                </div>
                <button id="meter-upload-preview" type="submit" class="btn btn-primary" disabled>Validate / Preview</button>
                <button id="meter-upload-import" type="button" class="btn btn-success" disabled>Import Gateways</button>
                <a id="meter-upload-view" class="btn btn-default" hidden>View Site Gateways</a>
            </form>
            <hr>
            <div id="meter-upload-message" class="alert alert-info" role="status" aria-live="polite">
                Choose a site and workbook to begin. Preview does not save any gateways.
            </div>
            <section id="meter-upload-results" hidden aria-labelledby="gateway-upload-results-title">
                <h2 id="gateway-upload-results-title" class="h4">Results</h2>
                <p id="meter-upload-summary"></p>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead><tr><th scope="col">Sheet / Row</th><th scope="col">Gateway Serial</th>
                            <th scope="col">MAC Address</th><th scope="col">Location</th><th scope="col">IP Address</th>
                            <th scope="col">IDF / Switch / Port</th><th scope="col">Status</th>
                            <th scope="col">Warnings / Errors</th></tr></thead>
                        <tbody id="meter-upload-rows"></tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>
<script src="<?php echo htmlspecialchars(base_url('assets/meter_upload.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
