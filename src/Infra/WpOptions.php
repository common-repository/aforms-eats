<?php

namespace AFormsEats\Infra;

use \Toml;

class WpOptions 
{
    protected $tpldir;
    protected $catalogLoaded = false;
    protected $siteIni = null;

    public function __construct($tpldir) 
    {
        $this->tpldir = $tpldir;
    }

    public function now() 
    {
        return (int)current_time('timestamp', 0);
    }

    protected function loadCatalog() 
    {
        //load_plugin_textdomain('aforms', false, $this->tpldir);
        $this->catalogLoaded = true;
    }

    public function getDefaultMail($formId)
    {
        $mail = new \stdClass();
        $mail->subject = $this->translate('Thank you for your order', $formId);
        $mail->fromAddress = get_option('admin_email');
        $mail->fromName = get_option('blogname');
        $mail->alignReturnPath = false;
        $mail->notifyTo = '';
        $mail->textBody = $this->translate('Thank you for your order.', $formId);
        return $mail;
    }

    public function getDefaultFormTitle($formId) 
    {
        return $this->translate('New Form', $formId);
    }

    public function translate($str, $formId) 
    {
        return __($str, 'aforms-eats');
    }

    public function extendRule($rule, $form) 
    {
        return apply_filters('aforms_eats_load_rule', $rule, $form);
    }

    public function extendWord($word, $form) 
    {
        return apply_filters('aforms_eats_load_word', $word, $form);
    }

    public function extendBehavior($behavior, $form) 
    {
        return apply_filters('aforms_eats_load_behavior', $behavior, $form);
    }

    public function extendStylesheetUrl($url, $form) 
    {
        return apply_filters('aforms_eats_get_stylesheet', $url, $form);
    }

    public function extendNoImageUrl($url, $form) 
    {
        return apply_filters('aforms_eats_get_noimage', $url, $form);
    }

    public function extendThanksMail($mail, $form) 
    {
        return apply_filters('aforms_eats_compose_thanks_mail', $mail, $form);
    }

    public function extendReportMail($mail, $form) 
    {
        return apply_filters('aforms_eats_compose_report_mail', $mail, $form);
    }
}