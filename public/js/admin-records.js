document.addEventListener('DOMContentLoaded', () => {
    const bar = document.querySelector('[data-record-type]');
    if (!bar) return;
    const type = bar.dataset.recordType;
    const boxes = [...document.querySelectorAll('.record-check')];
    const selectAll = document.querySelector('.records-select-all');
    const button = bar.querySelector('[data-bulk-delete]');
    const count = bar.querySelector('[data-selected-count]');
    const statusButtons = [...bar.querySelectorAll('[data-bulk-status]')];
    const update = () => {
        const n = boxes.filter(b => b.checked).length;
        count.textContent = n;
        button.disabled = !n;
        statusButtons.forEach(b => b.disabled = !n);
        if (selectAll) {
            selectAll.checked = n === boxes.length && n > 0;
            selectAll.indeterminate = n > 0 && n < boxes.length;
        }
    };
    boxes.forEach(box => box.addEventListener('change', update));
    selectAll?.addEventListener('change', () => {
        boxes.forEach(box => {
            if (box.closest('tr')?.style.display !== 'none') box.checked = selectAll.checked;
        });
        update();
    });
    statusButtons.forEach(control => control.addEventListener('click', async () => {
        const ids = boxes.filter(box => box.checked).map(box => Number(box.value));
        const status = control.dataset.bulkStatus;
        if (!ids.length) return;
        if (!await window.showConfirm(`Set ${ids.length} selected ${type} to ${status}? This will also update their login access.`)) return;
        statusButtons.forEach(b => b.disabled = true);
        button.disabled = true;
        try {
            const response = await fetch(`/admin/${type}/bulk-status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({ids, status}),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || Object.values(result.errors || {}).flat().join('\n') || 'Status update failed.');
            alert(result.message);
            window.location.reload();
        } catch (error) {
            alert(error.message);
            update();
        }
    }));
    button.addEventListener('click', async () => {
        const ids = boxes.filter(box => box.checked).map(box => Number(box.value));
        if (!ids.length) return;
        if (!await window.showConfirm(`Move ${ids.length} selected ${type} to Recycle Bin? Their linked login accounts will also be disabled. You can restore them later.`)) return;
        button.disabled = true;
        try {
            const response = await fetch(`/admin/${type}/bulk`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({ids}),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || Object.values(result.errors || {}).flat().join('\n') || 'Deletion failed.');
            alert(result.message);
            window.location.reload();
        } catch (error) {
            alert(error.message);
            update();
        }
    });
});
