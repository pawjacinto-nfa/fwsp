<section class="workspace-section">
    <div class="section-head compact">
        <div>
            <p class="eyebrow">Deletion review</p>
            <h3>Transactions affected by deleting this <?= e($recordType) ?></h3>
            <p class="mb-0 text-muted">Review each linked transaction before completing the deletion. The original transaction data will remain available after deletion.</p>
        </div>
    </div>

    <div class="alert alert-warning">
        <strong><?= e($recordName) ?></strong> is linked to <?= count($transactions) ?> active transaction<?= count($transactions) === 1 ? '' : 's' ?>. Open any transaction that needs correction, edit it, and save it before confirming this deletion.
    </div>

    <div class="accordion mb-3" id="affectedTransactionsAccordion">
        <?php foreach ($transactions as $index => $transaction): ?>
            <?php $deliveryPage = ($transaction['type'] ?? '') === 'Farmer Organization' ? 'organization-delivery' : 'individual-delivery'; ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="affectedTransactionHeading<?= e($transaction['id']) ?>">
                    <button class="accordion-button <?= $index === 0 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#affectedTransaction<?= e($transaction['id']) ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="affectedTransaction<?= e($transaction['id']) ?>">
                        <?= e($transaction['type']) ?> delivery · <?= e($transaction['wsr']) ?> · <?= e($transaction['delivery_date']) ?>
                    </button>
                </h2>
                <div id="affectedTransaction<?= e($transaction['id']) ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="affectedTransactionHeading<?= e($transaction['id']) ?>" data-bs-parent="#affectedTransactionsAccordion">
                    <div class="accordion-body">
                        <dl class="row mb-3">
                            <dt class="col-sm-3">Transaction ID</dt><dd class="col-sm-9">#<?= e($transaction['id']) ?></dd>
                            <dt class="col-sm-3">Farmer Group</dt><dd class="col-sm-9"><?= e($transaction['farmer_group_name'] ?: 'N/A') ?></dd>
                            <dt class="col-sm-3">50 kg bags</dt><dd class="col-sm-9"><?= number_format((float) $transaction['bags'], 3) ?></dd>
                            <dt class="col-sm-3">Net kg</dt><dd class="col-sm-9"><?= number_format((float) $transaction['net_kg'], 3) ?></dd>
                            <dt class="col-sm-3">Total amount</dt><dd class="col-sm-9"><?= number_format((float) $transaction['total_amount'], 3) ?></dd>
                        </dl>
                        <a class="btn btn-outline-success" href="index.php?page=<?= e($deliveryPage) ?>&transaction_id=<?= e($transaction['id']) ?>">Open Transaction for Edit / Save</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <form method="post" class="d-flex flex-wrap gap-2">
        <input type="hidden" name="action" value="<?= e($deleteAction) ?>">
        <input type="hidden" name="confirm_delete_after_review" value="1">
        <?php foreach ($deleteFields as $name => $value): ?>
            <input type="hidden" name="<?= e($name) ?>" value="<?= e((string) $value) ?>">
        <?php endforeach; ?>
        <a class="btn btn-outline-secondary" href="<?= e($cancelUrl) ?>">Cancel</a>
        <button class="btn btn-danger" type="submit" onclick="return confirm('Confirm that you reviewed the affected transactions and mark this <?= e(strtolower($recordType)) ?> as deleted?')">Confirm Deletion After Review</button>
    </form>
</section>
