<?php

//load_plugin_textdomain('aforms-eats', false, $tpldir);
$output['catalog'] = array(
    //'' => __('', 'aforms-eats'), 
    'Form List' => __('Form List', 'aforms-eats'), 
    'Add New' => __('Add New', 'aforms-eats'), 
    'Title' => __('Title', 'aforms-eats'), 
    'Author' => __('Author', 'aforms-eats'), 
    'Date' => __('Date', 'aforms-eats'), 
    'ID' => __('ID', 'aforms-eats'), 
    'Edit' => __('Edit', 'aforms-eats'), 
    'Duplicate' => __('Duplicate', 'aforms-eats'), 
    'Trash' => __('Trash', 'aforms-eats'), 
    'Preview' => __('Preview', 'aforms-eats'), 
    'Do You Want To Remove This Form?' => __('Do You Want To Remove This Form?', 'aforms-eats'), 
    'Form deleted.' => __('Form deleted.', 'aforms-eats'), 
    'Dismiss this notice.' => __('Dismiss this notice.', 'aforms-eats'), 
    'Form duplicated.' => __('Form duplicated.', 'aforms-eats'), 
    'View' => __('View', 'aforms-eats'), 
    'The process was interrupted for the following reasons: %s' => __('The process was interrupted for the following reasons: %s', 'aforms-eats')
);

if ($status != "SUCCESS") {
?>
<div class="wrap">
<h1 class="wp-heading-inline"><?= esc_html($output['catalog']['Form List']) ?></h1>
<hr class="wp-header-end" />
<?= esc_html(sprintf($output['catalog']['The process was interrupted for the following reasons: %s'], $status)) ?>
</div>
<?php
return;
}

$output['dupUrl'] = $urlHelper->ajax('wqe-form-dup', array('dup', 'placeholder'));
$output['delUrl'] = $urlHelper->ajax('wqe-form-del', array('del', 'placeholder'));
$output['editUrl'] = $urlHelper->adminPage('wqe-form', array('edit', 'placeholder'));
$output['newUrl'] = $urlHelper->adminPage('wqe-form', array('new', '-1'));
$output['pvUrl'] = $urlHelper->adminPage('wqe-form', array('preview', 'placeholder'));

$session = $resolve('session');
$output['user'] = $session->getUser();
$output['caps'] = array(
    'writeOthers' => $session->canWriteForms(true), 
    'write' => $session->canWriteForms(false)
);

wp_enqueue_script('aforms-eats-admin-forms-js', $urlHelper->asset('/asset/admin_forms.js'), array('jquery'), \AFormsEatsWrap::VERSION);
wp_localize_script('aforms-eats-admin-forms-js', 'wqData', $output);
wp_enqueue_style('aforms-eats-admin-css', $urlHelper->asset('/asset/admin.css'), array(), \AFormsEatsWrap::VERSION);

?>
<div class="wrap">
<h1 class="wp-heading-inline"><?= esc_html($output['catalog']['Form List']) ?></h1>
<a href="<?= esc_url($output['newUrl']) ?>" class="page-title-action"><?= esc_html($output['catalog']['Add New']) ?></a>
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