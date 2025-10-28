const express = require('express');
const fetch = require('node-fetch');
const nodemailer = require('nodemailer');
const multer = require('multer');
const path = require('path');

const NOWPAYMENTS_PRIVATE_KEY = '06PYHFE-FM6MH8Z-M5PYY98-EWPN3MJ';
const EMAIL_USER = 'apaynet@aol.com';
const EMAIL_PASS = 'YOUR_AOL_APP_PASSWORD_GOES_HERE';
const YOUR_DOMAIN = 'https://www.apaymentsnetwork.com';

const app = express();
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// ✅ Fix: Serve all files in the current directory (prevents 403 errors)
app.use(express.static(__dirname));

const upload = multer({ storage: multer.memoryStorage() });

// ✅ Default route - open sendpay.html when accessing root
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'sendpay.html'));
});

// Route to sendpay page
app.get('/sendpayapi', (req, res) => {
    res.sendFile(path.join(__dirname, 'sendpay.html'));
});

// Route to beneficiary form
app.get('/beneficiary-form', (req, res) => {
    res.sendFile(path.join(__dirname, 'beneficiary-form.html'));
});

// Create payment via NOWPayments API
app.post('/api/create-payment', async (req, res) => {
    const { price_amount, pay_currency } = req.body;

    if (!price_amount || !pay_currency) {
        return res.status(400).json({ message: 'Missing amount or currency' });
    }

    try {
        console.log(`Creating payment for ${price_amount} USD in ${pay_currency}`);

        const response = await fetch('https://api.nowpayments.io/v1/invoice', {
            method: 'POST',
            headers: {
                'x-api-key': NOWPAYMENTS_PRIVATE_KEY,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                price_amount: price_amount,
                price_currency: 'usd',
                pay_currency: pay_currency,
                ipn_callback_url: `${YOUR_DOMAIN}/api/payment-webhook`,
                success_url: `${YOUR_DOMAIN}/beneficiary-form`
            })
        });

        const data = await response.json();

        if (!response.ok) {
            console.error('NOWPayments Error:', data);
            throw new Error(data.message || 'Failed to create invoice');
        }

        console.log('Payment created successfully. Invoice URL:', data.invoice_url);
        res.json({ invoiceUrl: data.invoice_url });

    } catch (error) {
        console.error('Error creating payment:', error);
        res.status(500).json({ message: error.message });
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
    try {
        const details = req.body;
        const idFile = req.file;

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
            <p><strong>Date of Birth:</strong> ${details.dateOfBirth || 'Not provided'}</p>
            <p><strong>Contact Number:</strong> ${details.contactNumber || 'Not provided'}</p>
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
                user: EMAIL_USER,
                pass: EMAIL_PASS
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
        res.redirect('https://www.apaymentsnetwork.com/dts');

    } catch (error) {
        console.error('Error submitting details:', error);
        res.status(500).send('Error processing your request. Please contact support.');
    }
});

// Start the server
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`✅ Server running on port ${PORT}`);
    console.log(`➡️  Access main page at: http://localhost:${PORT}/`);
});
