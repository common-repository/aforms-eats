<?php

function echo_capsys_enabler() 
{
  echo sprintf(
    __('%sMake the layout of the admin menu to the new specification (recommended).%s You can also revert to the original.', 'aforms-eats'), 
    "<a href='javascript:void(0);' id='capsys-switcher'>", 
    "</a>"
  );
}
function echo_capsys_disabler() 
{
  echo sprintf(
    __('%sRevert the arrangement of the admin menu to the old specification.%s Can be re-activated.', 'aforms-eats'), 
    "<a href='javascript:void(0);' id='capsys-switcher'>", 
    "</a>"
  );
}
if (get_option(\AFormsEatsWrap::NEACAPSYS_KEY, false)) {
  add_filter('admin_footer_text', 'echo_capsys_disabler');
  $capsysVal = 0;
  $capsysPpt = __('Revert the arrangement of the admin menu to the old specification. Are you sure?', 'aforms-eats');
  $capsysMsg = __('The arrangement of the admin menu has been reverted to the old specification. Please reload the page for it to reflect the change.', 'aforms-eats');
} else {
  add_filter('admin_footer_text', 'echo_capsys_enabler');
  $capsysVal = 1;
  $capsysPpt = __('Change the arrangement of the admin menu to the new specification. Are you sure?', 'aforms-eats');
  $capsysMsg = __('The admin menu is now arranged in a new way. Please reload the page for it to reflect the changes.', 'aforms-eats');
}
?>
<script>
function handleCapSys() {
  if (! confirm('<?= esc_html($capsysPpt) ?>')) return;
  jQuery.ajax({
    type: "post", 
    url: '<?= $urlHelper->ajax('wqe-capsys-set') ?>', 
    data: JSON.stringify(<?= $capsysVal ?>), 
    contentType: 'application/json',
    success: function () {
      alert('<?= esc_html($capsysMsg) ?>');
    }, 
    dataType: 'json'
  });
}
jQuery(function () {
  jQuery('#capsys-switcher').click(handleCapSys);
});
</script>