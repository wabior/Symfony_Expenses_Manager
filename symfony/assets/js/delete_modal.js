let deleteExpenseId = null;
let deleteExpenseData = null;
let previousFocusElement = null;

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
    const lastOccurrenceWarning = document.querySelector('#deleteModal .last-occurrence-warning');

    if (expenseNameEl) {
        expenseNameEl.textContent = deleteExpenseData.name;
    }
    if (amountEl) {
        amountEl.textContent = `Kwota: ${deleteExpenseData.amount} zł`;
    }
    if (dateEl) {
        dateEl.textContent = `Data: ${deleteExpenseData.date}`;
    }

    if (recurringWarning) {
        if (deleteExpenseData.isRecurring === 'true') {
            recurringWarning.classList.remove('hidden');
        } else {
            recurringWarning.classList.add('hidden');
        }
    }

    if (lastOccurrenceWarning) {
        if (deleteExpenseData.isLastOccurrence === 'true') {
            lastOccurrenceWarning.classList.remove('hidden');
        } else {
            lastOccurrenceWarning.classList.add('hidden');
        }
    }
}

// Global functions for onclick handlers
window.showDeleteConfirm = function (occurrenceId, expenseName, amount, date, isRecurring, isLastOccurrence) {
    // Store previous focus for accessibility
    previousFocusElement = document.activeElement;

    deleteExpenseId = occurrenceId;
    deleteExpenseData = {
        name: expenseName,
        amount,
        date,
        isRecurring,
        isLastOccurrence
    };

    // Update modal content dynamically
    updateModalContent();

    // Show modal with animation
    const modal = document.getElementById('deleteModal');
    const modalContent = modal.querySelector('.modal-content');

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    // Trigger animation
    setTimeout(() => {
        modalContent.classList.remove('scale-95');
        modalContent.classList.add('scale-100');
    }, 10);

    // Focus management - move focus to modal
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    if (confirmBtn) {
        confirmBtn.focus();
    }

    // Prevent body scroll
    document.body.style.overflow = 'hidden';
};

window.hideDeleteConfirm = function () {
    const modal = document.getElementById('deleteModal');
    const modalContent = modal.querySelector('.modal-content');

    // Animation before hiding
    modalContent.classList.remove('scale-100');
    modalContent.classList.add('scale-95');

    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');

        // Restore body scroll
        document.body.style.overflow = '';

        // Restore focus
        if (previousFocusElement) {
            previousFocusElement.focus();
        }
    }, 150);

    deleteExpenseId = null;
    deleteExpenseData = null;
};

window.confirmDelete = function () {
    if (!deleteExpenseId) {
        return;
    }

    // Show loading state
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = `
            <span class="flex items-center">
                <svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Usuwanie...
            </span>
        `;
    }

    // Create and submit form with CSRF token
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/expenses/delete/${deleteExpenseId}`;
    form.style.display = 'none';

    // Add CSRF token
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta && csrfMeta.content) {
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = csrfMeta.content;
        form.appendChild(tokenInput);
    }

    document.body.appendChild(form);
    form.submit();
};

// Keyboard navigation and accessibility
document.addEventListener('DOMContentLoaded', function () {
    // Handle Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('deleteModal');
            if (!modal.classList.contains('hidden')) {
                window.hideDeleteConfirm();
            }
        }
    });

    // Handle modal click outside
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === this) {
                window.hideDeleteConfirm();
            }
        });

        // Focus trap for accessibility
        modal.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                const focusableElements = modal.querySelectorAll(
                    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                );
                const firstElement = focusableElements[0];
                const lastElement = focusableElements[focusableElements.length - 1];

                if (e.shiftKey) {
                    // Shift + Tab
                    if (document.activeElement === firstElement) {
                        e.preventDefault();
                        lastElement.focus();
                    }
                } else {
                    // Tab
                    if (document.activeElement === lastElement) {
                        e.preventDefault();
                        firstElement.focus();
                    }
                }
            }
        });
    }

    // Handle Enter key on confirm button (redundant but good UX)
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !this.disabled) {
                window.confirmDelete();
            }
        });
    }
});

// Export functions for potential module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        updateModalContent
    };
}
