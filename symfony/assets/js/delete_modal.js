let deleteExpenseId = null;

// Global functions for onclick handlers
window.showDeleteConfirm = function (expenseId) {
    deleteExpenseId = expenseId;
    const modal = document.getElementById('deleteModal');
    modal.style.display = 'flex';
};

window.hideDeleteConfirm = function () {
    const modal = document.getElementById('deleteModal');
    modal.style.display = 'none';
    deleteExpenseId = null;
};

window.confirmDelete = function () {
    if (deleteExpenseId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/expenses/delete/' + deleteExpenseId;
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
