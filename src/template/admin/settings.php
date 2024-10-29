<?php

//load_plugin_textdomain('aforms-eats', false, $tpldir);
$output['catalog'] = array(
    'Form Settings' => __('Form Settings', 'aforms-eats'),  // 'フォーム設定', 
    'Round Down' => __('Round Down', 'aforms-eats'),  // '切り下げ', 
    'Round Up' => __('Round Up', 'aforms-eats'),  // '切り上げ', 
    'Round Off' => __('Round Off', 'aforms-eats'),  // '四捨五入', 
    'Truncate' => __('Truncate', 'aforms-eats'),  // '切り捨て', 
    'should be integer' => __('should be integer', 'aforms-eats'),  // '数値を入力してください', 
    'Settings saved.' => __('Settings saved.', 'aforms-eats'),  // '設定を保存しました', 
    'Dismiss this notice.' => __('Dismiss this notice.', 'aforms-eats'),  // 'この通知を非表示にする', 
    'Fraction Treatment' => __('Fraction Treatment', 'aforms-eats'),  // '端数の取り扱い', 
    'Tax Included' => __('Tax Included', 'aforms-eats'),  // '内税表記', 
    'Tax Excluded' => __('Tax Excluded', 'aforms-eats'),  // '外税表記', 
    'Tax Rate' => __('Tax Rate', 'aforms-eats'),  // '税率', 
    '%' => __('%', 'aforms-eats'),  // '%', 
    'Fraction Processing' => __('Fraction Processing', 'aforms-eats'),  // '端数の処理方法', 
    'Processing Precision' => __('Processing Precision', 'aforms-eats'),  // '端数処理のケタ', 
    'The number of digits left by rounding. If "1" is specified, the processing result will be "12.3".' => __('The number of digits left by rounding. If "1" is specified, the processing result will be "12.3".', 'aforms-eats'),  // '端数処理で残すケタ数です。「1」を指定すると処理結果が「12.3」のようになります。', 
    'Save' => __('Save', 'aforms-eats'),  // '変更を保存', 
    'Tax Notation' => __('Tax Notation', 'aforms-eats'),  // '税の表記方法'
    'Discard Changes' => __('Discard Changes', 'aforms-eats'), 
    'Commit Changes' => __('Commit Changes', 'aforms-eats'), 
    'Changes committed. Be sure to save data before moving to another page.' => __('Changes committed. Be sure to save data before moving to another page.', 'aforms-eats'), 
    'Calculation Rule' => __('Calculation Rule', 'aforms-eats'), 
    'Words' => __('Words', 'aforms-eats'), 
    'Behavior' => __('Behavior', 'aforms-eats'), 
    'Smooth Scroll' => __('Smooth Scroll', 'aforms-eats'), 
    'Do Smooth Scroll' => __('Do Smooth Scroll', 'aforms-eats'), 
    'Don\'t Smooth Scroll' => __('Don\'t Smooth Scroll', 'aforms-eats'), 
    'Return To Top On Group Selection' => __('Return To Top On Group Selection', 'aforms-eats'), 
    'Do Scroll' => __('Do Scroll', 'aforms-eats'), 
    'Don\'t Scroll' => __('Don\'t Scroll', 'aforms-eats'), 
    'The process was interrupted for the following reasons: %s' => __('The process was interrupted for the following reasons: %s', 'aforms-eats')
);

if ($status != "SUCCESS") {
?>
<div class="wrap">
<h1 class="wp-heading-inline"><?= esc_html($output['catalog']['Form Settings']) ?></h1>
<hr class="wp-header-end" />
<?= esc_html(sprintf($output['catalog']['The process was interrupted for the following reasons: %s'], $status)) ?>
</div>
<?php
return;
}

$renderer->embed('admin/capsys');

$output['submitUrl'] = $urlHelper->ajax('wqe-settings-set');

wp_enqueue_script('aforms-eats-admin-settings-js', $urlHelper->asset('/asset/admin_settings.js'), array('jquery'), \AFormsEatsWrap::VERSION);
wp_localize_script('aforms-eats-admin-settings-js', 'wqData', $output);
wp_enqueue_style('aforms-eats-admin-css', $urlHelper->asset('/asset/admin.css'), array(), \AFormsEatsWrap::VERSION);

?>
<div class="wrap">
  <div class="wq-TitleBar">
    <h1 class="wp-heading-inline"><?= esc_html($output['catalog']['Form Settings']) ?></h1>
    <div class="wq--spacer"></div>
    <button id="save-button" class="button button-primary button-large"><?= esc_html($output['catalog']['Save']) ?></button>
  </div>
  <hr class="wp-header-end" />
  <div id="root"></div>
</div>