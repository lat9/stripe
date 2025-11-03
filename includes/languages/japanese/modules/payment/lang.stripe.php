<?php
$define = [

     'MODULE_PAYMENT_STRIPE_TEXT_TITLE'=> 'Stripe 決済 : クレジットカード',
     'MODULE_PAYMENT_STRIPE_TEXT_NOTICES_TO_CUSTOMER'=>  '',
     'TEXT_PAYMENT_STRIPE_SUCCESS'=> 'お支払が完了しました。 最終処理中です。',

];

if (defined('MODULE_PAYMENT_STRIPE_STATUS') && MODULE_PAYMENT_STRIPE_STATUS == 'True') {
    $define['MODULE_PAYMENT_STRIPE_TEXT_DESCRIPTION'] = 'Stripe ペイメントモジュール <br> Stripeとの接続テストする場合はVISAのテスト用カード番号 4242 4242 4242 4242をご利用ください。.<br><br>重要事項: APIキー変更後は、必ず下記項目を実行してください。<br>
管理者ページ => 追加設定・ツール =>SQLパッチのインストール画面から、erase_stripe_recordes.sqlファイルをアップロード 又は、「クエリ文を貼り付けて実行してください。」のテキストボックスに TRUNCATE ` stripe `; を貼り付けて「送信」ボタンを押してください。';
} else {
    $define['MODULE_PAYMENT_STRIPE_TEXT_DESCRIPTION'] = '<a rel="noreferrer noopener" target="_blank" href="https://stripe.com/">ここをクリックし、アカウント作成してください。 </a> <br><br><strong>必要事項<br>&nbsp;Stripe API 公開キー<br>&nbsp;Stripe API シークレットキー<br>&nbsp;Stripe API 公開テストキー<br>&nbsp;Stripe API シークレットテストキー<br></strong> ';
}

return $define;
