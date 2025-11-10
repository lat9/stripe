<?php
// -----
// Part of the Stripe payment module for Zen Cart versions 1.5.8+.
//
// Stripe v3.0.0
//
class zcAjaxStripe
{
    // -----
    // Check the number of times a credit-card failure has been recorded.
    //
    public function checkCC()
    {
        zen_define_default('MAX_STRIPE_FAILED_ATTEMPTS', 3);

        $_SESSION['stripe_payment_attempts'] ??= 0;
        $_SESSION['stripe_payment_attempts']++;

        // -----
        // Return the attempts-exceeded status
        //
        return [
            'status' => ($_SESSION['stripe_payment_attempts'] > (int)MAX_STRIPE_FAILED_ATTEMPTS) ? 'false' : 'ok',
        ];
    }
}
