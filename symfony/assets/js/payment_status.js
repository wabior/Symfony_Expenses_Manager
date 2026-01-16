const initPaymentStatus = () => {
    const paymentStatusSelect = document.getElementById('paymentStatus');
    const paymentDateInput = document.getElementById('paymentDate');

    // Only proceed if both elements exist (only in add form, not edit)
    if (!paymentStatusSelect || !paymentDateInput) {
        return;
    }

    const paymentDateContainer = paymentDateInput.closest('div');

    const togglePaymentDate = () => {
        if (paymentStatusSelect.value === 'unpaid') {
            paymentDateContainer.style.display = 'none';
        } else {
            paymentDateContainer.style.display = 'block';
        }
    };

    paymentStatusSelect.addEventListener('change', togglePaymentDate);

    // Initialize the visibility based on the initial status
    togglePaymentDate();
};

document.addEventListener('DOMContentLoaded', initPaymentStatus);
