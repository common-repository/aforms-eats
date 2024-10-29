<?php

if ($status != 'SUCCESS') {
    echo "ERROR:".$status;
    return;
}

$options = $resolve('options');
$form = $output['form'];

$output['catalog'] = $options->extendword($resolve('word')->load(), $form);
$output['rule'] = $options->extendRule($resolve('rule')->load(), $form);
$output['behavior'] = $options->extendBehavior($resolve('behavior')->load(), $form);
$output['confirmUrl'] = $urlHelper->ajax('wqe-confirm');
$output['submitUrl'] = $urlHelper->ajax('wqe-order-new');
$output['noimageurl'] = $options->extendNoImageUrl($urlHelper->asset('/asset/noimage.png'), $form);


wp_enqueue_script('aforms-eats-front-js', $urlHelper->asset('/asset/front.js'), array('jquery'), \AFormsEatsWrap::VERSION);
wp_localize_script('aforms-eats-front-js', 'wqData', $output);

// enqueue a style for this form after those of themes.
wp_enqueue_style('dashicons');
$stylesheet = $options->extendStylesheetUrl($urlHelper->asset('/asset/front.css'), $form);
wp_enqueue_style('aforms-eats-front-css', $stylesheet, array('dashicons'), \AFormsEatsWrap::VERSION);

?>
<div id="aforms-eats-form-<?= $form->id ?>"></div>