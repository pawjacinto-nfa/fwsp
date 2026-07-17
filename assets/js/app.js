const loader = document.getElementById("loaderScreen");

/* Offline delivery queue. Records are stored locally only until this user approves upload. */
(() => {
    const config = window.FWSP_OFFLINE || {};
    const key = `fwsp-offline-deliveries-${config.userId || 'guest'}`;
    const enabled = () => Boolean(config.enabled && config.userId);
    const read = () => { try { return JSON.parse(localStorage.getItem(key) || '[]'); } catch { return []; } };
    const write = (items) => localStorage.setItem(key, JSON.stringify(items));
    const controlNumber = () => `OFF-${config.userId}-${Date.now().toString(36)}-${crypto.getRandomValues(new Uint32Array(1))[0].toString(36)}`.toUpperCase();
    const modal = (title, body, footer = '') => `<div class="modal fade auth-modal" id="offlineSystemModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h2 class="modal-title fs-5">${title}</h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">${body}</div><div class="modal-footer">${footer || '<button class="btn btn-success" data-bs-dismiss="modal">OK</button>'}</div></div></div></div>`;
    const show = (title, body, footer) => { document.getElementById('offlineSystemModal')?.remove(); document.body.insertAdjacentHTML('beforeend', modal(title, body, footer)); const node = document.getElementById('offlineSystemModal'); const instance = new bootstrap.Modal(node); instance.show(); return node; };
    const banner = (message, buttons = '', error = false) => { document.getElementById('offlineConnectionBanner')?.remove(); document.body.insertAdjacentHTML('beforeend', `<aside class="offline-banner${error ? ' is-error' : ''}" id="offlineConnectionBanner"><p>${message}</p>${buttons}</aside>`); };
    const clearBanner = () => document.getElementById('offlineConnectionBanner')?.remove();
    const refreshBadge = () => document.querySelectorAll('[data-offline-queue-count]').forEach(el => { const count = read().length; el.textContent = count ? count : ''; el.hidden = !count; });
    const install = async () => {
        const node = show('Preparing offline workspace', `<p class="text-muted">Setting up secure local delivery capture for this device.</p><div data-install-steps><div class="offline-step is-active"><i>1</i><span>Registering offline workspace</span></div><div class="offline-step"><i>2</i><span>Downloading delivery forms and resources</span></div><div class="offline-step"><i>3</i><span>Verifying local storage</span></div></div>`, '');
        try { if (!('serviceWorker' in navigator)) throw new Error('This browser does not support offline workspaces.'); await navigator.serviceWorker.register('service-worker.js'); await navigator.serviceWorker.ready; const steps = node.querySelectorAll('.offline-step'); for (const step of steps) { step.classList.remove('is-active'); step.classList.add('is-done'); await new Promise(resolve => setTimeout(resolve, 350)); } node.querySelector('.modal-footer').innerHTML = '<button class="btn btn-success" data-bs-dismiss="modal">Offline workspace ready</button>'; }
        catch (error) { node.querySelector('.modal-body').insertAdjacentHTML('beforeend', `<div class="alert alert-danger mt-3 mb-0">${error.message} Keep your connection on and try again.</div>`); node.querySelector('.modal-footer').innerHTML = '<button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>'; }
    };
    const queueForm = (form) => {
        const data = Object.fromEntries(new FormData(form).entries());
        data.delivered_farmer_ids = [...new FormData(form).getAll('delivered_farmer_ids[]')];
        delete data['delivered_farmer_ids[]'];
        delete data.csrf_token; data.action = 'offline-sync-transaction'; data.client_control_number = controlNumber();
        const items = read(); items.push({ control: data.client_control_number, data, createdAt: new Date().toISOString() }); write(items); form.reset(); refreshBadge();
        show('Delivery saved offline', `<p>Your delivery input has been saved securely on this device.</p><p class="mb-0"><strong>Control number:</strong> ${data.client_control_number}</p>`);
    };
    const sync = async () => {
        const items = read(); if (!items.length) { clearBanner(); return; }
        const node = show('Uploading offline inputs', `<p class="text-muted">Do not close this window while your saved delivery inputs are being validated.</p><div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%">0%</div></div><p class="small mt-2 mb-0" data-sync-status>Preparing upload…</p>`, '');
        const bar = node.querySelector('.progress-bar'), status = node.querySelector('[data-sync-status]'), remaining = [];
        for (let i = 0; i < items.length; i++) { const item = items[i]; status.textContent = `Uploading ${i + 1} of ${items.length} (${item.control})…`; try { const body = new URLSearchParams({ ...item.data, csrf_token: config.csrfToken }); body.delete('delivered_farmer_ids'); item.data.delivered_farmer_ids.forEach(id => body.append('delivered_farmer_ids[]', id)); const response = await fetch(config.syncUrl, { method: 'POST', headers: { 'X-Requested-With': 'fetch', 'Content-Type': 'application/x-www-form-urlencoded' }, body, credentials: 'same-origin' }); const result = await response.json(); if (!response.ok || !result.success) throw new Error(result.message || 'The server rejected this input.'); } catch (error) { remaining.push(item); } bar.style.width = `${Math.round(((i + 1) / items.length) * 100)}%`; bar.textContent = bar.style.width; }
        write(remaining); refreshBadge(); node.querySelector('.modal-footer').innerHTML = remaining.length ? `<button class="btn btn-warning" data-bs-dismiss="modal">${remaining.length} input(s) need attention</button>` : '<button class="btn btn-success" data-bs-dismiss="modal">Upload complete</button>'; status.textContent = remaining.length ? `${remaining.length} input(s) were kept safely on this device. Check your connection or correct the record before retrying.` : 'All offline inputs were uploaded successfully. You are back online.'; if (!remaining.length) setTimeout(() => location.reload(), 900); }
    document.querySelector('[data-offline-enable]')?.addEventListener('change', event => { if (event.target.checked) install(); });
    document.querySelectorAll('form.tracked-form').forEach(form => form.addEventListener('submit', event => { if (enabled() && !navigator.onLine) { event.preventDefault(); if (!form.reportValidity()) return; queueForm(form); } }));
    document.querySelectorAll('[data-offline-unavailable]').forEach(el => { if (enabled() && !navigator.onLine) el.hidden = true; });
    window.addEventListener('offline', () => { if (enabled()) banner('You are offline. Delivery forms remain available; reports and support are unavailable.'); else banner('You are offline — turn on offline mode?', '<a class="btn btn-sm btn-warning" href="index.php?page=account">Enable offline mode</a>'); });
    window.addEventListener('online', () => { if (enabled() && read().length) banner(`Connection available. ${read().length} offline input(s) are waiting to upload.`, '<button class="btn btn-sm btn-warning" data-upload-offline>Upload offline inputs?</button>'); else clearBanner(); });
    document.addEventListener('click', event => { if (event.target.closest('[data-upload-offline]')) sync(); });
    if (enabled() && !navigator.onLine) window.dispatchEvent(new Event('offline')); else if (enabled() && read().length) window.dispatchEvent(new Event('online'));
    refreshBadge();
})();

document.querySelectorAll("[data-signatory-add-form]").forEach((form) => {
    const rows = form.querySelector("[data-signatory-form-rows]");
    const template = rows?.querySelector("[data-signatory-form-row]");
    const updateRemoveButtons = () => {
        const allRows = [...(rows?.querySelectorAll("[data-signatory-form-row]") ?? [])];
        allRows.forEach((row) => {
            const button = row.querySelector("[data-remove-signatory-row]");
            if (button) button.hidden = allRows.length === 1;
        });
    };

    form.querySelector("[data-add-signatory-row]")?.addEventListener("click", () => {
        if (!rows || !template) return;
        const newRow = template.cloneNode(true);
        newRow.querySelectorAll("input").forEach((input) => { input.value = ""; });
        rows.append(newRow);
        updateRemoveButtons();
        newRow.querySelector("input")?.focus();
    });

    rows?.addEventListener("click", (event) => {
        const button = event.target.closest("[data-remove-signatory-row]");
        if (!button) return;
        button.closest("[data-signatory-form-row]")?.remove();
        updateRemoveButtons();
    });
    updateRemoveButtons();
});

document.querySelectorAll("[data-report-signatory-selector]").forEach((selector) => {
    const optionsContainer = selector.querySelector(".report-signatory-options");
    const orderKey = selector.dataset.orderKey || "fwsp-report-signatory-order";
    const getOptions = () => [...selector.querySelectorAll("[data-signatory-option]")];
    let savedOrder = [];
    try {
        savedOrder = JSON.parse(localStorage.getItem(orderKey) || "[]");
    } catch (error) {
        localStorage.removeItem(orderKey);
    }
    if (optionsContainer && Array.isArray(savedOrder)) {
        const currentOptions = getOptions();
        const optionMap = new Map(currentOptions.map((option) => [option.dataset.signatoryId, option]));
        const orderedOptions = savedOrder.map((id) => optionMap.get(String(id))).filter(Boolean);
        const rememberedIds = new Set(orderedOptions.map((option) => option.dataset.signatoryId));
        currentOptions.filter((option) => !rememberedIds.has(option.dataset.signatoryId)).forEach((option) => orderedOptions.push(option));
        orderedOptions.forEach((option) => optionsContainer.append(option));
    }

    const saveOrder = () => {
        localStorage.setItem(orderKey, JSON.stringify(getOptions().map((option) => option.dataset.signatoryId)));
    };

    const syncPrintedSignatories = () => {
        const selected = getOptions().filter((option) => option.querySelector("[data-signatory-toggle]")?.getAttribute("aria-pressed") === "true");
        document.querySelectorAll(".report-signatory-footer").forEach((footer) => footer.remove());
        if (selected.length === 0) return;

        document.querySelectorAll(".report-sheet").forEach((sheet) => {
            const footer = document.createElement("footer");
            footer.className = "report-signatory-footer";
            selected.forEach((option) => {
                const block = document.createElement("div");
                block.className = "report-signatory-block";
                const role = option.querySelector("[data-signatory-role]")?.value || "";
                block.innerHTML = `<span class="report-signatory-role"></span><span class="report-signature-space" aria-hidden="true"></span><strong></strong><small></small>`;
                block.querySelector(".report-signatory-role").textContent = role;
                block.querySelector("strong").textContent = option.dataset.name || "";
                block.querySelector("small").textContent = option.dataset.designation || "";
                footer.append(block);
            });
            sheet.append(footer);
        });
    };

    let draggedOption = null;
    getOptions().forEach((option) => {
        const toggle = option.querySelector("[data-signatory-toggle]");
        toggle?.addEventListener("click", () => {
            toggle.setAttribute("aria-pressed", toggle.getAttribute("aria-pressed") === "true" ? "false" : "true");
            syncPrintedSignatories();
        });
        option.querySelector("[data-signatory-role]")?.addEventListener("change", syncPrintedSignatories);

        toggle?.addEventListener("dragstart", (event) => {
            draggedOption = option;
            option.classList.add("is-dragging");
            event.dataTransfer.effectAllowed = "move";
            event.dataTransfer.setData("text/plain", option.dataset.signatoryId || "");
        });
        toggle?.addEventListener("dragend", () => {
            option.classList.remove("is-dragging");
            draggedOption = null;
            saveOrder();
            syncPrintedSignatories();
        });
    });

    optionsContainer?.addEventListener("dragover", (event) => {
        if (!draggedOption) return;
        event.preventDefault();
        event.dataTransfer.dropEffect = "move";
        const target = event.target.closest("[data-signatory-option]");
        if (!target || target === draggedOption) return;
        const bounds = target.getBoundingClientRect();
        const sameRow = event.clientY >= bounds.top && event.clientY <= bounds.bottom;
        const insertBefore = sameRow
            ? event.clientX < bounds.left + bounds.width / 2
            : event.clientY < bounds.top + bounds.height / 2;
        optionsContainer.insertBefore(draggedOption, insertBefore ? target : target.nextSibling);
    });

    optionsContainer?.addEventListener("drop", (event) => {
        event.preventDefault();
        saveOrder();
        syncPrintedSignatories();
    });
    window.addEventListener("beforeprint", syncPrintedSignatories);
    syncPrintedSignatories();
});

window.addEventListener("load", () => {
    setTimeout(() => loader?.classList.add("is-hidden"), 350);
});

document.querySelectorAll(".tracked-form").forEach((form) => {
    const progress = form.querySelector(".progress-bar");
    const fields = Array.from(form.querySelectorAll("input, select, textarea"))
        .filter((field) => field.type !== "hidden" && field.type !== "checkbox");

    const updateProgress = () => {
        const completed = fields.filter((field) => field.value.trim() !== "").length;
        const width = fields.length ? Math.round((completed / fields.length) * 100) : 0;
        progress.style.width = `${width}%`;
        progress.textContent = width === 100 ? "Done" : `${width}%`;
    };

    form.addEventListener("input", updateProgress);
    form.addEventListener("change", updateProgress);
    updateProgress();
});

document.querySelectorAll("[data-delivery-total-cost]").forEach((output) => {
    const form = output.closest("form");
    const price = form?.querySelector("[data-delivery-price]");
    const netKilogram = form?.querySelector("[data-delivery-net-kg]");

    if (!price || !netKilogram) return;

    const updateTotalCost = () => {
        const priceValue = Number.parseFloat(price.value) || 0;
        const netKilogramValue = Number.parseFloat(netKilogram.value) || 0;
        const totalCost = Math.round((priceValue * netKilogramValue + Number.EPSILON) * 100) / 100;
        output.textContent = `Total Cost: ${totalCost.toLocaleString("en-PH", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`;
    };

    price.addEventListener("input", updateTotalCost);
    netKilogram.addEventListener("input", updateTotalCost);
    updateTotalCost();
});

const themeToggle = document.getElementById("themeToggle");
const savedTheme = localStorage.getItem("fwsp-theme");

if (savedTheme) {
    document.documentElement.setAttribute("data-bs-theme", savedTheme);
}

themeToggle?.addEventListener("click", () => {
    const nextTheme = document.documentElement.getAttribute("data-bs-theme") === "dark" ? "light" : "dark";
    document.documentElement.setAttribute("data-bs-theme", nextTheme);
    localStorage.setItem("fwsp-theme", nextTheme);
});

const desktopMenuHover = window.matchMedia("(min-width: 1200px) and (hover: hover)");
const mainMenuDropdowns = [...document.querySelectorAll(".app-nav .main-menu > .dropdown")];

mainMenuDropdowns.forEach((dropdown) => {
    const toggle = dropdown.querySelector(":scope > .dropdown-toggle");
    const menu = dropdown.querySelector(":scope > .dropdown-menu");
    let closeTimer = null;

    if (!toggle || !menu) return;

    const openMenu = () => {
        if (!desktopMenuHover.matches) return;
        window.clearTimeout(closeTimer);
        mainMenuDropdowns.forEach((otherDropdown) => {
            if (otherDropdown === dropdown) return;
            otherDropdown.classList.remove("is-hover-open");
            const otherToggle = otherDropdown.querySelector(":scope > .dropdown-toggle");
            const otherMenu = otherDropdown.querySelector(":scope > .dropdown-menu");
            if (!otherMenu?.classList.contains("show")) {
                otherToggle?.setAttribute("aria-expanded", "false");
            }
        });
        dropdown.classList.add("is-hover-open");
        toggle.setAttribute("aria-expanded", "true");
    };

    const scheduleClose = () => {
        if (!desktopMenuHover.matches) return;
        window.clearTimeout(closeTimer);
        closeTimer = window.setTimeout(() => {
            dropdown.classList.remove("is-hover-open");
            if (!menu.classList.contains("show")) {
                toggle.setAttribute("aria-expanded", "false");
            }
        }, 600);
    };

    dropdown.addEventListener("pointerenter", openMenu);
    dropdown.addEventListener("pointerleave", scheduleClose);
    desktopMenuHover.addEventListener("change", () => {
        window.clearTimeout(closeTimer);
        dropdown.classList.remove("is-hover-open");
    });
});

document.querySelectorAll("[data-password-toggle]").forEach((button) => {
    const group = button.closest(".input-group") || button.parentElement;
    const field = group?.querySelector("[data-password-field]");
    if (!field) return;

    button.addEventListener("click", () => {
        const isHidden = field.type === "password";
        field.type = isHidden ? "text" : "password";
        button.setAttribute("aria-label", isHidden ? "Hide password" : "Show password");
        button.title = isHidden ? "Hide password" : "Show password";
    });
});

const loginModal = document.getElementById("loginModal");
if (loginModal) {
    const rememberedUsername = localStorage.getItem("fwsp-remembered-username") || "";
    const usernameField = loginModal.querySelector("[data-remember-username]");
    const rememberField = loginModal.querySelector("[data-remember-login]");
    const credentialAlert = loginModal.querySelector("[data-login-credential-alert]");
    const countdown = credentialAlert?.querySelector("[data-login-error-countdown]");

    if (usernameField && rememberField && rememberedUsername !== "") {
        usernameField.value = rememberedUsername;
        rememberField.checked = true;
    }

    if (credentialAlert && countdown) {
        let remaining = 5;
        countdown.textContent = String(remaining);

        const timer = window.setInterval(() => {
            remaining -= 1;

            if (remaining <= 0) {
                window.clearInterval(timer);
                credentialAlert.classList.add("is-hidden");
                window.setTimeout(() => credentialAlert.remove(), 240);
                return;
            }

            countdown.textContent = String(remaining);
        }, 1000);
    }

    loginModal.querySelector("form")?.addEventListener("submit", () => {
        if (!usernameField || !rememberField) return;

        if (rememberField.checked) {
            localStorage.setItem("fwsp-remembered-username", usernameField.value.trim());
        } else {
            localStorage.removeItem("fwsp-remembered-username");
        }
    });
}

document.getElementById("forgotPasswordModal")?.addEventListener("show.bs.modal", () => {
    const loginUsername = loginModal?.querySelector("[data-remember-username]")?.value || "";
    const forgotUsername = document.querySelector("[data-forgot-username]");
    if (forgotUsername && forgotUsername.value.trim() === "") {
        forgotUsername.value = loginUsername.trim();
    }
});

const showRequestedAuthModal = () => {
    if (!window.bootstrap || !window.FWSP_AUTH_MODAL) return;

    if (window.FWSP_AUTH_MODAL.showChangePassword) {
        const modal = document.getElementById("changePasswordModal");
        if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
    } else if (window.FWSP_AUTH_MODAL.showRegister) {
        const modal = document.getElementById("registerModal");
        if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
    } else if (window.FWSP_AUTH_MODAL.showForgotPassword) {
        const modal = document.getElementById("forgotPasswordModal");
        if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
    } else if (window.FWSP_AUTH_MODAL.showLogin) {
        if (loginModal) bootstrap.Modal.getOrCreateInstance(loginModal).show();
    }
};

const flashMessageModal = document.querySelector("[data-flash-message-modal]");
if (flashMessageModal && window.bootstrap) {
    flashMessageModal.addEventListener("hidden.bs.modal", showRequestedAuthModal, { once: true });
    bootstrap.Modal.getOrCreateInstance(flashMessageModal).show();
} else {
    showRequestedAuthModal();
}

const confirmActionModal = document.querySelector("[data-confirm-action-modal]");
if (confirmActionModal && window.bootstrap) {
    const confirmModal = bootstrap.Modal.getOrCreateInstance(confirmActionModal);
    const message = confirmActionModal.querySelector("[data-confirm-message]");
    const title = confirmActionModal.querySelector("[data-confirm-title]");
    const accept = confirmActionModal.querySelector("[data-confirm-accept]");
    let pendingButton = null;

    const openConfirm = (button) => {
        pendingButton = button;
        if (title) title.textContent = button.dataset.confirmTitle || "Confirm Action";
        if (message) message.textContent = button.dataset.confirmMessage || "Are you sure?";
        if (accept) accept.textContent = button.dataset.confirmAccept || "Delete";
        confirmModal.show();
    };

    document.addEventListener("click", (event) => {
        const button = event.target.closest?.("button[data-confirm-message]");
        if (!button) return;

        if (button.dataset.confirmed === "true") {
            return;
        }

        event.preventDefault();
        openConfirm(button);
    }, true);

    document.addEventListener("submit", (event) => {
        const button = event.submitter;
        if (!button?.matches?.("button[data-confirm-message]")) return;

        if (button.dataset.confirmed === "true") {
            delete button.dataset.confirmed;
            return;
        }

        event.preventDefault();
        openConfirm(button);
    }, true);

    accept?.addEventListener("click", () => {
        if (!pendingButton) return;

        const button = pendingButton;
        pendingButton = null;
        button.dataset.confirmed = "true";
        confirmModal.hide();

        const form = button.form || button.closest("form");
        if (form?.requestSubmit) {
            form.requestSubmit(button);
        } else if (form) {
            form.submit();
        } else {
            button.click();
        }
    });

    confirmActionModal.addEventListener("hidden.bs.modal", () => {
        pendingButton = null;
    });
}

document.querySelectorAll("[data-notifications-clear-form]").forEach((form) => {
    form.addEventListener("submit", async (event) => {
        event.preventDefault();

        const button = form.querySelector("button");
        button.disabled = true;

        try {
            const response = await fetch("index.php", {
                method: "POST",
                headers: { "X-Requested-With": "fetch" },
                body: new FormData(form),
            });

            if (!response.ok) throw new Error("Notification clear failed");

            document.querySelectorAll("[data-notification-badge]").forEach((badge) => badge.remove());
            document.querySelectorAll(".notification-menu-head span").forEach((count) => count.remove());
            form.remove();

            document.querySelectorAll("[data-notification-list]").forEach((list) => {
                list.innerHTML = '<div class="notification-empty">No notifications yet.</div>';
            });
        } catch (error) {
            button.disabled = false;
            form.submit();
        }
    });
});

document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", (event) => {
        const target = document.querySelector(anchor.getAttribute("href"));
        if (!target) return;

        event.preventDefault();
        target.scrollIntoView({ behavior: "smooth", block: "start" });
    });
});

document.querySelectorAll("[data-toggle-other-input]").forEach((checkbox) => {
    const input = document.getElementById(checkbox.dataset.toggleOtherInput);
    if (!input) return;

    const sync = () => {
        input.disabled = !checkbox.checked;
        if (!checkbox.checked) input.value = "";
    };

    document.querySelectorAll(`[name="${checkbox.name}"]`).forEach((field) => {
        field.addEventListener("change", sync);
    });
    sync();
});

document.querySelectorAll(".rainbow-choice input").forEach((field) => {
    const syncGroup = () => {
        const group = field.closest(".rainbow-selection");
        const fields = group ? group.querySelectorAll(".rainbow-choice input") : [field];
        fields.forEach((item) => item.closest(".rainbow-choice")?.classList.toggle("is-selected", item.checked));
    };
    field.addEventListener("change", syncGroup);
    syncGroup();
});

document.querySelectorAll(".sdd-check-filter input").forEach((field) => {
    const sync = () => {
        field.closest(".sdd-check-filter")?.classList.toggle("is-selected", field.checked);
    };

    field.addEventListener("change", sync);
    sync();
});

document.querySelectorAll("[data-farmer-profile-form]").forEach((form) => {
    const fieldset = form.querySelector("[data-farmer-profile-fields]");
    const editButton = form.querySelector("[data-profile-edit-button]");
    const saveButton = form.querySelector("[data-profile-save-button]");

    if (!fieldset || !editButton || !saveButton) return;

    editButton.addEventListener("click", () => {
        fieldset.disabled = false;
        editButton.classList.add("d-none");
        saveButton.classList.remove("d-none");
        const firstField = fieldset.querySelector("input, select, textarea");
        firstField?.focus();
    });
});

document.querySelectorAll("[data-autocomplete-field]").forEach((field) => {
    const input = field.querySelector("[data-autocomplete-input]");
    const menu = field.querySelector("[data-autocomplete-menu]");
    const source = JSON.parse(input?.dataset.autocompleteSource || "[]").map((item) => String(item));

    if (!input || !menu || source.length === 0) return;

    const close = () => {
        menu.innerHTML = "";
        menu.hidden = true;
    };

    const choose = (value) => {
        input.value = value;
        input.dispatchEvent(new Event("input", { bubbles: true }));
        close();
    };

    const render = () => {
        const query = input.value.trim().toLowerCase();
        menu.innerHTML = "";

        if (query.length === 0) {
            close();
            return;
        }

        const matches = source
            .filter((item) => item.toLowerCase().includes(query))
            .slice(0, 8);

        if (matches.length === 0) {
            close();
            return;
        }

        matches.forEach((match) => {
            const button = document.createElement("button");
            button.type = "button";
            button.className = "autocomplete-option";
            button.textContent = match;
            button.addEventListener("mousedown", (event) => {
                event.preventDefault();
                choose(match);
            });
            menu.appendChild(button);
        });

        menu.hidden = false;
    };

    input.addEventListener("input", render);
    input.addEventListener("focus", render);
    input.addEventListener("blur", () => window.setTimeout(close, 120));
    close();
});

document.querySelectorAll("[data-fo-member-picker]").forEach((picker) => {
    const form = picker.closest("form");
    const foInput = form?.querySelector("[data-fo-name-input]");
    const membersInput = form?.querySelector('input[name="members"]');
    const search = picker.querySelector("[data-fo-member-search]");
    const options = Array.from(picker.querySelectorAll("[data-fo-member-option]"));
    const selectedList = picker.querySelector("[data-selected-member-list]");
    const hiddenInputs = picker.querySelector("[data-selected-member-inputs]");
    const submit = picker.querySelector("[data-fo-member-submit]");
    const empty = picker.querySelector("[data-fo-member-empty]");
    const selected = new Map();

    if (!selectedList || !hiddenInputs || !submit) return;

    const normalize = (value) => String(value || "").trim().toLowerCase();

    const optionMatchesOrganization = (option, organizationQuery) => {
        if (!organizationQuery) return true;

        const organization = normalize(option.dataset.memberOrganization);
        return organization !== "" && (organization.includes(organizationQuery) || organizationQuery.includes(organization));
    };

    const renderOptions = () => {
        const query = normalize(search?.value);
        const organizationQuery = normalize(foInput?.value);
        let visibleCount = 0;

        options.forEach((option) => {
            const matchesSearch = !query || normalize(option.dataset.memberSearch).includes(query);
            const matchesOrganization = optionMatchesOrganization(option, organizationQuery);
            const isVisible = matchesSearch && matchesOrganization;
            option.hidden = !isVisible;
            if (isVisible) visibleCount += 1;
        });

        if (empty) empty.hidden = visibleCount > 0;
    };

    const renderSelected = () => {
        selectedList.innerHTML = "";
        hiddenInputs.innerHTML = "";

        if (selected.size === 0) {
            const placeholder = document.createElement("span");
            placeholder.className = "text-muted";
            placeholder.textContent = "No farmer members selected yet.";
            selectedList.appendChild(placeholder);
            if (membersInput) {
                membersInput.value = "";
                membersInput.dispatchEvent(new Event("input", { bubbles: true }));
            }
            return;
        }

        selected.forEach((member, id) => {
            const pill = document.createElement("span");
            const remove = document.createElement("button");
            const hidden = document.createElement("input");
            const rsbsa = document.createElement("small");

            pill.className = "selected-member-pill";
            pill.append(document.createTextNode(member.name));

            rsbsa.textContent = member.rsbsa;
            pill.appendChild(rsbsa);

            remove.type = "button";
            remove.setAttribute("aria-label", `Remove ${member.name}`);
            remove.textContent = "x";
            remove.addEventListener("click", () => {
                selected.delete(id);
                const checkbox = picker.querySelector(`[data-member-id="${id}"] input`);
                if (checkbox) checkbox.checked = false;
                renderSelected();
            });
            pill.appendChild(remove);

            hidden.type = "hidden";
            hidden.name = "delivered_farmer_ids[]";
            hidden.value = id;
            hiddenInputs.appendChild(hidden);
            selectedList.appendChild(pill);
        });

        if (membersInput) {
            membersInput.value = selected.size;
            membersInput.dispatchEvent(new Event("input", { bubbles: true }));
        }
    };

    submit.addEventListener("click", () => {
        selected.clear();
        options.forEach((option) => {
            const checkbox = option.querySelector("input[type='checkbox']");
            if (!checkbox?.checked) return;

            selected.set(option.dataset.memberId, {
                name: option.dataset.memberName || "Unnamed Farmer",
                rsbsa: option.dataset.memberRsbsa || "",
            });
        });
        renderSelected();
    });

    search?.addEventListener("input", renderOptions);
    foInput?.addEventListener("input", renderOptions);
    renderOptions();
    renderSelected();
});

document.querySelectorAll("table").forEach((table) => {
    if (table.dataset.noSort === "true") return;

    const headers = Array.from(table.querySelectorAll("thead th"));
    const tbody = table.querySelector("tbody");

    if (!tbody || headers.length === 0) return;

    headers.forEach((header, index) => {
        if (header.textContent.trim().toLowerCase() === "actions" || header.textContent.trim().toLowerCase() === "action") return;

        header.classList.add("sortable-heading");
        header.tabIndex = 0;
        header.setAttribute("role", "button");

        const sort = () => {
            const rows = Array.from(tbody.querySelectorAll("tr"))
                .filter((row) => row.dataset.filterEmptyRow !== "true");
            const direction = header.dataset.sortDirection === "asc" ? "desc" : "asc";
            headers.forEach((item) => delete item.dataset.sortDirection);
            header.dataset.sortDirection = direction;

            rows.sort((left, right) => {
                const leftText = left.children[index]?.dataset.sortValue || left.children[index]?.textContent.trim() || "";
                const rightText = right.children[index]?.dataset.sortValue || right.children[index]?.textContent.trim() || "";
                const leftNumber = Number(leftText.replace(/,/g, ""));
                const rightNumber = Number(rightText.replace(/,/g, ""));
                const result = Number.isNaN(leftNumber) || Number.isNaN(rightNumber)
                    ? leftText.localeCompare(rightText)
                    : leftNumber - rightNumber;

                return direction === "asc" ? result : -result;
            });

            rows.forEach((row) => tbody.appendChild(row));
            table.dispatchEvent(new CustomEvent("table:changed"));
        };

        header.addEventListener("click", sort);
        header.addEventListener("keydown", (event) => {
            if (event.key === "Enter" || event.key === " ") {
                event.preventDefault();
                sort();
            }
        });
    });
});

document.querySelectorAll("[data-table-filter]").forEach((input) => {
    const table = document.getElementById(input.dataset.tableFilter);
    const tbody = table?.querySelector("tbody");
    if (!table || !tbody) return;

    const rows = Array.from(tbody.querySelectorAll("tr"));
    const emptyRow = document.createElement("tr");
    const emptyCell = document.createElement("td");

    emptyRow.hidden = true;
    emptyRow.dataset.filterEmptyRow = "true";
    emptyCell.colSpan = table.querySelectorAll("thead th").length || 1;
    emptyCell.className = "text-muted";
    emptyCell.textContent = "No matching records found.";
    emptyRow.appendChild(emptyCell);
    tbody.appendChild(emptyRow);

    const applyFilter = () => {
        const query = input.value.trim().toLowerCase();
        let visibleCount = 0;

        rows.forEach((row) => {
            const matches = !query || row.textContent.toLowerCase().includes(query);
            row.dataset.filterHidden = matches ? "false" : "true";
            row.hidden = !matches;
            if (matches) visibleCount += 1;
        });

        emptyRow.hidden = visibleCount > 0;
        table.dispatchEvent(new CustomEvent("table:changed"));
    };

    input.addEventListener("input", applyFilter);
    applyFilter();
});

document.querySelectorAll("table").forEach((table) => {
    const tbody = table.querySelector("tbody");
    const rowSelector = table.dataset.paginateRowSelector || "tr";
    const rows = tbody ? Array.from(tbody.querySelectorAll(rowSelector)).filter((row) => row.dataset.filterEmptyRow !== "true") : [];
    const pageSizes = (table.dataset.pageSizes || "10,20,30,40,50")
        .split(",")
        .map((size) => Number(size.trim()))
        .filter((size) => size > 0);
    const defaultPageSize = Number(table.dataset.pageSize || pageSizes[0] || 10);

    if (!tbody || rows.length <= defaultPageSize) return;

    const wrapper = table.closest(".table-responsive") || table.parentElement;
    const controls = document.createElement("div");
    const status = document.createElement("span");
    const sizeLabel = document.createElement("label");
    const sizeSelect = document.createElement("select");
    const pager = document.createElement("div");
    const previous = document.createElement("button");
    const next = document.createElement("button");

    let currentPage = 1;
    let pageSize = defaultPageSize;

    controls.className = "table-pagination no-print";
    status.className = "table-pagination-status";
    sizeLabel.className = "table-page-size";
    pager.className = "table-page-buttons";

    sizeLabel.textContent = "Rows";
    sizeSelect.className = "form-select form-select-sm";
    sizeSelect.setAttribute("aria-label", "Rows per page");
    pageSizes.forEach((size) => {
        const option = document.createElement("option");
        option.value = size;
        option.textContent = size;
        if (size === pageSize) option.selected = true;
        sizeSelect.appendChild(option);
    });
    sizeLabel.appendChild(sizeSelect);

    previous.className = "btn btn-sm btn-outline-success";
    previous.type = "button";
    previous.textContent = "Previous";
    next.className = "btn btn-sm btn-outline-success";
    next.type = "button";
    next.textContent = "Next";

    pager.append(previous, next);
    controls.append(status, sizeLabel, pager);
    wrapper.after(controls);

    const render = () => {
        const currentRows = Array.from(tbody.querySelectorAll(rowSelector))
            .filter((row) => row.dataset.filterEmptyRow !== "true");
        const visibleRows = currentRows.filter((row) => row.dataset.filterHidden !== "true");
        const totalRows = currentRows.length;
        const totalVisibleRows = visibleRows.length;
        const totalPages = Math.max(1, Math.ceil(totalVisibleRows / pageSize));
        currentPage = Math.min(currentPage, totalPages);

        const start = (currentPage - 1) * pageSize;
        const end = start + pageSize;

        currentRows.forEach((row) => {
            row.hidden = true;
        });

        visibleRows.forEach((row, index) => {
            const isHidden = index < start || index >= end;
            row.hidden = isHidden;
            if (rowSelector !== "tr" && row.nextElementSibling?.classList.contains("ticket-detail-row")) {
                row.nextElementSibling.hidden = isHidden;
                if (isHidden) {
                    row.nextElementSibling.querySelector(".collapse.show")?.classList.remove("show");
                }
            }
        });

        const firstShown = totalVisibleRows === 0 ? 0 : start + 1;
        const lastShown = Math.min(end, totalVisibleRows);
        status.textContent = `Showing ${firstShown}-${lastShown} of ${totalVisibleRows}`;
        previous.disabled = currentPage <= 1;
        next.disabled = currentPage >= totalPages;
    };

    sizeSelect.addEventListener("change", () => {
        pageSize = Number(sizeSelect.value);
        currentPage = 1;
        render();
    });

    previous.addEventListener("click", () => {
        currentPage = Math.max(1, currentPage - 1);
        render();
    });

    next.addEventListener("click", () => {
        currentPage += 1;
        render();
    });

    table.addEventListener("table:changed", () => {
        currentPage = 1;
        render();
    });

    render();
});

const addRepeatingPrintTitleRows = (root, title, subtitle = "") => {
    const tables = root.matches?.("table") ? [root] : Array.from(root.querySelectorAll("table"));

    tables.forEach((table) => {
        const thead = table.tHead || table.createTHead();
        if (thead.querySelector(".print-document-title-row")) return;

        const headerRow = thead.querySelector("tr");
        const columnCount = headerRow
            ? Array.from(headerRow.cells).reduce((total, cell) => total + Math.max(1, cell.colSpan || 1), 0)
            : 1;
        const sectionTitle = table.closest(".full-list-report-section")?.querySelector(":scope > h3")?.textContent.trim() || "";
        const titleRow = document.createElement("tr");
        const titleCell = document.createElement("th");
        const titleText = document.createElement("strong");

        titleRow.className = "print-document-title-row";
        titleCell.colSpan = columnCount;
        titleText.textContent = title;
        titleCell.appendChild(titleText);

        if (sectionTitle) {
            const sectionText = document.createElement("span");
            sectionText.textContent = sectionTitle;
            titleCell.appendChild(sectionText);
        }

        if (subtitle) {
            const subtitleText = document.createElement("small");
            subtitleText.textContent = subtitle;
            titleCell.appendChild(subtitleText);
        }

        titleRow.appendChild(titleCell);
        thead.prepend(titleRow);
    });
};

document.querySelectorAll(".report-sheet").forEach((sheet) => {
    const title = sheet.querySelector(".report-title h2")?.textContent.trim() || document.title;
    const subtitle = sheet.querySelector(".report-title p")?.textContent.trim() || "";
    addRepeatingPrintTitleRows(sheet, title, subtitle);
});

document.querySelectorAll("[data-print-target]").forEach((button) => {
    button.addEventListener("click", () => {
        const target = document.getElementById(button.dataset.printTarget);
        if (!target) return;

        const printable = target.cloneNode(true);
        printable.querySelectorAll("tr[hidden]").forEach((row) => {
            row.hidden = false;
        });
        printable.querySelectorAll(".print-exclude").forEach((element) => {
            element.remove();
        });
        printable.querySelectorAll("a").forEach((link) => {
            link.replaceWith(document.createTextNode(link.textContent));
        });
        const reportTitle = button.dataset.reportTitle || "Records Report";
        const isPdf = button.dataset.printMode === "pdf";
        const generatedLabel = `Generated ${new Date().toLocaleString()}`;
        addRepeatingPrintTitleRows(printable, reportTitle.toUpperCase(), generatedLabel);

        const preview = window.open("", "_blank", "width=1200,height=800");
        if (!preview) return;

        preview.document.write(`
            <!doctype html>
            <html>
            <head>
                <title>${reportTitle}</title>
                <style>
                    @page { size: legal landscape; margin: 0.4in 0.35in; }
                    * { box-sizing: border-box; }
                    body { font-family: Arial, sans-serif; padding: 18px; color: #222; }
                    h1 { font-size: 16px; text-align: center; margin: 0 0 4px; }
                    p { text-align: center; margin: 0 0 14px; font-size: 11px; }
                    table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 9.5px; line-height: 1.2; }
                    th, td { height: 0.24in; border: 1px solid #444; padding: 4px; text-align: left; white-space: normal; overflow-wrap: anywhere; }
                    th { background: #ffe94a; }
                    thead { display: table-header-group; }
                    .print-document-title-row { display: none; }
                    tfoot { display: table-row-group; }
                    tbody tr, tfoot tr { break-inside: avoid; page-break-inside: avoid; }
                    tfoot th, tfoot td { background: #eef8ef; font-weight: 700; }
                    .table-responsive { overflow: visible; }
                    .actions { margin: 18px 0; text-align: right; }
                    button { padding: 8px 12px; border: 1px solid #146b3a; background: #146b3a; color: white; border-radius: 6px; }
                    .table-location-cell { font-size: 8px; line-height: 1.25; }
                    @media print {
                        .actions { display: none; }
                        body { padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                        body > h1, body > .print-generated-at { display: none; }
                        .print-document-title-row { display: table-row; }
                        .print-document-title-row th { height: auto; padding: 0 3px 5px; border: 0; background: #fff; text-align: center; }
                        .print-document-title-row strong, .print-document-title-row span, .print-document-title-row small { display: block; }
                        .print-document-title-row strong { font-size: 14px; }
                        .print-document-title-row span, .print-document-title-row small { margin-top: 2px; font-size: 10px; }
                    }
                </style>
            </head>
            <body>
                <h1>${reportTitle.toUpperCase()}</h1>
                <p class="print-generated-at">${generatedLabel}</p>
                <div class="actions"><button onclick="window.print()">Print / Save PDF</button></div>
                ${printable.outerHTML}
            </body>
            </html>
        `);
        preview.document.close();
        preview.focus();
        if (isPdf) {
            preview.addEventListener("load", () => preview.print(), { once: true });
            window.setTimeout(() => preview.print(), 350);
        }
    });
});

const transactionDetailModal = document.getElementById("transactionDetailModal");
if (transactionDetailModal && window.bootstrap?.Modal) {
    const modal = new bootstrap.Modal(transactionDetailModal);
    modal.show();
    transactionDetailModal.addEventListener("hidden.bs.modal", () => {
        const url = new URL(window.location.href);
        url.searchParams.delete("transaction_id");
        window.history.replaceState({}, "", url.toString());
    });
}

const locationData = window.FWSP_LOCATIONS || {};
const locationGroups = new Set(
    Array.from(document.querySelectorAll("[data-location-level]")).map((select) => {
        return select.closest(".row") || select.closest("form") || document;
    })
);

const resetOptions = (select, label) => {
    const first = select.querySelector("option[value='']")?.textContent || label;
    select.innerHTML = `<option value="">${first}</option>`;
};

const addOptions = (select, items) => {
    const selected = select.dataset.selected || select.value;
    items.forEach((item) => {
        const option = document.createElement("option");
        option.value = item.id;
        option.textContent = item.name;
        if (String(item.id) === String(selected)) option.selected = true;
        select.appendChild(option);
    });
};

locationGroups.forEach((group) => {
    const region = group.querySelector('[data-location-level="region"]');
    const branch = group.querySelector('[data-location-level="branch"]');
    const province = group.querySelector('[data-location-level="province"]');
    const warehouse = group.querySelector('[data-location-level="warehouse"]');

    if (!region || !branch || !province || !warehouse) return;

    const renderBranches = () => {
        resetOptions(branch, "Select");
        resetOptions(province, "Select");
        resetOptions(warehouse, "Select");
        addOptions(branch, (locationData.branches || []).filter((item) => String(item.region_id) === String(region.value)));
        renderProvinces();
    };

    const renderProvinces = () => {
        resetOptions(province, "Select");
        resetOptions(warehouse, "Select");
        addOptions(province, (locationData.provinces || []).filter((item) => String(item.branch_id) === String(branch.value)));
        renderWarehouses();
    };

    const renderWarehouses = () => {
        resetOptions(warehouse, "Select");
        addOptions(warehouse, (locationData.warehouses || []).filter((item) => String(item.province_id) === String(province.value)));
    };

    region.addEventListener("change", () => {
        branch.dataset.selected = "";
        province.dataset.selected = "";
        warehouse.dataset.selected = "";
        renderBranches();
    });

    branch.addEventListener("change", () => {
        province.dataset.selected = "";
        warehouse.dataset.selected = "";
        renderProvinces();
    });

    province.addEventListener("change", () => {
        warehouse.dataset.selected = "";
        renderWarehouses();
    });

    group.querySelectorAll("[data-clear-location-filters]").forEach((button) => {
        button.addEventListener("click", () => {
            [region, branch, province, warehouse].forEach((select) => {
                select.dataset.selected = "";
                select.value = "";
            });
            renderBranches();
            [region, branch, province, warehouse].forEach((select) => {
                select.dispatchEvent(new Event("change", { bubbles: true }));
            });
        });
    });

    renderBranches();
});

document.querySelectorAll("[data-location-add-stack]").forEach((stack) => {
    const region = stack.querySelector('[data-location-add-level="region"]');
    const branch = stack.querySelector('[data-location-add-level="branch"]');
    const province = stack.querySelector('[data-location-add-level="province"]');

    const renderBranches = () => {
        if (!branch || !region) return;

        resetOptions(branch, "Select");
        if (province) resetOptions(province, "Select");
        addOptions(branch, (locationData.branches || []).filter((item) => String(item.region_id) === String(region.value)));
        renderProvinces();
    };

    const renderProvinces = () => {
        if (!province || !branch) return;

        resetOptions(province, "Select");
        addOptions(province, (locationData.provinces || []).filter((item) => String(item.branch_id) === String(branch.value)));
    };

    region?.addEventListener("change", () => {
        if (branch) branch.dataset.selected = "";
        if (province) province.dataset.selected = "";
        renderBranches();
    });

    branch?.addEventListener("change", () => {
        if (province) province.dataset.selected = "";
        renderProvinces();
    });

    stack.querySelector("[data-clear-location-add]")?.addEventListener("click", () => {
        [region, branch, province].forEach((select) => {
            if (!select) return;
            select.dataset.selected = "";
            select.value = "";
        });
        renderBranches();
    });

    renderBranches();
});

document.querySelectorAll("form").forEach((form) => {
    const scopeInput = form.querySelector("[data-registration-office-scope]");
    const tabs = Array.from(form.querySelectorAll("[data-registration-scope-tab]"));
    const panels = Array.from(form.querySelectorAll("[data-registration-scope-panel]"));

    if (!scopeInput || tabs.length === 0 || panels.length === 0) return;

    const setScope = (scope) => {
        scopeInput.value = scope;
        panels.forEach((panel) => {
            const isActive = panel.dataset.registrationScopePanel === scope;
            panel.querySelectorAll("input, select, textarea").forEach((field) => {
                field.disabled = !isActive;
            });
        });
    };

    tabs.forEach((tab) => {
        tab.addEventListener("shown.bs.tab", () => setScope(tab.dataset.registrationScopeTab));
        tab.addEventListener("click", () => setScope(tab.dataset.registrationScopeTab));
    });

    setScope(scopeInput.value || "field");
});

const centralOfficeData = window.FWSP_CENTRAL_OFFICE || {};
const centralOfficeGroups = new Set(
    Array.from(document.querySelectorAll("[data-central-office-level]")).map((select) => {
        return select.closest(".row") || select.closest("form") || document;
    })
);

centralOfficeGroups.forEach((group) => {
    const department = group.querySelector('[data-central-office-level="department"]');
    const division = group.querySelector('[data-central-office-level="division"]');
    const unit = group.querySelector('[data-central-office-level="unit"]');

    if (!department || !division || !unit) return;

    const renderDivisions = () => {
        resetOptions(division, "Select");
        resetOptions(unit, "Select");
        addOptions(division, (centralOfficeData.divisions || []).filter((item) => String(item.department_id) === String(department.value)));
        renderUnits();
    };

    const renderUnits = () => {
        resetOptions(unit, "Select");
        addOptions(unit, (centralOfficeData.units || []).filter((item) => String(item.division_id) === String(division.value)));
    };

    department.addEventListener("change", () => {
        division.dataset.selected = "";
        unit.dataset.selected = "";
        renderDivisions();
    });

    division.addEventListener("change", () => {
        unit.dataset.selected = "";
        renderUnits();
    });

    renderDivisions();
});

document.querySelectorAll("[data-central-office-add-stack]").forEach((stack) => {
    const department = stack.querySelector('[data-central-office-add-level="department"]');
    const division = stack.querySelector('[data-central-office-add-level="division"]');

    const renderDivisions = () => {
        if (!division || !department) return;

        resetOptions(division, "Select");
        addOptions(division, (centralOfficeData.divisions || []).filter((item) => String(item.department_id) === String(department.value)));
    };

    department?.addEventListener("change", () => {
        if (division) division.dataset.selected = "";
        renderDivisions();
    });

    stack.querySelector("[data-clear-central-office-add]")?.addEventListener("click", () => {
        [department, division].forEach((select) => {
            if (!select) return;
            select.dataset.selected = "";
            select.value = "";
        });
        renderDivisions();
    });

    renderDivisions();
});

document.querySelectorAll("[data-pie-chart]").forEach((canvas) => {
    const context = canvas.getContext("2d");
    if (!context) return;

    const palettes = {
        sex: ["#146b3a", "#d8a31e"],
        sectoral: ["#146b3a", "#5f8f2f", "#d8a31e", "#2f80b7", "#8a5a9f"],
        rainbow: ["#e53935", "#fb8c00", "#fdd835", "#43a047", "#1e88e5", "#8e24aa", "#d81b60"],
    };

    const piePath = (cx, cy, radius, start, end) => {
        context.beginPath();
        context.moveTo(cx, cy);
        context.arc(cx, cy, radius, start, end);
        context.closePath();
    };

    const roundedRect = (x, y, width, height, radius) => {
        context.beginPath();
        context.moveTo(x + radius, y);
        context.lineTo(x + width - radius, y);
        context.quadraticCurveTo(x + width, y, x + width, y + radius);
        context.lineTo(x + width, y + height - radius);
        context.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
        context.lineTo(x + radius, y + height);
        context.quadraticCurveTo(x, y + height, x, y + height - radius);
        context.lineTo(x, y + radius);
        context.quadraticCurveTo(x, y, x + radius, y);
        context.closePath();
    };

    const drawText = (text, x, y, options = {}) => {
        context.fillStyle = options.color || "#1d2b22";
        context.font = options.font || "600 12px Poppins, Arial, sans-serif";
        context.textAlign = options.align || "left";
        context.textBaseline = "middle";
        context.fillText(text, x, y);
    };

    let activeIndex = -1;
    let geometry = null;

    const draw = () => {
        const raw = JSON.parse(canvas.getAttribute("data-pie-chart") || "{}");
        const entries = Object.entries(raw).filter(([, value]) => Number(value) > 0);
        const ratio = window.devicePixelRatio || 1;
        const width = canvas.clientWidth || canvas.parentElement?.clientWidth || 360;
        const height = 320;
        const colors = palettes[canvas.dataset.palette] || palettes.sectoral;
        const total = entries.reduce((sum, [, value]) => sum + Number(value), 0);

        canvas.width = width * ratio;
        canvas.height = height * ratio;
        canvas.style.height = `${height}px`;
        context.setTransform(ratio, 0, 0, ratio, 0, 0);
        context.clearRect(0, 0, width, height);

        if (total === 0) {
            drawText("No data", width / 2, height / 2, {
                align: "center",
                font: "700 18px Poppins, Arial, sans-serif",
                color: "#5d6f64",
            });
            return;
        }

        const cx = width * 0.50;
        const cy = 108;
        const radius = Math.min(width * 0.30, 92);
        let angle = -Math.PI / 2;
        const slices = [];

        entries.forEach(([, value], index) => {
            const slice = (Number(value) / total) * Math.PI * 2;
            slices.push({
                start: angle,
                end: angle + slice,
                index,
                label: entries[index][0],
                value: Number(value),
                color: colors[index % colors.length],
            });
            angle += slice;
        });

        const drawSlice = (slice, isActive = false) => {
            const middle = (slice.start + slice.end) / 2;
            const offset = isActive ? 13 : 0;
            const sliceCx = cx + Math.cos(middle) * offset;
            const sliceCy = cy + Math.sin(middle) * offset;

            if (isActive) {
                context.save();
                context.shadowColor = "rgba(7, 59, 34, 0.26)";
                context.shadowBlur = 16;
                context.shadowOffsetY = 8;
            }

            piePath(sliceCx, sliceCy, radius, slice.start, slice.end);
            context.fillStyle = slice.color;
            context.fill();
            context.strokeStyle = "#ffffff";
            context.lineWidth = isActive ? 4 : 2;
            context.stroke();

            if (isActive) {
                context.restore();
            }
        };

        slices.forEach((slice) => {
            if (slice.index !== activeIndex) drawSlice(slice, false);
        });

        const activeSlice = slices.find((slice) => slice.index === activeIndex);
        if (activeSlice) {
            drawSlice(activeSlice, true);
        }

        geometry = { cx, cy, radius, slices };

        if (activeSlice) {
            const middle = (activeSlice.start + activeSlice.end) / 2;
            const percent = (activeSlice.value / total) * 100;
            const edgeX = cx + Math.cos(middle) * (radius + 18);
            const edgeY = cy + Math.sin(middle) * (radius + 18);
            const boxWidth = Math.min(170, width * 0.48);
            const boxHeight = 86;
            const rightSide = Math.cos(middle) >= 0;
            const boxX = rightSide
                ? Math.min(width - boxWidth - 8, edgeX + 12)
                : Math.max(8, edgeX - boxWidth - 12);
            const boxY = Math.max(12, Math.min(height - boxHeight - 12, edgeY - boxHeight / 2));
            const anchorX = rightSide ? boxX : boxX + boxWidth;

            context.strokeStyle = activeSlice.color;
            context.lineWidth = 2;
            context.beginPath();
            context.moveTo(edgeX, edgeY);
            context.lineTo(anchorX, boxY + boxHeight / 2);
            context.stroke();

            roundedRect(boxX, boxY, boxWidth, boxHeight, 10);
            context.fillStyle = "rgba(255, 255, 255, 0.96)";
            context.fill();
            context.strokeStyle = activeSlice.color;
            context.lineWidth = 2;
            context.stroke();

            drawText(activeSlice.label, boxX + 12, boxY + 18, {
                font: "800 12px Poppins, Arial, sans-serif",
                color: activeSlice.color,
            });
            drawText(`Count: ${activeSlice.value.toLocaleString()}`, boxX + 12, boxY + 38, {
                font: "700 11px Poppins, Arial, sans-serif",
            });
            drawText(`Share: ${percent.toFixed(2)}%`, boxX + 12, boxY + 56, {
                font: "700 11px Poppins, Arial, sans-serif",
            });
            drawText(`Total basis: ${total.toLocaleString()}`, boxX + 12, boxY + 74, {
                font: "600 10px Poppins, Arial, sans-serif",
                color: "#5d6f64",
            });
        }

        let legendY = cy + radius + 28;
        entries.forEach(([label, value], index) => {
            const numeric = Number(value);
            const percent = total > 0 ? (numeric / total) * 100 : 0;
            const x = width * 0.10;
            context.fillStyle = colors[index % colors.length];
            context.beginPath();
            context.arc(x, legendY, 5, 0, Math.PI * 2);
            context.fill();

            drawText(`${label}: ${numeric.toLocaleString()} (${percent.toFixed(2)}%)`, x + 12, legendY, {
                font: "700 11px Poppins, Arial, sans-serif",
                color: "#1d2b22",
            });
            legendY += 20;
        });
    };

    draw();
    window.addEventListener("resize", draw);
    canvas.addEventListener("mousemove", (event) => {
        if (!geometry) return;

        const rect = canvas.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;
        const dx = x - geometry.cx;
        const dy = y - geometry.cy;
        const distance = Math.sqrt(dx * dx + dy * dy);
        let pointerAngle = Math.atan2(dy, dx);
        if (pointerAngle < -Math.PI / 2) pointerAngle += Math.PI * 2;

        const nextIndex = distance <= geometry.radius
            ? geometry.slices.find((slice) => pointerAngle >= slice.start && pointerAngle < slice.end)?.index ?? -1
            : -1;

        if (nextIndex !== activeIndex) {
            activeIndex = nextIndex;
            canvas.style.cursor = activeIndex >= 0 ? "pointer" : "default";
            draw();
        }
    });
    canvas.addEventListener("mouseleave", () => {
        if (activeIndex === -1) return;
        activeIndex = -1;
        canvas.style.cursor = "default";
        draw();
    });
});

document.querySelectorAll(".activity-transition").forEach((link) => {
    link.addEventListener("click", (event) => {
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.button !== 0) return;

        event.preventDefault();

        if (!window.FWSP_IS_AUTHENTICATED) {
            const modal = document.getElementById("activityLoginRequiredModal");
            if (modal && window.bootstrap?.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(modal).show();
            }
            return;
        }

        document.body.classList.add("route-fade");
        window.setTimeout(() => {
            window.location.href = link.href;
        }, 360);
    });
});
