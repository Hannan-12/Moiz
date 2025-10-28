<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Payment</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #8B5CF6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes fadeInSlideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        #paymentCard {
            animation: fadeInSlideUp 0.7s ease-out forwards;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            50% { transform: translateX(5px); }
            75% { transform: translateX(-5px); }
        }
        .shake-error {
            animation: shake 0.3s ease-in-out;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-100 to-purple-200 flex items-center justify-center min-h-screen p-4">
    <div id="paymentCard" class="w-full max-w-lg p-8 space-y-6 bg-white/90 backdrop-blur-sm rounded-2xl shadow-2xl">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900">Create Your Payment</h1>
            <p class="mt-2 text-gray-600">Enter your amount in USD and select a cryptocurrency to pay with.</p>
        </div>

        <form id="paymentForm" class="space-y-6">
            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700">Amount (USD)</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 pl-3 flex items-center">
                        <span class="text-gray-500 sm:text-sm">$</span>
                    </div>
                    <input type="number" name="amount" id="amount" class="block w-full rounded-md border-gray-300 pl-7 pr-12 py-3 focus:border-purple-500 focus:ring-purple-500" placeholder="0.00" step="0.01" min="1" required>
                    <div class="pointer-events-none absolute inset-y-0 right-0 pr-3 flex items-center">
                        <span class="text-gray-500 sm:text-sm" id="basic-addon2">USD</span>
                    </div>
                </div>
            </div>

            <div>
                <label for="currency" class="block text-sm font-medium text-gray-700">Pay With</label>
                <select id="currency" name="currency" class="mt-1 block w-full rounded-md border-gray-300 py-3 px-3 shadow-sm focus:border-purple-500 focus:ring-purple-500" required>
                    <option value="" disabled selected>Loading currencies...</option>
                </select>
            </div>

            <div>
                <button type="submit" id="submitButton" class="flex w-full justify-center rounded-md border border-transparent bg-gradient-to-r from-blue-600 to-purple-600 py-3 px-4 text-sm font-medium text-white shadow-sm hover:from-blue-700 hover:to-purple-700 hover:scale-105 transform transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                    Proceed to Payment
                </button>
            </div>

            <div id="loader" class="hidden flex justify-center items-center">
                <div class="loader"></div>
                <p class="ml-4 text-gray-600">Redirecting to payment...</p>
            </div>

            <div id="errorMessage" class="hidden p-4 rounded-md bg-red-50 border border-red-200">
                <p class="text-sm font-medium text-red-800"><strong>Error:</strong> <span id="errorText"></span></p>
            </div>
        </form>
    </div>

   <script>
    document.addEventListener('DOMContentLoaded', () => {
        const currencySelect = document.getElementById('currency');
        const paymentForm = document.getElementById('paymentForm');
        const submitButton = document.getElementById('submitButton');
        const loader = document.getElementById('loader');
        const errorMessage = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');

        async function fetchCurrencies() {
            try {
                // ✅ Changed: Path points to the new PHP API file
                const response = await fetch('/api/currencies.php'); // Relative path

                if (!response.ok) {
                    const errorData = await response.json(); // Try parsing error from API
                    console.error("API Response Error:", errorData);
                    throw new Error(errorData.message || `Failed to load currencies. Status: ${response.status}`);
                }

                const data = await response.json();
                currencySelect.innerHTML = '<option value="" disabled selected>Select a currency</option>';

                // Ensure 'data.currencies' exists and is an array
                if (data && Array.isArray(data.currencies)) {
                     data.currencies.forEach(currency => {
                        const option = document.createElement('option');
                        option.value = currency;
                        option.textContent = currency.toUpperCase();
                        currencySelect.appendChild(option);
                    });
                } else {
                     console.error('Unexpected data format from /api/currencies.php:', data);
                     throw new Error('Unexpected data format for currencies.');
                }
            } catch (error) {
                console.error('Error fetching currencies:', error);
                currencySelect.innerHTML = '<option value="" disabled selected>Failed to load currencies</option>';
                showError(error.message || 'Could not load cryptocurrency list. Please try refreshing the page.');
            }
        }

        paymentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            hideError();
            loader.classList.remove('hidden');
            submitButton.disabled = true;
            submitButton.textContent = 'Processing...';

            const amount = document.getElementById('amount').value;
            const currency = currencySelect.value;

            if (!amount || !currency) {
                showError('Please enter an amount and select a currency.');
                resetButton();
                return;
            }

            try {
                // ✅ Changed: Path points to the new PHP API file
                const serverUrl = '/api/create-payment.php'; // Relative path
                const response = await fetch(serverUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        price_amount: parseFloat(amount),
                        pay_currency: currency
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    // Use the message from the backend's JSON response
                    throw new Error(data.message || 'Failed to create payment.');
                }

                // Redirect to NOWPayments invoice URL
                window.location.href = data.invoiceUrl;

            } catch (error) {
                console.error('Form submission error:', error);
                showError(error.message || 'An unknown error occurred while creating the payment.');
                resetButton(); // Only reset if redirect doesn't happen
            }
        });

        function showError(message) {
            errorText.textContent = message;
            errorMessage.classList.remove('hidden');
            // Optional: Add shake animation class
            errorMessage.classList.add('shake-error');
            setTimeout(() => errorMessage.classList.remove('shake-error'), 300);
        }

        function hideError() {
            errorMessage.classList.add('hidden');
            errorText.textContent = '';
        }

        function resetButton() {
            loader.classList.add('hidden');
            submitButton.disabled = false;
            submitButton.textContent = 'Proceed to Payment';
        }

        // Fetch currencies when the page loads
        fetchCurrencies();
    });
</script>
</body>
</html>