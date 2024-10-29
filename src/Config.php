<?php

namespace AFormsEats;

use Aura\Di\Container;
use Aura\Di\ContainerConfig;

class Config extends ContainerConfig
{
    public function define(Container $di) 
    {
        global $wpdb;
        $tpldir = basename(dirname(dirname(__FILE__))). '/src/template';

        // Responders
        $di->params['AFormsEats\Shell\HtmlResponder'][0] = $di->lazyNew('tyam\bamboo\Engine');
        $di->params['AFormsEats\Shell\HtmlResponder'][1] = $di->lazyGet('urlHelper');
        $di->params['AFormsEats\Shell\HtmlResponder'][2] = $di->lazyGet('resolver');
        $di->params['AFormsEats\Shell\HtmlResponder'][3] = $tpldir;
        
        // Bamboo
        $di->params['tyam\bamboo\Engine'][0] = [__DIR__.'/template'];

        // UrlHelper
        $di->set('urlHelper', $di->lazyNew('AFormsEats\Shell\UrlHelper'));
        $di->params['AFormsEats\Shell\UrlHelper'][0] = 'wq_nonce';
        $di->params['AFormsEats\Shell\UrlHelper'][1] = plugins_url('', dirname(__FILE__));

        // resolver
        $di->set('resolver', $di->lazy(array($di, 'newResolutionHelper')));

        // Validator
        $di->set('validator', $di->lazyNew('JsonSchema\Validator'));

        // Session
        $di->set('session', $di->lazyNew('AFormsEats\Infra\WpSession'));

        // options
        $di->set('options', $di->lazyNew('AFormsEats\Infra\WpOptions'));
        $di->params['AFormsEats\Infra\WpOptions'][0] = $tpldir;

        // scorer which always be passed.
        $di->params['AFormsEats\Infra\ConstScorer'][0] = 1.0;

        // Infra
        $di->set('rule', $di->lazyNew('AFormsEats\Infra\RuleMapper'));
        $di->params['AFormsEats\Infra\RuleMapper'][0] = $wpdb;
        $di->set('word', $di->lazyNew('AFormsEats\Infra\WordMapper'));
        $di->params['AFormsEats\Infra\WordMapper'][0] = $wpdb;
        $di->set('behavior', $di->lazyNew('AFormsEats\Infra\BehaviorMapper'));
        $di->params['AFormsEats\Infra\BehaviorMapper'][0] = $wpdb;
        $di->set('form', $di->lazyNew('AFormsEats\Infra\FormMapper'));
        $di->params['AFormsEats\Infra\FormMapper'][0] = $wpdb;
        $di->set('order', $di->lazyNew('AFormsEats\Infra\OrderMapper'));
        $di->params['AFormsEats\Infra\OrderMapper'][0] = $wpdb;
        $di->params['AFormsEats\Infra\OrderMapper'][1] = $di->lazyGet('rule');
        $di->params['AFormsEats\Infra\OrderMapper'][2] = $di->lazyGet('word');

        // App
        $di->params['AFormsEats\App\Admin\Install'] = array(
            $di->lazyNew('AFormsEats\Infra\FormMapper'), 
            $di->lazyNew('AFormsEats\Infra\OrderMapper'), 
            $di->lazyGet('session'), 
            $di->lazyGet('options')
        );
        $di->params['AFormsEats\App\Admin\SettingsRef']= array(
            $di->lazyGet('rule'), 
            $di->lazyGet('word'), 
            $di->lazyGet('behavior'), 
            $di->lazyGet('session')
        );
        $di->params['AFormsEats\App\Admin\SettingsSet']= array(
            $di->lazyGet('rule'), 
            $di->lazyGet('word'), 
            $di->lazyGet('behavior'), 
            $di->lazyGet('validator'), 
            $di->lazyGet('session')
        );
        $di->params['AFormsEats\App\Admin\FormList'] = array(
            $di->lazyNew('AFormsEats\Infra\FormMapper'), 
            $di->lazyGet('session')
        );
        $di->params['AFormsEats\App\Admin\FormRef'] = array(
            $di->lazyNew('AFormsEats\Infra\FormMapper'), 
            $di->lazyGet('session'), 
            $di->lazyGet('options')
        );
        $di->params['AFormsEats\App\Admin\FormSet'] = array(
            $di->lazyNew('AFormsEats\Infra\FormMapper'), 
            $di->lazyGet('session'), 
            $di->lazyGet('validator')
        );
        $di->params['AFormsEats\App\Admin\FormDel'] = array(
            $di->lazyNew('AFormsEats\Infra\FormMapper'), 
            $di->lazyGet('session')
        );
        $di->params['AFormsEats\App\Admin\FormDup'] = array(
            $di->lazyNew('AFormsEats\Infra\FormMapper'), 
            $di->lazyGet('session'), 
            $di->lazyGet('options')
        );
        $di->params['AFormsEats\App\Admin\OrderList'] = array(
            $di->lazyGet('session'), 
            $di->lazyNew('AFormsEats\Infra\OrderMapper'), 
            $di->lazyGet('validator'), 
            $di->lazyNew('AFormsEats\Infra\FormMapper')
        );
        $di->params['AFormsEats\App\Admin\OrderListPage'] = array(
            $di->lazyGet('session'), 
            $di->lazyNew('AFormsEats\Infra\OrderMapper'), 
            $di->lazyGet('validator'), 
            $di->lazyNew('AFormsEats\Infra\FormMapper')
        );
        $di->params['AFormsEats\App\Admin\OrderDel'] = array(
            $di->lazyNew('AFormsEats\Infra\OrderMapper'), 
            $di->lazyGet('session'), 
            $di->lazyNew('AFormsEats\Infra\FormMapper')
        );
        $di->params['AFormsEats\App\Admin\CapSysSet'] = array(
            $di->lazyGet('session')
        );

        $di->params['AFormsEats\App\Front\FormRef'] = array(
            $di->lazyNew('AFormsEats\Infra\FormMapper'), 
            $di->lazyGet('session')
        );
        $di->params['AFormsEats\App\Front\OrderNew'] = array(
            $di->lazyNew('AFormsEats\Infra\FormMapper'), 
            $di->LazyGet('rule'), 
            $di->lazyNew('AFormsEats\Infra\OrderMapper'), 
            $di->lazyNew('AFormsEats\Infra\WpMailer'), 
            $di->lazyGet('session'), 
            $di->lazyGet('options'), 
            //new \AFormsEats\Infra\ConstScorer(0.4), 
            $di->lazyNew('AFormsEats\Infra\GoogleScorer'), 
            $di->lazyGet('word')
        );
        $di->params['AFormsEats\App\Front\Confirm'] = array(
            $di->lazyNew('AFormsEats\Infra\FormMapper'), 
            $di->LazyGet('rule'), 
            $di->lazyGet('session'), 
            $di->lazyGet('options'), 
            $di->lazyNew('AFormsEats\Infra\ConstScorer'), 
            $di->lazyGet('word')
        );
    }
}