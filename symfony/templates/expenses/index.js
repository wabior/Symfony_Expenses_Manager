// Obsługa zmiany statusu płatności dla wystąpień i wydatków
document.querySelectorAll('.status-cell').forEach(cell => {
    const itemId = cell.getAttribute('data-id');
    const itemType = cell.getAttribute('data-type'); // 'occurrence' lub 'expense'
    const statusText = cell.querySelector('.status-text');
    const statusSelect = cell.querySelector('.status-select');

    // Kliknięcie w komórkę pokazuje select
    cell.addEventListener('click', (e) => {
        e.stopPropagation();
        statusText.classList.add('hidden');
        statusSelect.classList.remove('hidden');
        statusSelect.focus();
    });

    // Zmiana wartości w select
    statusSelect.addEventListener('change', async () => {
        const newStatus = statusSelect.value;

        try {
            // Wybierz odpowiedni endpoint
            const endpoint = itemType === 'occurrence'
                ? `/expenses/occurrence/${itemId}/status`
                : `/expenses/update-status/${itemId}`;

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({status: newStatus})
            });

            const data = await response.json();

            if (data.success) {
                // Aktualizuj wyświetlany tekst
                const displayText = newStatus.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                statusText.textContent = displayText;

                // Aktualizuj klasy CSS dla koloru
                statusText.className = `status-text hover:text-shadow ${
                    newStatus === 'paid' ? 'text-green-600' :
                    newStatus === 'unpaid' ? 'text-red-500' :
                    'text-yellow-600'
                }`;

                // Ukryj select, pokaż tekst
                statusSelect.classList.add('hidden');
                statusText.classList.remove('hidden');
            } else {
                alert('Błąd podczas aktualizacji statusu: ' + (data.error || 'Nieznany błąd'));
            }
        } catch (error) {
            alert('Błąd połączenia: ' + error.message);
        }
    });

    // Kliknięcie poza select ukrywa go
    document.addEventListener('click', (e) => {
        if (!cell.contains(e.target)) {
            statusSelect.classList.add('hidden');
            statusText.classList.remove('hidden');
        }
    });
});

// Dodaj obsługę wskaźników cyklicznych
document.querySelectorAll('.recurring-indicator').forEach(indicator => {
    indicator.addEventListener('click', (e) => {
        e.stopPropagation();
        const frequency = indicator.textContent.replace('🔄', '');
        const frequencyText = frequency == 1 ? 'miesiąc' : 'miesięcy';
        alert(`To jest wydatek cykliczny - powtarza się co ${frequency} ${frequencyText}.`);
    });
});
