<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #000;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            padding: 30px;
            background-color: #1a1a1a;
            border-radius: 20px;
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.3);
        }

        h1 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #4dff4d;
        }

        img {
            width: 100%;
            max-width: 300px;
            margin-bottom: 20px;
        }

        p {
            font-size: 16px;
            margin: 10px 0;
        }

        strong {
            color: #ccc;
        }

        .btn-go-to {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            color: #000;
            background-color: #4dff4d;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .btn-go-to:hover,
        .btn-go-to:focus {
            background-color: #00cc00;
            outline: 2px solid #fff;
            outline-offset: 2px;
        }
    </style>
</head>

<body>
    <main role="main" aria-labelledby="payment-success-heading">
        <section class="container" role="status" aria-describedby="payment-success-description">
            <h1 id="payment-success-heading">Payment Successful</h1>
            <img src="{{ asset('assets/images/paymentsuccess.gif') }}" alt="Animated confirmation of a successful payment">
            <p id="payment-success-description">Thank you! Your payment has been processed successfully.</p>

            <p>
                <strong>Transaction ID:</strong>
                {{ Str::limit($transaction->stripe_transaction_id, 20, '...') }}
            </p>
            <p>
                <strong>Total Amount Paid:</strong>
                ${{ number_format($transaction->total_price, 2) }}
            </p>

            <a href="{{ route('tickets') }}" class="btn-go-to" role="button" aria-label="Go to your tickets" wire:navigate>
                Go to Tickets
            </a>
        </section>
    </main>
</body>

</html>
