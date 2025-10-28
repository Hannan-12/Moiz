// server.js (Updated for Vercel with HARDCODED values - NOT RECOMMENDED)
const express = require('express');
const fetch = require('node-fetch'); // Use node-fetch v2 as installed
const nodemailer = require('nodemailer');
const multer = require('multer');
const path = require('path');

// --- 🚨 HARDCODED SENSITIVE VALUES - VERY RISKY! ---
const NOWPAYMENTS_PRIVATE_KEY = '06PYHFE-FM6MH8Z-M5PYY98-EWPN3MJ'; // Hardcoded API Key
const EMAIL_USER = 'apaynet@aol.com'; // Hardcoded Email User
const EMAIL_PASS = 'YOUR_AOL_APP_PASSWORD_GOES_HERE'; // Hardcoded AOL App Password
const YOUR_DOMAIN = 'https://www.apaymentsnetwork.com'; // Hardcoded Domain
// --- End of Hardcoded Values ---

const app = express();
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Serve Static Files (Vercel uses vercel.json, this helps locally)
app.use(express.static(path.join(__dirname, 'public')));

const upload = multer({ storage: multer.memoryStorage() });

// --- API Routes ---

// Route to fetch currencies server-side
app.get('/api/currencies', async (req, res) => {
    try {
        const apiUrl = 'https://api.nowpayments.io/v1/currencies';
        const response = await fetch(apiUrl);
        if (!response.ok) {
            const errorText = await response.text();
            console.error('NOWPayments Currencies Error Status:', response.status);
            console.error('NOWPayments Currencies Error Body:', errorText);
            throw new Error(`Failed to fetch currencies: ${response.statusText}`);
        }
        const data = await response.json();
        res.json(data);
    } catch (error) {
        console.error('Error fetching currencies:', error);
        res.status(500).json({ message: 'Failed to load currencies' });
    }
});

// Create payment via NOWPayments API
app.post('/api/create-payment', async (req, res) => {
    const { price_amount, pay_currency } = req.body;

    if (!price_amount || !pay_currency) {
        return res.status(400).json({ message: 'Missing amount or currency' });
    }
     // Check if hardcoded values are present (though they always will be here)
    if (!NOWPAYMENTS_PRIVATE_KEY || !YOUR_DOMAIN) {
         console.error('Hardcoded NOWPAYMENTS_PRIVATE_KEY or YOUR_DOMAIN is missing!'); // Should not happen with hardcoding
         return res.status(500).json({ message: 'Server configuration error.' });
    }

    try {
        console.log(`Creating payment for ${price_amount} USD in ${pay_currency}`);
        const response = await fetch('https://api.nowpayments.io/v1/invoice', {
            method: 'POST',
            headers: {
                'x-api-key': NOWPAYMENTS_PRIVATE_KEY, // Using hardcoded value
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                price_amount: price_amount,
                price_currency: 'usd',
                pay_currency: pay_currency,
                ipn_callback_url: `${YOUR_DOMAIN}/api/payment-webhook`, // Using hardcoded value
                success_url: `${YOUR_DOMAIN}/beneficiary-form.html` // Using hardcoded value
            })
        });
        const data = await response.json();
        if (!response.ok) {
            console.error('NOWPayments Error creating invoice:', data);
            throw new Error(data.message || `Failed to create invoice (Status: ${response.status})`);
        }
        console.log('Payment created successfully. Invoice URL:', data.invoice_url);
        res.json({ invoiceUrl: data.invoice_url });
    } catch (error) {
        console.error('Error creating payment:', error);
        res.status(500).json({ message: error.message || 'Internal server error creating payment.' });
    }
});

// Handle payment webhook
app.post('/api/payment-webhook', (req, res) => {
    const paymentData = req.body;
    console.log('--- Webhook Received ---');
    console.log(JSON.stringify(paymentData, null, 2));
    res.status(200).send('Webhook received');
});

// Handle beneficiary form submissions
app.post('/api/submit-details', upload.single('idUpload'), async (req, res) => {
     // Check if hardcoded email values are present
    if (!EMAIL_USER || !EMAIL_PASS) {
         console.error('Hardcoded EMAIL_USER or EMAIL_PASS is missing!'); // Should not happen
         return res.status(500).json({ message: 'Email configuration error.' });
    }
    try {
        const details = req.body;
        const idFile = req.file;

        const dateOfBirth = details.dob;
        const contactNumber = details.contact;

        if (!idFile) {
            console.error("Form submission failed: No ID file was uploaded.");
            return res.status(400).send('Error: No ID file was uploaded.');
        }

        console.log('Form received. Preparing email to', EMAIL_USER);

        let emailBody = `
            <h1>New Beneficiary Submission</h1>
            <p>A payment was completed and the beneficiary has submitted their details.</p>
            <hr>
            <h2>Beneficiary Personal Details</h2>
            <p><strong>Full Name:</strong> ${details.fullName || 'Not provided'}</p>
            <p><strong>Date of Birth:</strong> ${dateOfBirth || 'Not provided'}</p>
            <p><strong>Contact Number:</strong> ${contactNumber || 'Not provided'}</p>
            <p><strong>Email:</strong> ${details.email || 'Not provided'}</p>
            <hr>
            <h2>Beneficiary Bank Details</h2>
            <p><strong>Bank Name:</strong> ${details.bankName || 'Not provided'}</p>
            <p><strong>Branch:</strong> ${details.branch || 'N/A'}</p>
            <p><strong>Country:</strong> ${details.country || 'Not provided'}</p>
            <p><strong>Currency:</strong> ${details.currency || 'Not provided'}</p>
            <p><strong>IBAN:</strong> ${details.iban || 'Not provided'}</p>
            <hr>
            <p><strong>Remarks:</strong> ${details.remarks || 'N/A'}</p>
            <br>
            <p>The user's ID file is attached to this email.</p>
        `;

        let transporter = nodemailer.createTransport({
            service: 'aol',
            auth: {
                user: EMAIL_USER, // Using hardcoded value
                pass: EMAIL_PASS  // Using hardcoded value (AOL App Password)
            }
        });

        await transporter.sendMail({
            from: `"A-Payments Network" <${EMAIL_USER}>`,
            to: EMAIL_USER,
            subject: `New Beneficiary Submission - ${details.fullName}`,
            html: emailBody,
            attachments: [
                {
                    filename: idFile.originalname,
                    content: idFile.buffer,
                    contentType: idFile.mimetype
                }
            ]
        });

        console.log('Email sent successfully to', EMAIL_USER);
        res.redirect('/?submission=success');

    } catch (error) {
        console.error('Error submitting details:', error);
        res.status(500).send('Error processing your request. Please contact support.');
    }
});

// Export the app for Vercel's Node.js runtime
module.exports = app;