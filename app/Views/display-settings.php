<section class="workspace-section display-settings-page">
    <div class="section-head compact"><div><p class="eyebrow">System Admin</p><h3>Display Settings</h3><p class="mb-0 text-muted">Control the landing slideshow and review community photo submissions.</p></div></div>
    <form method="post" class="panel form-panel mb-4">
        <input type="hidden" name="action" value="display-settings-save">
        <div class="row g-3 align-items-end">
            <div class="col-md-4"><label class="form-label" for="loopDuration">Loop duration</label><div class="input-group"><input class="form-control" id="loopDuration" type="number" min="3" max="30" name="loop_duration" value="<?= e((string) $settings['loop_duration']) ?>"><span class="input-group-text">seconds</span></div></div>
            <div class="col-md-5"><div class="form-check form-switch pt-2"><input class="form-check-input" type="checkbox" role="switch" id="panningEnabled" name="panning_enabled" value="1" <?= !empty($settings['panning_enabled']) ? 'checked' : '' ?>><label class="form-check-label" for="panningEnabled">Enable slow photo panning</label></div></div>
            <div class="col-md-3"><button class="btn btn-success w-100" type="submit">Save Display Settings</button></div>
        </div>
    </form>
    <div class="panel overflow-auto">
        <div class="section-head compact"><div><h4>Photo Management</h4><p class="mb-0 text-muted">Approve submissions to resize and optimize them for the landing page. Lower position values appear first.</p></div></div>
        <table class="table align-middle display-photo-table"><thead><tr><th>Photo</th><th>Details</th><th>Source</th><th>Position</th><th>Status</th><th>Action</th></tr></thead><tbody>
        <?php foreach ($photos as $photo): ?>
            <tr><td><img class="display-photo-thumb" src="<?= e($photo['optimized_path'] ?: $photo['image_path']) ?>" alt=""></td><td><strong><?= e($photo['title']) ?></strong><small>Photo: <?= e($photo['photographer_name']) ?><?= $photo['location'] ? ' · ' . e($photo['location']) : '' ?></small><small><?= $photo['image_width'] && $photo['image_height'] ? number_format((int) $photo['image_width']) . ' × ' . number_format((int) $photo['image_height']) . ' px' : '' ?></small></td><td><?= e($photo['source']) ?><?= $photo['submitter_name'] ? '<small>Submitted by ' . e($photo['submitter_name']) . '</small>' : '' ?></td><td><form method="post" class="d-flex gap-1"><input type="hidden" name="action" value="display-photo-position"><input type="hidden" name="id" value="<?= e((string) $photo['id']) ?>"><input class="form-control form-control-sm display-position-input" type="number" min="1" name="position" value="<?= e((string) $photo['position']) ?>"><button class="btn btn-sm btn-outline-success" type="submit">Save</button></form></td><td><span class="badge text-bg-<?= $photo['status'] === 'Approved' ? 'success' : ($photo['status'] === 'Pending' ? 'warning' : 'secondary') ?>"><?= e($photo['status']) ?></span></td><td>
                <?php if ($photo['source'] === 'User submission'): ?><form method="post" class="d-flex gap-2 align-items-center"><input type="hidden" name="action" value="display-photo-review"><input type="hidden" name="id" value="<?= e((string) $photo['id']) ?>"><input class="form-control form-control-sm display-position-input" type="number" min="1" name="position" value="<?= e((string) $photo['position']) ?>"><button class="btn btn-sm btn-success" type="submit" name="status" value="Approved">Approve</button><button class="btn btn-sm btn-outline-secondary" type="submit" name="status" value="Rejected">Reject</button></form><?php endif; ?>
            </td></tr>
        <?php endforeach; ?>
        </tbody></table>
    </div>
</section>
