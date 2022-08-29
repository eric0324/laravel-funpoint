<?php
return [
    'MerchantId' => env('FUNPOINT_MERCHANT_ID', ''),
    'HashKey' => env('FUNPOINT_HASH_KEY', ''),
    'HashIV' => env('FUNPOINT_HASH_IV', ''),
    'InvoiceHashKey' => env('FUNPOINT_INVOICE_HASH_KEY', ''),
    'InvoiceHashIV' => env('FUNPOINT_INVOICE_HASH_IV', ''),
    'SendForm' => env('FUNPOINT_SEND_FORM', null)
];