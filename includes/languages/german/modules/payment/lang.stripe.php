<?php
$define = [
    'MODULE_PAYMENT_STRIPE_TEXT_TITLE' => 'Stripe zahlungen: Kreditkarte',
    'MODULE_PAYMENT_STRIPE_TEXT_DESCRIPTION' => 'Stripe zahlungen: Kreditkarte',
    'MODULE_PAYMENT_STRIPE_TEXT_NOTICES_TO_CUSTOMER' => '',
    'TEXT_PAYMENT_STRIPE_SUCCESS'=>'Zahlung erfolgreich abgeschlossen! Bitte warten Sie ein paar Sekunden.',

];

if (defined('MODULE_PAYMENT_STRIPE_STATUS') && MODULE_PAYMENT_STRIPE_STATUS == 'True') {
    $define['MODULE_PAYMENT_STRIPE_TEXT_DESCRIPTION'] = 'Stripe-Zahlungsmodul <br> Verwenden Sie beim interaktiven Testen eine Test-Visa-Karte 4242 4242 4242 4242.<br><br>WICHTIG: Nachdem Sie den API-Schl&uuml;ssel ge&auml;ndert haben, m&uuml;ssen Sie die folgenden Schritte ausf&uuml;hren<br>
Gehe zu Admin => Tools => SQL Patches Installieren. <br>Laden Sie die erase_stripe_recordes.sql file hoch oder PHP-Code einf&uuml;gen TRUNCATE `stripe`; im "SQL-Befehl(e) ausf&uuml;hren:(Abschliessen mit)."  und dr&uuml;cken Sie die Schaltfl&auml;che "Senden".';
} else {
    $define['MODULE_PAYMENT_STRIPE_TEXT_DESCRIPTION'] = '<a rel="noreferrer noopener" target="_blank" href="https://stripe.com/">Klicken Sie hier, um sich für ein Konto anzumelden </a> <br><br><strong>Anforderungen<br>&nbsp;Stripe API Veröffentlichbarer Schlüssel<br>&nbsp;Stripe API Geheimschlüssel<br>&nbsp;Stripe API test Veröffentlichbarer Schlüssel<br>&nbsp;Stripe API test Geheimschlüssel<br></strong>';
}



return $define;

