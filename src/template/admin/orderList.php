<?php

//load_plugin_textdomain('aforms-eats', false, $tpldir);
$output['catalog'] = array(
    'Subtotal' => __('Subtotal', 'aforms-eats'), 
    'Tax' => __('Tax', 'aforms-eats'), 
    'Total' => __('Total', 'aforms-eats'), 
    'Order List' => __('Order List', 'aforms-eats'),  // '注文一覧', 
    'Dismiss this notice.' => __('Dismiss this notice.', 'aforms-eats'), 
    'Summary' => __('Summary', 'aforms-eats'),  // '概要', 
    'Form' => __('Form', 'aforms-eats'),  // 'フォーム', 
    'Customer' => __('Customer', 'aforms-eats'),  // 'お客様', 
    'Close' => __('Close', 'aforms-eats'),  // '閉じる', 
    'Delete' => __('Delete', 'aforms-eats'),  // '削除', 
    'Open' => __('Open', 'aforms-eats'),  // '開く', 
    'guest' => __('guest', 'aforms-eats'),  // 'ゲスト', 
    'There are no orders yet.' => __('There are no orders yet.', 'aforms-eats'),  // '注文はまだありません。'
    'Current Page' => __('Current Page', 'aforms-eats'),  // '現在のページ', 
    'First Page' => __('First Page', 'aforms-eats'),  // '最初のページ', 
    'Prev Page' => __('Prev Page', 'aforms-eats'),  // '前のページ', 
    'Next Page' => __('Next Page', 'aforms-eats'),  // 次のページ
    'Last Page' => __('Last Page', 'aforms-eats'),  // 最後のページ
    'Input a valid page number.' => __('Input a valid page number.', 'aforms-eats'), 
    'Do You Want To Remove This Order?' => __('Do You Want To Remove This Order?', 'aforms-eats'), 
    'Order deleted.' => __('Order deleted.', 'aforms-eats'), 
    ', ' => __(', ', 'aforms-eats'), 
    '(%s%% applied)' => __('(%s%% applied)', 'aforms-eats'),  // +
    'Tax (%s%%)' => __('Tax (%s%%)', 'aforms-eats'),  // +
    '(common %s%% applied)' => __('(common %s%% applied)', 'aforms-eats'),  // +
    'Tax (common %s%%)' => __('Tax (common %s%%)', 'aforms-eats'),  // +
    '%s items' => __('%s items', 'aforms-eats'), 
    '#%s' => __('#%s', 'aforms-eats'), 
    'The process was interrupted for the following reasons: %s' => __('The process was interrupted for the following reasons: %s', 'aforms-eats')
);

if ($status != "SUCCESS") {
?>
<div class="wrap">
<h1 class="wp-heading-inline"><?= esc_html($output['catalog']['Order List']) ?></h1>
<hr class="wp-header-end" />
<?= esc_html(sprintf($output['catalog']['The process was interrupted for the following reasons: %s'], $status)) ?>
</div>
<?php
return;
}

$output['rule'] = $resolve('rule')->load();
$output['pageUrl'] = $urlHelper->ajax('wqe-order', array('placeholder'));
$output['delUrl'] = $urlHelper->ajax('wqe-order-del', array('del', 'placeholder'));

$session = $resolve('session');
$output['user'] = $session->getUser();
$output['caps'] = array(
    'writeOthers' => $session->canWriteOrders(true), 
    'write' => $session->canWriteOrders(false)
);

wp_enqueue_script('aforms-eats-admin-order-js', $urlHelper->asset('/asset/admin_orders.js'), array('jquery'), \AFormsEatsWrap::VERSION);
wp_localize_script('aforms-eats-admin-order-js', 'wqData', $output);
wp_enqueue_style('aforms-eats-admin-css', $urlHelper->asset('/asset/admin.css'), array(), \AFormsEatsWrap::VERSION);

?>
<div class="wrap">
<h1 class="wp-heading-inline"><?= esc_html($output['catalog']['Order List']) ?></h1>
<hr class="wp-header-end" />
<div class="wq-Row">
  <div class="wq--main">
    <div id="root"></div>
  </div>
  <?php if (get_locale() == 'ja'): ?>
  <div class="wq--side">
    <div class="wq-Panel">
      <h2>応援をお願いします</h2>
      <p>役立つソフトをめざして日々改良を続けています。<br />
      <?= sprintf('%1$s%1$s%1$s%1$s%1$sで応援していただけると嬉しいです！', '<span class="dashicons dashicons-star-filled" style="color:#ffb900;"></span>') ?></p>
      <a class="button" href="https://wordpress.org/support/plugin/aforms-eats/reviews/" target="_blank">WordPress.orgでレビュー</a>
      <hr />
      <h2>有料版もあります</h2>
      <p>こんなことがしたいなら有料版をご検討ください！</p>
      <ul>
        <li>チョイス型セットメニュー</li>
        <li>受渡希望日時（営業時間連携）</li>
        <li>デザインのカスタマイズ</li>
        <li>料金の割引・割増</li>
        <li>お客様属性を料金に反映</li>
        <li>顧客情報の記憶・自動補完</li>
        <li>HTMLをフォームに挿入</li>
        <li>注文データCSV</li>
      </ul>
      <a class="button" href="https://a-forms.com/ja/eats/" target="_blank">デモとガイドを見る</a>
    </div>
  </div>
  <?php else: ?>
  <div class="wq--side">
    <div class="wq-Panel">
      <h2>We need your support</h2>
      <p>We are constantly improving the software to make it more useful.<br />
      <?= sprintf('%1$s%1$s%1$s%1$s%1$s to support us!', '<span class="dashicons dashicons-star-filled" style="color:#ffb900;"></span>') ?></p>
      <a class="button" href="https://wordpress.org/support/plugin/aforms-eats/reviews/" target="_blank">Review on WordPress.org</a>
      <hr />
      <h2>Paid edition also available</h2>
      <p>If you want these things, consider the paid edition!</p>
      <ul>
        <li>Combo meals</li>
        <li>Linking to Opening hours</li>
        <li>Style Customizer</li>
        <li>Discounts and Premiums</li>
        <li>Change price by attribute input</li>
        <li>Memory of Customer Info.</li>
        <li>Insert arbitrary HTML into form</li>
        <li>Order Data CSV</li>
      </ul>
      <a class="button" href="https://a-forms.com/en/eats/" target="_blank">View Demos and Guides</a>
    </div>
  </div>
  <?php endif; ?>
</div>
