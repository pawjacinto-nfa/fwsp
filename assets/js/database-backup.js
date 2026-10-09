(() => {
    'use strict';
    const root = document.getElementById('database-backup-wizard');
    if (!root) return;
    const message = document.getElementById('backup-message');
    let busy = false;
    function notice(text, type = 'info') {
        message.className = `alert alert-${type}`;
        message.textContent = text;
        message.focus();
    }
    function lock(value) {
        busy = value;
        root.setAttribute('aria-busy', String(value));
        root.querySelectorAll('button').forEach(button => { button.disabled = value; });
    }
    window.addEventListener('beforeunload', event => {
        if (busy) { event.preventDefault(); event.returnValue = ''; }
    });
    root.querySelectorAll('[data-backup-action]').forEach(form => {
        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (busy || !form.reportValidity()) return;
            const action = form.dataset.backupAction;
            const data = new FormData(form);
            lock(true);
            notice(action === 'restore' ? 'Creating a recovery backup, then restoring and checking records. Keep this page open. Do not submit again.' : action === 'upload' ? 'Uploading and checking every table. Your database records are unchanged.' : 'Creating and verifying your database backup. Keep this page open.');
            try {
                const response = await fetch('index.php', {method: 'POST', body: data, credentials: 'same-origin', headers: {'X-Requested-With': 'fetch'}});
                const result = await response.json().catch(() => { throw new Error('The server returned an unexpected response. Refresh Backup history before retrying.'); });
                if (!response.ok || !result.success) throw new Error(result.message || 'The request could not finish. Check Backup history.');
                form.querySelectorAll('input[type=password]').forEach(input => { input.value = ''; });
                notice(result.message, 'success');
                if (action === 'create') {
                    const created = document.getElementById('backup-created');
                    created.classList.remove('d-none');
                    created.replaceChildren();
                    const text = document.createElement('p');
                    text.textContent = `${result.backup.tables} tables · ${result.backup.rows.toLocaleString()} records · ${(result.backup.bytes / 1048576).toFixed(2)} MB. Backup verified.`;
                    const link = document.createElement('a');
                    link.href = 'index.php?page=system-maintenance&tab=database';
                    link.className = 'btn btn-success';
                    link.textContent = 'Open history to download ZIP or SQL';
                    created.append(text, link);
                } else if (action === 'upload') {
                    document.getElementById('backup-restore-id').value = result.backup.id;
                    document.getElementById('backup-review-summary').textContent = `Database label: ${result.backup.database}. ${result.backup.tables} tables, ${result.backup.rows.toLocaleString()} records. ${result.backup.backup_date ? 'Backup date: ' + result.backup.backup_date : 'SQL backup date is unknown; reviewed at ' + result.backup.created_at}.`;
                    const table = document.createElement('table'); table.className = 'table table-sm';
                    const body = document.createElement('tbody');
                    result.backup.table_summary.forEach(row => {
                        const tr = document.createElement('tr');
                        [row.name, row.rows.toLocaleString()].forEach(value => { const td = document.createElement('td'); td.textContent = value; tr.append(td); });
                        body.append(tr);
                    });
                    table.append(body); document.getElementById('backup-table-summary').replaceChildren(table);
                    document.getElementById('backup-review').classList.remove('d-none');
                    document.getElementById('backup-acknowledge').checked = false;
                    document.getElementById('backup-confirmation').value = '';
                } else {
                    root.querySelectorAll('form').forEach(item => { item.hidden = true; });
                    const link = document.createElement('a'); link.href = 'index.php?show_login=1'; link.textContent = ' Sign in again'; message.append(link);
                }
            } catch (error) { notice(error.message || 'Connection interrupted. Check Backup history before retrying.', 'danger'); }
            finally { lock(false); }
        });
    });
    document.getElementById('backup-cancel').addEventListener('click', () => {
        document.querySelector('[data-backup-action=restore]').reset();
        document.getElementById('backup-restore-id').value = '';
        document.getElementById('backup-review').classList.add('d-none');
        notice('Review canceled. No database records were changed.');
    });
})();
