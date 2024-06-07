document.addEventListener('DOMContentLoaded', function () {
    const paymentStatusSelect = document.getElementById('paymentStatus');
    const paymentDateInput = document.getElementById('paymentDate').closest('div'); // Get the containing div

    console.log('dupa')

    function togglePaymentDate() {
        if (paymentStatusSelect.value === 'unpaid') {
            paymentDateInput.style.display = 'none';
        } else {
            paymentDateInput.style.display = 'block';
        }
    }

    paymentStatusSelect.addEventListener('change', togglePaymentDate);

    // Initialize the visibility based on the initial status
    togglePaymentDate();
});
