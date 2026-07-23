<?php
$ticketRows = $tickets ?? [];
$isSuperAdmin = !empty($isSuperAdmin);
$archiveKey = $isSuperAdmin ? 'admin_archived' : 'reporter_archived';
?>
<section class="workspace-section tech-support-page">
    <div class="section-head compact tech-support-head">
        <div>
            <p class="eyebrow">Developer Team Desk</p>
            <h3>Tech Support</h3>
        </div>
        <img class="tech-support-head-logo" src="assets/images/fwsp-logos.gif" alt="" aria-hidden="true">
    </div>

    <?php if (!$isSuperAdmin): ?>
        <div class="panel support-submit-panel">
            <div class="panel-head">
                <div>
                    <p class="eyebrow">Ticketing Division</p>
                    <h2>Submit a concern to the developer team</h2>
                </div>
            </div>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="support-ticket">
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label" for="supportTitle">Title</label>
                        <input class="form-control" id="supportTitle" name="title" required maxlength="180" placeholder="Brief summary of the concern">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="supportCategory">Category</label>
                        <select class="form-select" id="supportCategory" name="category" required>
                            <option value="">Choose category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= e($category) ?>"><?= e($category) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="supportDescription">Description</label>
                        <textarea class="form-control" id="supportDescription" name="description" rows="5" required placeholder="Tell us what happened, the page or form involved, and any steps that repeat the issue."></textarea>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label" for="supportScreenshot">Upload screenshot if applicable</label>
                        <input class="form-control" id="supportScreenshot" name="screenshot" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    </div>
                    <div class="col-md-4 d-flex align-items-end justify-content-md-end">
                        <button class="btn btn-success w-100 w-md-auto" type="submit">Submit Ticket</button>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <div class="panel support-ticket-panel">
        <div class="panel-head">
            <div>
                <p class="eyebrow"><?= $isSuperAdmin ? 'System Admin Queue' : 'My Reports' ?></p>
                <h2><?= $isSuperAdmin ? 'Submitted Tickets' : 'Submitted Concerns' ?></h2>
            </div>
            <span class="support-count"><?= number_format(count($ticketRows)) ?> ticket<?= count($ticketRows) === 1 ? '' : 's' ?></span>
        </div>

        <?php if ($ticketRows === []): ?>
            <div class="support-empty">No tech support tickets yet.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle support-ticket-table" data-no-sort="true" data-page-size="20" data-paginate-row-selector=".ticket-summary-row">
                    <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Reporter</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($ticketRows as $ticket): ?>
                        <?php
                        $ticketId = (int) $ticket['id'];
                        $collapseId = 'ticketDetails' . $ticketId;
                        $isArchived = !empty($ticket[$archiveKey]);
                        ?>
                        <tr class="ticket-summary-row <?= $isArchived ? 'support-ticket-archived' : '' ?>">
                            <td>
                                <button class="support-ticket-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#<?= e($collapseId) ?>" aria-expanded="false" aria-controls="<?= e($collapseId) ?>">
                                    #<?= e($ticketId) ?> <?= e($ticket['title']) ?>
                                </button>
                                <small><?= e($ticket['submitted_at'] ?? '') ?></small>
                            </td>
                            <td><?= e($ticket['reporter_name'] ?: 'Anonymous') ?></td>
                            <td><?= e($ticket['category']) ?></td>
                            <td><span class="support-status <?= $ticket['status'] === 'Completed' ? 'is-completed' : 'is-open' ?>"><?= e($ticket['status']) ?></span></td>
                            <td><?= e($ticket['updated_label'] ?? '') ?></td>
                            <td class="support-actions">
                                <?php if ($isSuperAdmin && $ticket['status'] !== 'Completed'): ?>
                                    <form method="post">
                                        <input type="hidden" name="action" value="support-ticket-complete">
                                        <input type="hidden" name="ticket_id" value="<?= e($ticketId) ?>">
                                        <button class="btn btn-sm btn-success" type="submit">Completed</button>
                                    </form>
                                <?php endif; ?>
                                <form method="post">
                                    <input type="hidden" name="action" value="support-ticket-archive">
                                    <input type="hidden" name="ticket_id" value="<?= e($ticketId) ?>">
                                    <button class="btn btn-sm btn-outline-secondary" type="submit">Archive</button>
                                </form>
                            </td>
                        </tr>
                        <tr class="ticket-detail-row <?= $isArchived ? 'support-ticket-archived' : '' ?>">
                            <td colspan="6" class="p-0">
                                <div class="collapse" id="<?= e($collapseId) ?>">
                                    <div class="support-ticket-detail">
                                        <div class="support-ticket-description">
                                            <strong>Description</strong>
                                            <p><?= nl2br(e($ticket['description'])) ?></p>
                                            <?php if (!empty($ticket['screenshot_path'])): ?>
                                                <a class="support-screenshot-link" href="<?= e($ticket['screenshot_path']) ?>" target="_blank" rel="noopener">View uploaded screenshot</a>
                                            <?php endif; ?>
                                        </div>
                                        <div class="support-thread">
                                            <strong>Conversation</strong>
                                            <?php foreach (($ticket['messages'] ?? []) as $message): ?>
                                                <div class="support-message <?= ($message['sender_role'] ?? '') === 'System Admin' ? 'from-admin' : 'from-user' ?>">
                                                    <div><b><?= e($message['sender_name']) ?></b> <span><?= e($message['sent_at']) ?></span></div>
                                                    <p><?= nl2br(e($message['message'])) ?></p>
                                                </div>
                                            <?php endforeach; ?>
                                            <?php if (($ticket['messages'] ?? []) === []): ?>
                                                <p class="text-muted mb-0">No replies yet.</p>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($ticket['status'] === 'Completed'): ?>
                                            <div class="support-closed-note">This ticket is completed and closed for replies.</div>
                                        <?php else: ?>
                                            <form class="support-reply-form" method="post">
                                                <input type="hidden" name="action" value="support-ticket-reply">
                                                <input type="hidden" name="ticket_id" value="<?= e($ticketId) ?>">
                                                <label class="form-label" for="reply<?= e($ticketId) ?>">Reply</label>
                                                <textarea class="form-control" id="reply<?= e($ticketId) ?>" name="message" rows="3" required></textarea>
                                                <button class="btn btn-success btn-sm" type="submit">Send Reply</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="panel support-about">
        <p class="eyebrow">About Us</p>
        <h2>Corporate Planning and Management Services Department</h2>
        <p>Information and Communications Technology Support Division - Software Development Unit</p>
        <p>Created by <strong>Paulo Anthony A. Jacinto</strong>, Computer Programmer II, under the supervision of <strong>Rainier John S. Dela Cruz</strong>, Information Systems Analyst III, and <strong>Gary R. Riparip</strong>, Division Chief - ICTSD.</p>
    </div>
</section>
