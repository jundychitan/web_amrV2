<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading"><h1 class="panel-title">Meter Upload</h1></div>
        <div class="panel-body">
            <?php if (!$sites): ?>
            <div class="alert alert-warning">No sites are available.</div>
            <?php endif; ?>
            <noscript><div class="alert alert-warning">JavaScript is required.</div></noscript>
            <form id="meter-upload-form" enctype="multipart/form-data"
                data-import-url="<?php echo htmlspecialchars(site_url('api/meters/import'), ENT_QUOTES, 'UTF-8'); ?>"
                data-token-url="<?php echo htmlspecialchars(site_url('api/meters/import/token'), ENT_QUOTES, 'UTF-8'); ?>"
                data-meter-url="<?php echo htmlspecialchars(site_url('admin/meter_management'), ENT_QUOTES, 'UTF-8'); ?>">
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
                        accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                </div>
                <button id="meter-upload-preview" type="submit" class="btn btn-primary" disabled>Validate / Preview</button>
                <button id="meter-upload-import" type="button" class="btn btn-success" disabled>Import Meters</button>
                <a id="meter-upload-view" class="btn btn-default" hidden>View Site Meters</a>
            </form>
            <hr>
            <div id="meter-upload-message" class="alert alert-info" role="status" aria-live="polite" hidden></div>
            <section id="meter-upload-results" hidden aria-labelledby="meter-upload-results-title">
                <h2 id="meter-upload-results-title" class="h4">Results</h2>
                <p id="meter-upload-summary"></p>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr><th scope="col">Sheet / Row</th><th scope="col">Meter Serial</th>
                                <th scope="col">Gateway Serial</th><th scope="col">Gateway ID</th>
                                <th scope="col">Status</th><th scope="col">Warnings / Errors</th></tr>
                        </thead>
                        <tbody id="meter-upload-rows"></tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>
<script src="<?php echo htmlspecialchars(base_url('assets/meter_upload.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
