(function () {
    if (window.showModal && window.showConfirm) return;
    const html = `<div class="custom-modal-overlay" id="customModalOverlay" aria-hidden="true"><div class="custom-modal-box" role="dialog" aria-modal="true" aria-labelledby="customModalTitle" aria-describedby="customModalMessage"><div class="custom-modal-icon" id="customModalIcon"><i class="fa-solid fa-circle-info"></i></div><h3 class="custom-modal-title" id="customModalTitle">Notification</h3><p class="custom-modal-message" id="customModalMessage"></p><div class="custom-modal-actions"><button type="button" class="custom-modal-btn custom-modal-cancel" id="customModalCancel" hidden>Cancel</button><button type="button" class="custom-modal-btn" id="customModalBtn">OK</button></div></div></div>`;
    document.body.insertAdjacentHTML('beforeend', html);
    const overlay = document.getElementById('customModalOverlay');
    const icon = document.getElementById('customModalIcon');
    const title = document.getElementById('customModalTitle');
    const message = document.getElementById('customModalMessage');
    const okay = document.getElementById('customModalBtn');
    const cancel = document.getElementById('customModalCancel');
    const symbols = {success: 'fa-circle-check', error: 'fa-circle-xmark', warning: 'fa-triangle-exclamation', info: 'fa-circle-info'};
    const headings = {success: 'Success', error: 'Error', warning: 'Confirmation', info: 'Information'};
    let pending = null;
    let previousFocus = null;
    function complete(result) {
        if (!pending) return;
        const resolve = pending;
        pending = null;
        overlay.classList.remove('active');
        overlay.setAttribute('aria-hidden', 'true');
        if (previousFocus && previousFocus.isConnected) previousFocus.focus();
        resolve(result);
    }
    function open(text, type, confirmation) {
        if (pending) complete(false);
        previousFocus = document.activeElement;
        const kind = symbols[type] ? type : 'info';
        icon.className = 'custom-modal-icon ' + kind;
        icon.innerHTML = '<i class="fa-solid ' + symbols[kind] + '"></i>';
        title.textContent = confirmation ? 'Please Confirm' : headings[kind];
        message.textContent = String(text);
        cancel.hidden = !confirmation;
        okay.textContent = confirmation ? 'Confirm' : 'OK';
        okay.className = 'custom-modal-btn' + (kind === 'error' ? ' error-btn' : kind === 'warning' ? ' warning-btn' : kind === 'success' ? ' success-btn' : '');
        overlay.classList.add('active');
        overlay.setAttribute('aria-hidden', 'false');
        (confirmation ? cancel : okay).focus();
        return new Promise(resolve => { pending = resolve; });
    }
    window.showModal = (text, type = 'info') => open(text, type, false).then(() => undefined);
    window.showConfirm = (text) => open(text, 'warning', true);
    window.alert = (text) => {
        const lower = String(text).toLowerCase();
        const type = /error|failed|incorrect|cannot|invalid/.test(lower) ? 'error' : /success|added|updated|saved|deleted|restored/.test(lower) ? 'success' : 'info';
        return window.showModal(text, type);
    };
    okay.addEventListener('click', () => complete(true));
    cancel.addEventListener('click', () => complete(false));
    overlay.addEventListener('click', (event) => { if (event.target === overlay) complete(false); });
    document.addEventListener('keydown', (event) => {
        if (!pending) return;
        if (event.key === 'Escape') { event.preventDefault(); complete(false); }
        if (event.key === 'Tab') {
            const elements = cancel.hidden ? [okay] : [cancel, okay];
            const first = elements[0];
            const last = elements[elements.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
    });
    const approved = new WeakSet();
    document.addEventListener('submit', async event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.dataset.confirm) return;
        if (approved.has(form)) { approved.delete(form); return; }
        event.preventDefault();
        event.stopPropagation();
        const submitter = event.submitter || undefined;
        if (form.id === 'removeAccountsForm' || form.id === 'removeAdministratorsForm') {
            if (![...document.querySelectorAll(`input[form="${form.id}"][name="ids[]"]`)].some(box => box.checked)) {
                await window.showModal('Please select at least one account first.', 'warning');
                return;
            }
        }
        if (await window.showConfirm(form.dataset.confirm)) {
            approved.add(form);
            if (typeof form.requestSubmit === 'function') form.requestSubmit(submitter);
            else HTMLFormElement.prototype.submit.call(form);
        }
    }, true);
    document.addEventListener('DOMContentLoaded', () => {
        const errors = document.querySelector('[data-flash-error]');
        const success = document.querySelector('[data-flash-success]');
        if (errors) window.showModal(errors.textContent.trim(), 'error');
        else if (success) window.showModal(success.textContent.trim(), 'success');
    });
})();
