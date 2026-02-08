let deleteExpenseId = null;
let deleteExpenseData = null;

// Function to update modal content dynamically
function updateModalContent() {
    if (!deleteExpenseData) {
        return;
    }

    // Update expense details in modal
    const expenseNameEl = document.querySelector('#deleteModal .expense-name');
    const amountEl = document.querySelector('#deleteModal .expense-amount');
    const dateEl = document.querySelector('#deleteModal .expense-date');
    const recurringWarning = document.querySelector('#deleteModal .recurring-warning');

    if (expenseNameEl) {
        expenseNameEl.textContent = deleteExpenseData.name;
    }
    if (amountEl) {
        amountEl.textContent = deleteExpenseData.amount + ' zł';
    }
    if (dateEl) {
        dateEl.textContent = deleteExpenseData.date;
    }

    if (recurringWarning) {
        if (deleteExpenseData.isRecurring) {
            recurringWarning.style.display = 'block';
        } else {
            recurringWarning.style.display = 'none';
        }
    }
}

// Global functions for onclick handlers
window.showDeleteConfirm = function (occurrenceId, expenseName, amount, date, isRecurring) {
    deleteExpenseId = occurrenceId;
    deleteExpenseData = {
        name: expenseName,
        amount,
        date,
        isRecurring: isRecurring === 'true'
    };

    // Update modal content dynamically
    updateModalContent();

    const modal = document.getElementById('deleteModal');
    modal.style.display = 'flex';
};

window.hideDeleteConfirm = function () {
    const modal = document.getElementById('deleteModal');
    modal.style.display = 'none';
    deleteExpenseId = null;
    deleteExpenseData = null;
};

window.confirmDelete = function () {
    if (deleteExpenseId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/expenses/delete/' + deleteExpenseId;

        // Add CSRF token
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
        form.appendChild(tokenInput);

        document.body.appendChild(form);
        form.submit();
    }
};

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === this) {
                window.hideDeleteConfirm();
            }
        });
    }
});
