<?php

define('MODULE_PAYMENT_STRIPE_TEXT_TITLE', 'Carte de paiement sécurisé (Stripe)');
define('MODULE_PAYMENT_STRIPE_TEXT_NOTICES_TO_CUSTOMER',  '');
define('TEXT_PAYMENT_STRIPE_SUCCESS', 'Le paiement a été fait avec succès ! Veuillez attendre quelques secondes.');

if (defined('MODULE_PAYMENT_STRIPE_STATUS') && MODULE_PAYMENT_STRIPE_STATUS == 'True') {
    define('MODULE_PAYMENT_STRIPE_TEXT_DESCRIPTION', 'Module de paiement Stripe <br> Lors d\'un test interactif, utilisez la carte Visa de test 4242 4242 4242 4242.<br><br>IMPORTANT : Après avoir changer la clé API, vous devez suivre les étapes ci-dessous<br> .
Allez sur la page Admin => Tools =>Install SQL patches. Téléchargez le fichier erase_stripe_recordes.sql ou collez le code php TRUNCATE ` stripe `; dans la boîte de texte "Enter the queryto be executed:" et cliquez sur le bouton "Send".');
} else {
    define('MODULE_PAYMENT_STRIPE_TEXT_DESCRIPTION', '<a rel="noreferrer noopener" target="_blank" href="https://stripe.com/">Cliquez ici pour vous créer un compte </a> <br><br><strong>Exigences<br>&nbsp;Clé publique de l\'API Stripe<br>&nbsp;Clé secrète de l\'API Stripe<br>&nbsp;Clé de test de l\'API publique Stripe<br>&nbsp;Clé de test secrète de l\'API Stripe<br></strong> ');
}

