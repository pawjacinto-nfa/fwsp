<section class="workspace-section">
    <div class="section-head compact">
        <div>
            <p class="eyebrow">Account</p>
            <h3>Notifications</h3>
        </div>
    </div>

    <div class="row g-3">
    <section class="col-lg-8"><div class="panel notification-history">
        <div class="notification-list" data-notification-list>
            <?php foreach ($notifications as $notification): ?>
                <a class="notification-item <?= empty($notification['read']) ? 'is-unread' : '' ?>" href="index.php?notification_id=<?= e($notification['id']) ?>">
                    <p><?= e($notification['message']) ?></p>
                    <small><?= e($notification['time']) ?></small>
                </a>
            <?php endforeach; ?>
            <?php if ($notifications === []): ?>
                <div class="notification-empty">No notifications yet.</div>
            <?php endif; ?>
        </div>
    </div></section>
    <aside class="col-lg-4"><section class="panel notification-settings"><h2 class="h5">Notification settings</h2><form method="post"><input type="hidden" name="action" value="notification-preferences">
        <p class="form-label mb-1">Location notifications</p><div class="toggle-button-group mb-3" role="group"><?php foreach (['Region', 'Province', 'Office', 'Facility'] as $level): ?><label class="toggle-button"><input type="radio" name="location_level" value="<?= e($level) ?>" <?= ($preferences['location_level'] ?? 'Region') === $level ? 'checked' : '' ?>><span><?= e($level) ?></span></label><?php endforeach; ?></div>
        <p class="form-label mb-1">Farmer deliveries</p><div class="toggle-button-group mb-3"><label class="toggle-button"><input type="checkbox" name="farmer_delivery_individual" value="1" <?= !empty($preferences['farmer_delivery_individual']) ? 'checked' : '' ?>><span>Individual</span></label><label class="toggle-button"><input type="checkbox" name="farmer_delivery_fo" value="1" <?= !empty($preferences['farmer_delivery_fo']) ? 'checked' : '' ?>><span>FO</span></label></div>
        <p class="form-label mb-1">Other notifications</p><div class="toggle-button-stack"><?php foreach (['farmer_new' => 'New farmer profiles', 'farmer_updates' => 'Farmer profile updates', 'annual_bag_limit' => 'Farmer reaches 400 bags per year', 'cross_location_delivery' => 'Farmer delivers to another location', 'tech_support' => 'Tech support updates', 'account_updates' => 'Account and access updates'] as $key => $label): ?><label class="toggle-button"><input type="checkbox" name="<?= e($key) ?>" value="1" <?= !empty($preferences[$key]) ? 'checked' : '' ?>><span><?= e($label) ?></span></label><?php endforeach; ?></div><button class="btn btn-success mt-3" type="submit">Save settings</button></form></section></aside>
    </div>
</section>
