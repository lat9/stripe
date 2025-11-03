<?php
$define = [];

global $current_page_base;
if (in_array($current_page_base, [FILENAME_CHECKOUT_CONFIRMATION, 'checkout_one_confirmation'], true)) {
    $define['TEXT_PAYMENT_STRIPE_MODULE_CORP'] = '<a href="https://www.yokane.co.jp/" target="_blank"  rel="noopener noreferrer">Powered by Nihon Yokane corporation</a> ';
];

if ($current_page_base === FILENAME_ACCOUNT) {
    $define['TEXT_STRIPE_CARD_INFORMATION'] = 'Stripe クレジットカード情報';
    $define['TEXT_DELETE_STRIPE'] = '削　除';
}
return $define;
