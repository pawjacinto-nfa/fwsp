<section class="workspace-section tech-support-page">
    <div class="section-head compact"><div><p class="eyebrow">System Admin Queue</p><h3>Archived Tech Support</h3></div><a class="btn btn-outline-success" href="index.php?page=tech-support">Back to active tickets</a></div>
    <section class="panel support-ticket-panel">
        <?php if (($tickets ?? []) === []): ?><div class="support-empty">No archived tickets.</div><?php else: ?>
        <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Ticket</th><th>Reporter</th><th>Category</th><th>Status</th><th>Archived / Updated</th></tr></thead><tbody>
        <?php foreach ($tickets as $ticket): ?><tr><td>#<?= e($ticket['id']) ?> <?= e($ticket['title']) ?><small class="d-block text-muted"><?= nl2br(e($ticket['description'])) ?></small></td><td><?= e($ticket['reporter_name'] ?: 'Anonymous') ?></td><td><?= e($ticket['category']) ?></td><td><?= e($ticket['status']) ?></td><td><?= e($ticket['updated_label'] ?? '') ?></td></tr><?php endforeach; ?>
        </tbody></table></div><?php endif; ?>
    </section>
</section>
