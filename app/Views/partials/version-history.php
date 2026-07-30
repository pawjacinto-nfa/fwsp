<?php if (!empty($versions ?? [])): ?>
<section class="panel table-section mt-4">
    <div class="panel-head"><h2>Version History</h2></div>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Date</th><th>Changed By</th><th class="text-end">Changes</th></tr></thead><tbody>
    <?php foreach ($versions as $version): ?>
        <?php $versionCollapseId = 'version-history-' . (int) $version['id']; ?>
        <tr>
            <td><?= e($version['created_at']) ?></td>
            <td><?= e($version['changed_by_name']) ?></td>
            <td class="text-end"><button class="btn btn-sm btn-outline-success" type="button" data-bs-toggle="collapse" data-bs-target="#<?= e($versionCollapseId) ?>" aria-expanded="false" aria-controls="<?= e($versionCollapseId) ?>">View Changes</button></td>
        </tr>
        <tr>
            <td colspan="3" class="p-0 border-0"><div id="<?= e($versionCollapseId) ?>" class="collapse"><div class="p-3 bg-light border-bottom">
                <?php foreach ($version['changes'] as $field => $change): ?><div class="mb-2"><strong><?= e(ucwords(str_replace('_', ' ', $field))) ?>:</strong> <mark><?= e($change['from'] === '' ? 'Blank' : $change['from']) ?></mark> &rarr; <mark><?= e($change['to'] === '' ? 'Blank' : $change['to']) ?></mark></div><?php endforeach; ?>
            </div></div></td>
        </tr>
    <?php endforeach; ?>
    </tbody></table></div>
</section>
<?php endif; ?>
