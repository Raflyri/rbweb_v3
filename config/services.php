<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    |
    | Which method App\Services\Payment\PaymentGatewayResolver hands out. Only
    | 'manual_transfer' exists today; phase 5 adds 'midtrans' behind its own
    | activation switch.
    |
    */

    'payment' => [
        'active_gateway' => 'manual_transfer',
    ],

    /*
    | Bank account shown to buyers on the payment page.
    |
    | ⚠️  ISI TIGA NILAI DI BAWAH INI SEBELUM MENERIMA PEMBAYARAN.
    |
    | Kept here rather than in .env on purpose: this is information printed on
    | a public page for buyers to read, not a secret. Selama masih berisi
    | placeholder, halaman pembayaran tidak menampilkan rekening apa pun —
    | pembeli diarahkan menghubungi kami (lihat ManualTransferGateway).
    */

    'manual_transfer' => [
        'bank_name'      => 'ISI_NAMA_BANK',
        'account_number' => 'ISI_NOMOR_REKENING',
        'account_holder' => 'ISI_ATAS_NAMA',
    ],

];
