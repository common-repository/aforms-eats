<?php

if ($status != "SUCCESS") {
  echo "X:".$status;
  exit;
}

$word = $resolve('word')->load();

//load_plugin_textdomain('aforms-eats', false, $tpldir);
$catalog = array(
    'Select Image' => __('Select Image', 'aforms-eats'), 
    'OK' => __('OK', 'aforms-eats'), 
    'Open Media' => __('Open Media', 'aforms-eats'), 
    'Clear' => __('Clear', 'aforms-eats'), 
    'Title' => __('Title', 'aforms-eats'), 
    'The name for you to distinguish the form. The end-users don\'t see this.' => __('The name for you to distinguish the form. The end-users don\'t see this.', 'aforms-eats'),  // 運営者がフォームを区別するための名前です。お客様には表示されません。
    'Shortcode' => __('Shortcode', 'aforms-eats'), 
    'Embed the shortcode above in a post or a page to display this.' => __('Embed the shortcode above in a post or a page to display this.', 'aforms-eats'),  // 上記のショートコードを記事や固定ページに埋め込むと、そのページにこのフォームが表示されます。
    'Commit Changes' => __('Commit Changes', 'aforms-eats'),  // 変更を確定
    'Discard Changes' => __('Discard Changes', 'aforms-eats'),  // 変更を破棄
    'New Auto Item' => __('New Auto Item', 'aforms-eats'),  // 新しい自動項目
    'New Selector Item' => __('New Selector Item', 'aforms-eats'),  // 新しい選択項目
    'New Option' => __('New Option', 'aforms-eats'),  // 新しい選択肢
    'Auto Item' => __('Auto Item', 'aforms-eats'),  // 自動項目
    'Selector Item' => __('Selector Item', 'aforms-eats'),  // 選択項目
    'Option' => __('Option', 'aforms-eats'),  // 選択肢
    'Duplicate' => __('Duplicate', 'aforms-eats'),  // 複製
    'Delete' => __('Delete', 'aforms-eats'),  // 削除
    'Close Nav' => __('Close Nav', 'aforms-eats'),  // ナビを閉じる
    'Open Nav' => __('Open Nav', 'aforms-eats'),  // ナビを開く
    'Name' => __('Name', 'aforms-eats'), 
    'Category' => __('Category', 'aforms-eats'), 
    'Input here if you want to display a category name in a order detail.' => __('Input here if you want to display a category name in a order detail.', 'aforms-eats'),  // 明細にカテゴリーを表記したい場合は入力してください。
    'Price' => __('Price', 'aforms-eats'), 
    'Labels' => __('Labels', 'aforms-eats'),  // ラベル
    'Required Labels' => __('Required Labels', 'aforms-eats'),  // 必要ラベル
    'Image' => __('Image', 'aforms-eats'),  // 画像
    'Note' => __('Note', 'aforms-eats'),  // 注意書き
    'You can write in HTML.' => __('You can write in HTML.', 'aforms-eats'),  // HTMLで記述できます。
    'Multiple Selection' => __('Multiple Selection', 'aforms-eats'),  // 複数選択
    'Allow' => __('Allow', 'aforms-eats'),  // 可
    'Disallow' => __('Disallow', 'aforms-eats'),  // 不可
    'Regular Price' => __('Regular Price', 'aforms-eats'),  // 通常価格
    'Price' => __('Price', 'aforms-eats'), 
    'You can display the manufacturer\'s desired price.' => __('You can display the manufacturer\'s desired price.', 'aforms-eats'),  // メーカー希望価格などを表示できます。
    'New Input Field' => __('New Input Field', 'aforms-eats'),  // 新しい入力欄
    'Additional Menu' => __('Additional Menu', 'aforms-eats'),  // 追加メニュー
    'Phone Number' => __('Phone Number', 'aforms-eats'), 
    'Mail Address' => __('Mail Address', 'aforms-eats'), 
    'Address' => __('Address', 'aforms-eats'), 
    'Checkbox' => __('Checkbox', 'aforms-eats'), 
    'Radio Button' => __('Radio Button', 'aforms-eats'), 
    'Text' => __('Text', 'aforms-eats'), 
    'Input Required' => __('Input Required', 'aforms-eats'), 
    'Required' => __('Required', 'aforms-eats'),  // 必須
    'Optional' => __('Optional', 'aforms-eats'),  // 自由
    'Split Input Field' => __('Split Input Field', 'aforms-eats'),  // 入力欄の分割
    'Split' => __('Split', 'aforms-eats'),  // 分割する
    'Don\'t Split' => __('Don\'t Split', 'aforms-eats'),  // 分割しない
    'Confirmation Input' => __('Confirmation Input', 'aforms-eats'),  // 確認入力
    'Whether to have email address entered twice for confirmation.' => __('Whether to have email address entered twice for confirmation.', 'aforms-eats'),  // 確認のためメールアドレスを二度入力してもらうかどうか。
    'Confirm' => __('Confirm', 'aforms-eats'),  // 確認を行う
    'Don\'t Confirm' => __('Don\'t Confirm', 'aforms-eats'),  // 確認を行わない
    'Options' => __('Options', 'aforms-eats'),  // 選択肢
    'Separate them with ",".' => __('Separate them with ",".', 'aforms-eats'),  // 「,」で区切ってください。
    'Number of Lines' => __('Number of Lines', 'aforms-eats'),  // 行数
    'Multiple Lines' => __('Multiple Lines', 'aforms-eats'),  // 複数行
    '1 Line' => __('1 Line', 'aforms-eats'),  
    'Width of Input Field' => __('Width of Input Field', 'aforms-eats'), 
    'Nano' => __('Nano', 'aforms-eats'), 
    'Mini' => __('Mini', 'aforms-eats'), 
    'Small' => __('Small', 'aforms-eats'), 
    'Regular' => __('Regular', 'aforms-eats'), 
    'Full' => __('Full', 'aforms-eats'), 
    'Up to 3 characters.' => __('Up to 3 characters.', 'aforms-eats'), 
    'Up to 5 characters.' => __('Up to 5 characters.', 'aforms-eats'), 
    'Up to 8 characters.' => __('Up to 8 characters.', 'aforms-eats'), 
    'Up to 13 characters.' => __('Up to 13 characters.', 'aforms-eats'), 
    'Full width' => __('Full width', 'aforms-eats'), 
    'Subject' => __('Subject', 'aforms-eats'), 
    'From Address' => __('From Address', 'aforms-eats'), 
    'From Name' => __('From Name', 'aforms-eats'), 
    'Notify To' => __('Notify To', 'aforms-eats'),  // 通知先アドレス
    'You can also send a copy of the thank-you-mail to another address. Separate them with "," to specify multiple addresses.' => __('You can also send a copy of the thank-you-mail to another address. Separate them with "," to specify multiple addresses.', 'aforms-eats'),  // サンキューメールのコピーを別のアドレスに送ることもできます。アドレスを複数指定する場合は「,」で区切ってください。
    'Text Body' => __('Text Body', 'aforms-eats'), 
    'HTML Body' => __('HTML Body', 'aforms-eats'), 
    'Leave here blank if you don\'t want to send email in HTML format.' => __('Leave here blank if you don\'t want to send email in HTML format.', 'aforms-eats'),  // メールをHTML形式で送らない場合は空欄のままで構いません。
    'Save' => __('Save', 'aforms-eats'), 
    'Form Details' => __('Form Details', 'aforms-eats'),  // フォーム詳細
    'Form saved.' => __('Form saved.', 'aforms-eats'),  // フォームを保存しました。
    'Dismiss this notice.' => __('Dismiss this notice.', 'aforms-eats'), 
    'General' => __('General', 'aforms-eats'),  
    'Details' => __('Details', 'aforms-eats'), 
    'Attributes' => __('Attributes', 'aforms-eats'), 
    'Mail' => __('Mail', 'aforms-eats'), 
    'should NOT be shorter than 1 characters' => __('should NOT be shorter than 1 characters', 'aforms-eats'), 
    'Changes committed. Be sure to save data before moving to another page.' => __('Changes committed. Be sure to save data before moving to another page.', 'aforms-eats'), 
    'should match pattern' => __('should match pattern', 'aforms-eats'), 
    'should be number' => __('should match pattern', 'aforms-eats'), 
    'Preview' => __('Preview', 'aforms-eats'), 
    'Display Confirmation Screen' => __('Display Confirmation Screen', 'aforms-eats'), 
    'Display' => __('Display', 'aforms-eats'), 
    'Don\'t Display' => __('Don\'t Display', 'aforms-eats'), 
    'Type' => __('Type', 'aforms-eats'), 
    'should match format "uri"' => __('should match format "uri"', 'aforms-eats'), 
    'Thanks Url' => __('Thanks Url', 'aforms-eats'), 
    'If you want to display another page after submitting the form, enter the URL.' => __('If you want to display another page after submitting the form, enter the URL.', 'aforms-eats'), 
    'Navigator' => __('Navigator', 'aforms-eats'), 
    'Flow format' => __('Flow format', 'aforms-eats'), 
    'Wizard format' => __('Wizard format', 'aforms-eats'), 
    'Price Checker' => __('Price Checker', 'aforms-eats'), 
    'Equation' => __('Equation', 'aforms-eats'), 
    'Threshold' => __('Threshold', 'aforms-eats'), 
    'Equal' => __('Equal', 'aforms-eats'), 
    'Not Equal' => __('Not Equal', 'aforms-eats'), 
    'Greater Than' => __('Greater Than', 'aforms-eats'), 
    'Greater Equal' => __('Greater Equal', 'aforms-eats'), 
    'Less Than' => __('Less Than', 'aforms-eats'), 
    'Less Equal' => __('Less Equal', 'aforms-eats'), 
    'Adds a fixed detail line.' => __('Adds a fixed detail line.', 'aforms-eats'), 
    'Monitors the estimated price and gives labels if condition is met.' => __('Monitors the estimated price and gives labels if condition is met.', 'aforms-eats'), 
    'Creates a group of choices.' => __('Creates a group of choices.', 'aforms-eats'), 
    'Adds a detail line if selected.' => __('Adds a detail line if selected.', 'aforms-eats'), 
    'You can insert the following data into the text body.' => __('You can insert the following data into the text body.', 'aforms-eats'), 
    'Order id' => __('Order id', 'aforms-eats'), 
    'Detail lines' => __('Detail lines', 'aforms-eats'), 
    'Total; In case of tax-excluded notation, subtotal and tax are included.' => __('Total; In case of tax-excluded notation, subtotal and tax are included.', 'aforms-eats'), 
    'Customer attributes' => __('Customer attributes', 'aforms-eats'), 
    'Customer name; Available only when using Name control.' => __('Customer name; Available only when using Name control.', 'aforms-eats'), 
    'Customer mail address; Available only when using MailAddress control.' => __('Customer mail address; Available only when using MailAddress control.', 'aforms-eats'), 
    'Separete with ",". This item is availble only if all labels listed are satisfied.' => __('Separete with ",". This item is availble only if all labels listed are satisfied.', 'aforms-eats'), 
    'Separate with ",". If the conditions are met, all the labels listed will be awarded.' => __('Separate with ",". If the conditions are met, all the labels listed will be awarded.', 'aforms-eats'), 
    'Separate with ",". If this option is selected, all the labels listed will be awarded.' => __('Separate with ",". If this option is selected, all the labels listed will be awarded.', 'aforms-eats'), 
    'New Quantity Item' => __('New Quantity Item', 'aforms-eats'), 
    'Quantity Item' => __('Quantity Item', 'aforms-eats'), 
    'Fixed To 1' => __('Fixed To 1', 'aforms-eats'), 
    'Allows Fraction' => __('Allows Fraction', 'aforms-eats'), 
    'Input if you want to add a unit to the input field.' => __('Input if you want to add a unit to the input field.', 'aforms-eats'), 
    'Minimum Value' => __('Minimum Value', 'aforms-eats'), 
    'Maximum Value' => __('Maximum Value', 'aforms-eats'), 
    'Prompts quantity.' => __('Prompts quantity.', 'aforms-eats'), 
    'Initial Value' => __('Initial Value', 'aforms-eats'), 
    'Unit' => __('Unit', 'aforms-eats'), 
    'Can be empty.' => __('Can be empty.', 'aforms-eats'), 
    'Quantity' => __('Quantity', 'aforms-eats'), 
    'Off' => __('Off', 'aforms-eats'), 
    'On' => __('On', 'aforms-eats'), 
    'Dropdown' => __('Dropdown', 'aforms-eats'), 
    'Site Key' => __('Site Key', 'aforms-eats'), 
    'Secret Key' => __('Secret Key', 'aforms-eats'), 
    'Action' => __('Action', 'aforms-eats'), 
    'Soft-Pass Score' => __('Soft-Pass Score', 'aforms-eats'), 
    "If the score is lower than this value, AForms considers that the submission is somewhat unreliable and email notifications to administrators will be omitted." => __("If the score is lower than this value, AForms considers that the submission is somewhat unreliable and email notifications to administrators will be omitted.", 'aforms-eats'),  // 信頼性がやや低いとみなされ、運営者への通知メールが省略されます。
    'Failure Score' => __('Failure Score', 'aforms-eats'), 
    "If the score is lower than this value, AForms blocks the submission and show an error to customer." => __("If the score is lower than this value, AForms blocks the submission and show an error to customer.", 'aforms-eats'), 
    "A string that identifies the user's action. Refer: " => __("A string that identifies the user's action. Refer: ", 'aforms-eats'), 
    'Auto Completion' => __('Auto Completion', 'aforms-eats'), 
    'Choose a service to auto-complete address from zip code.' => __('Choose a service to auto-complete address from zip code.', 'aforms-eats'), 
    'None' => __('None', 'aforms-eats'), 
    'Yubinbango (Japan)' => __('Yubinbango (Japan)', 'aforms-eats'), 
    'Input Restriction' => __('Input Restriction', 'aforms-eats'), 
    'Japanese Hiragana' => __('Japanese Hiragana', 'aforms-eats'), 
    'Japanese Katakana' => __('Japanese Katakana', 'aforms-eats'), 
    'Price Checker (OBSOLETED)' => __('Price Checker (OBSOLETED)', 'aforms-eats'), 
    'Monitors the estimated price and gives labels if condition is met. This item is OBSOLETED and deleted in the near future.' => __('Monitors the estimated price and gives labels if condition is met. This item is OBSOLETED and deleted in the near future.', 'aforms-eats'), 
    'Price Watcher' => __('Price Watcher', 'aforms-eats'), 
    'Lower Limit Value' => __('Lower Limit Value', 'aforms-eats'), 
    'Leave this blank if there are no lower limit.' => __('Leave this blank if there are no lower limit.', 'aforms-eats'), 
    'Includes Lower Limit Value' => __('Includes Lower Limit Value', 'aforms-eats'), 
    'Include' => __('Include', 'aforms-eats'), 
    'Don\'t Include' => __('Don\'t Include', 'aforms-eats'), 
    'Includes Higher Limit Value' => __('Includes Higher Limit Value', 'aforms-eats'), 
    'Higher Limit Value' => __('Higher Limit Value', 'aforms-eats'), 
    'Leave this blank if there are no higher limit.' => __('Leave this blank if there are no higher limit.', 'aforms-eats'), 
    'Monitors the estimated price and gives labels if the price is included in a spacified range.' => __('Monitors the estimated price and gives labels if the price is included in a spacified range.', 'aforms-eats'), 
    'Multiple Checkbox' => __('Multiple Checkbox', 'aforms-eats'), 
    'reCAPTCHA v3' => __('reCAPTCHA v3', 'aforms-eats'), 
    'Ribbons' => __('Ribbons', 'aforms-eats'), 
    'SALE' => __('SALE', 'aforms-eats'), 
    'RECOMMENDED' => __('RECOMMENDED', 'aforms-eats'), 
    'Group' => __('Group', 'aforms-eats'), 
    'Product Item' => __('Product Item', 'aforms-eats'), 
    'Creates a group of products.' => __('Creates a group of products.', 'aforms-eats'), 
    'Adds a product that can be ordered with quantity.' => __('Adds a product that can be ordered with quantity.', 'aforms-eats'), 
    'Tax Rate' => __('Tax Rate', 'aforms-eats'), 
    'The tax rate on common settings will be applied when you leave it blank.' => __('The tax rate on common settings will be applied when you leave it blank.', 'aforms-eats'), 
    '%' => __('%', 'aforms-eats'), 
    'should be >= 0' => __('should be >= 0', 'aforms-eats'), 
    '%s\'s Copy' => __('%s\'s Copy', 'aforms-eats'), 
    'Visibility' => __('Visibility', 'aforms-eats'), 
    'Visible' => __('Visible', 'aforms-eats'), 
    'Invisible' => __('Invisible', 'aforms-eats'), 
    'New Group' => __('New Group', 'aforms-eats'), 
    'New Product' => __('New Product', 'aforms-eats'), 
    'State' => __('State', 'aforms-eats'), 
    'Effective' => __('Effective', 'aforms-eats'), 
    'Disabled' => __('Disabled', 'aforms-eats'), 
    'Hidden' => __('Hidden', 'aforms-eats'), 
    'Stop' => __('Stop', 'aforms-eats'), 
    'Stops form submission under certain conditions.' => __('Stops form submission under certain conditions.', 'aforms-eats'), 
    'Message' => __('Message', 'aforms-eats'), 
    'Separete with ",". Form submission is stopped if all labels listed are given.' => __('Separete with ",". Form submission is stopped if all labels listed are given.', 'aforms-eats'), 
    'Appears when the form submission was stopped.' => __('Appears when the form submission was stopped.', 'aforms-eats'), 
    'Placeholder' => __('Placeholder', 'aforms-eats'), 
    'Set Return-Path' => __('Set Return-Path', 'aforms-eats'), 
    'Uncheck this if you prefer the default behavior of WordPress.' => __('Uncheck this if you prefer the default behavior of WordPress.', 'aforms-eats'), 
    'Set Return-Path to be the same as the From address' => __('Set Return-Path to be the same as the From address', 'aforms-eats')
);
$catalog['SALE'] = $word['SALE'];
$catalog['RECOMMENDED'] = $word['RECOMMENDED'];

$output['catalog'] = $catalog;
$output['noimageUrl'] = $urlHelper->asset('/asset/noimage.png');
$output['submitUrl'] = $urlHelper->ajax('wqe-form-set', array('edit', 'placeholder'));
$output['editUrl'] = $urlHelper->adminPage('wqe-form', array('edit', 'placeholder'));
$output['pvUrl'] = $urlHelper->adminPage('wqe-form', array('preview', 'placeholder'));

wp_enqueue_script('aforms-eats-admin-form-js', $urlHelper->asset('/asset/admin_form.js'), array('jquery'), \AFormsEatsWrap::VERSION);
wp_localize_script('aforms-eats-admin-form-js', 'wqData', $output);
//wp_enqueue_style('material-icons', 'https://fonts.googleapis.com/icon?family=Material+Icons');
wp_enqueue_style('aforms-eats-admin-css', $urlHelper->asset('/asset/admin.css'), array(), \AFormsEatsWrap::VERSION);
wp_enqueue_media();

?>
<?php
/*
 * Some theme intrusively embed own contents into admin pages. 
 * That uses .wp-header-end as an installation marker, so.
 */
?>
<?php
/*
 * parcelがmaterial-icons.cssの中にあるurl()を適切に処理してくれないのでここに書く。
 */
$fontBase = $urlHelper->asset('/asset/');
?>
<style>
@font-face {
  font-family: 'Material Icons';
  font-style: normal;
  font-weight: 400;
  src: url(<?= $fontBase ?>MaterialIcons-Regular.eot); /* For IE6-8 */
  src: local('Material Icons'),
       local('MaterialIcons-Regular'),
       url(<?= $fontBase ?>MaterialIcons-Regular.woff2) format('woff2'),
       url(<?= $fontBase ?>MaterialIcons-Regular.woff) format('woff'),
       url(<?= $fontBase ?>MaterialIcons-Regular.ttf) format('truetype');
}

.material-icons {
  font-family: 'Material Icons';
  font-weight: normal;
  font-style: normal;
  font-size: 24px;  /* Preferred icon size */
  display: inline-block;
  line-height: 1;
  text-transform: none;
  letter-spacing: normal;
  word-wrap: normal;
  white-space: nowrap;
  direction: ltr;

  /* Support for all WebKit browsers. */
  -webkit-font-smoothing: antialiased;
  /* Support for Safari and Chrome. */
  text-rendering: optimizeLegibility;

  /* Support for Firefox. */
  -moz-osx-font-smoothing: grayscale;

  /* Support for IE. */
  font-feature-settings: 'liga';
}
</style>
<div class="wrap">
<div class="wq-TitleBar">
  <h1 class="wp-heading-inline"><?= esc_html($output['catalog']['Form Details']) ?></h1>
  <div class="wq--spacer"></div>
  <div class="wq--link"><a id="preview-link" href="<?= esc_url(str_replace('placeholder', $output['form']->id, $output['pvUrl'])) ?>" target="_blank"><?= esc_html($output['catalog']['Preview']) ?></a></div>
  <?php if ($resolve('session')->canWriteForm($output['form'])): ?><button id="save-button" class="button button-primary button-large"><?= esc_html($output['catalog']['Save']) ?></button><?php endif; ?>
</div>
<hr class="wp-header-end" />
<div id="root"></div>
</div>