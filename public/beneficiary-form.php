<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beneficiary Bank Details</title>
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
        #formCard {
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
        .file-input-label {
            transition: all 0.2s ease-out;
        }
        .file-input-label:hover {
            border-color: #8B5CF6;
            color: #8B5CF6;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-100 to-purple-200 flex items-center justify-center min-h-screen py-12 px-4">
    <div id="formCard" class="w-full max-w-2xl p-8 space-y-6 bg-white/90 backdrop-blur-sm rounded-2xl shadow-2xl">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900">Beneficiary Details</h1>
            <p class="mt-2 text-gray-600">Payment successful. Please complete the form below.</p>
        </div>

        <form id="beneficiaryForm" action="/api/submit-details.php" method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="fullName" class="block text-sm font-medium text-gray-700">Full Name (as on ID)</label>
                    <input type="text" name="fullName" id="fullName" class="mt-1 block w-full rounded-md border-gray-300 py-3 px-3 shadow-sm focus:border-purple-500 focus:ring-purple-500" required>
                </div>
                <div>
                    <label for="dob" class="block text-sm font-medium text-gray-700">Date of Birth</label>
                    <input type="date" name="dob" id="dob" class="mt-1 block w-full rounded-md border-gray-300 py-3 px-3 shadow-sm focus:border-purple-500 focus:ring-purple-500" required>
                </div>
                <div>
                    <label for="contact" class="block text-sm font-medium text-gray-700">Contact Number</label>
                    <input type="tel" name="contact" id="contact" class="mt-1 block w-full rounded-md border-gray-300 py-3 px-3 shadow-sm focus:border-purple-500 focus:ring-purple-500" required>
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 py-3 px-3 shadow-sm focus:border-purple-500 focus:ring-purple-500" required>
                </div>
                <div>
                    <label for="bankName" class="block text-sm font-medium text-gray-700">Bank Name</label>
                    <input type="text" name="bankName" id="bankName" class="mt-1 block w-full rounded-md border-gray-300 py-3 px-3 shadow-sm focus:border-purple-500 focus:ring-purple-500" required>
                </div>
                <div>
                    <label for="branch" class="block text-sm font-medium text-gray-700">Branch (If applicable)</label>
                    <input type="text" name="branch" id="branch" class="mt-1 block w-full rounded-md border-gray-300 py-3 px-3 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                </div>
                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700">Country</label>
                    <input type="text" name="country" id="country" class="mt-1 block w-full rounded-md border-gray-300 py-3 px-3 shadow-sm focus:border-purple-500 focus:ring-purple-500" required>
                </div>
                <div>
                    <label for="currency" class="block text-sm font-medium text-gray-700">Currency</label>
                    <input type="text" name="currency" id="currency" class="mt-1 block w-full rounded-md border-gray-300 py-3 px-3 shadow-sm focus:border-purple-500 focus:ring-purple-500" required>
                </div>
                <div class="md:col-span-2">
                    <label for="iban" class="block text-sm font-medium text-gray-700">IBAN</label>
                    <input type="text" name="iban" id="iban" class="mt-1 block w-full rounded-md border-gray-300 py-3 px-3 shadow-sm focus:border-purple-500 focus:ring-purple-500" required>
                </div>
                <div class="md:col-span-2">
                    <label for="remarks" class="block text-sm font-medium text-gray-700">Remarks / Message</label>
                    <textarea name="remarks" id="remarks" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Upload ID</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="idUpload" class="file-input-label relative cursor-pointer rounded-md font-medium text-purple-600 hover:text-purple-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-purple-500 focus-within:ring-offset-2">
                                    <span>Upload a file</span>
                                    <input id="idUpload" name="idUpload" type="file" class="sr-only" required>
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p id="fileName" class="text-xs text-gray-500">PNG, JPG, PDF up to 10MB</p>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <button type="submit" id="submitButton" class="flex w-full justify-center rounded-md border border-transparent bg-gradient-to-r from-blue-600 to-purple-600 py-3 px-4 text-sm font-medium text-white shadow-sm hover:from-blue-700 hover:to-purple-700 hover:scale-105 transform transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                    Submit Details
                </button>
            </div>
            <div id="loader" class="hidden flex justify-center items-center">
                <div class="loader"></div>
                <p class="ml-4 text-gray-600">Submitting your details...</p>
            </div>
            <div id="errorMessage" class="hidden p-4 rounded-md bg-red-50 border border-red-200">
                <p class="text-sm font-medium text-red-800"><strong>Error:</strong> <span id="errorText"></span></p>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const beneficiaryForm = document.getElementById('beneficiaryForm');
            const submitButton = document.getElementById('submitButton');
            const loader = document.getElementById('loader');
            const errorMessage = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');
            const idUpload = document.getElementById('idUpload');
            const fileName = document.getElementById('fileName');

            idUpload.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    fileName.textContent = e.target.files[0].name;
                    fileName.classList.remove('text-gray-500');
                    fileName.classList.add('text-purple-700', 'font-medium');
                } else {
                    fileName.textContent = 'PNG, JPG, PDF up to 10MB';
                    fileName.classList.add('text-gray-500');
                    fileName.classList.remove('text-purple-700', 'font-medium');
                }
            });

            beneficiaryForm.addEventListener('submit', (e) => {
                hideError();
                loader.classList.remove('hidden');
                submitButton.disabled = true;
                submitButton.textContent = 'Submitting...';
                if (idUpload.files.length === 0) {
                    e.preventDefault();
                    showError('Please upload your ID document.');
                    resetButton();
                }
            });

            function showError(message) {
                errorText.textContent = message;
                errorMessage.classList.remove('hidden');
                errorMessage.classList.add('shake-error');
                setTimeout(() => {
                    errorMessage.classList.remove('shake-error');
                }, 300);
            }

            function hideError() {
                errorMessage.classList.add('hidden');
            }

            function resetButton() {
                loader.classList.add('hidden');
                submitButton.disabled = false;
                submitButton.textContent = 'Submit Details';
            }
        });
    </script>
</body>
</html>