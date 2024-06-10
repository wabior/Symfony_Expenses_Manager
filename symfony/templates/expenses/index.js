document.querySelectorAll('.status-cell').forEach(cell => {
    const statusText = cell.querySelector('.status-text');
    const statusSelect = cell.querySelector('.status-select');

    cell.addEventListener('click', function() {
        statusText.classList.add('hidden');
        statusSelect.classList.remove('hidden');
        statusSelect.focus();
    });

    statusSelect.addEventListener('change', function() {
        const expenseId = cell.getAttribute('data-id');
        const newStatus = statusSelect.value;
        const row = document.getElementById(`expense-${expenseId}`);
        const paymentDateCell = row.querySelector('[data-key="paymentDate"]');

        fetch(`/expenses/update-status/${expenseId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')            },
            body: JSON.stringify({ status: newStatus })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusText.innerText = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                    statusText.dataset.status = newStatus;
                    statusSelect.classList.add('hidden');
                    statusText.classList.remove('hidden');
                    paymentDateCell.innerText = data.paymentDate || 'N/A';
                } else {
                    alert('Failed to update status.');
                }
            });
    });

    statusSelect.addEventListener('blur', function() {
        statusSelect.classList.add('hidden');
        statusText.classList.remove('hidden');
    });
});
