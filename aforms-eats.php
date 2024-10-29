<?php
/*
  Plugin Name: AForms Eats
  Plugin URI:https://a-forms.com/en/eats/
  Description: Order form builder for restaurants. If you have any problems or feature requests for this plugin, also <strong>requests for installation or customization</strong>, please feel free to <a href="https://a-forms.com/en/contact/" target="_blank">contact us</a>.
  Version: 1.3.1
  Author: Vivid Colors, inc.
  Author URI: https://www.vividcolors.co.jp/
  License: MIT
  Text Domain: aforms-eats
  Domain Path: /src/template/
 */

require __DIR__.'/vendor/autoload.php';

AFormsEatsWrap::start();

class AFormsEatsWrap 
{
    const VERSION = '1.3.1';
    const NEACAPSYS_KEY = 'wp_aforms_eats_newcapsys';

    protected $plugin;
    static $instance = null;

    public function __construct($plugin) 
    {
        $this->plugin = $plugin;
    }

    public static function start() 
    {
        $obj = new self(\AFormsEats\Shell\Dispatcher::newInstance(array('AFormsEats\Config')));
        self::$instance = $obj;

        add_action('init', array($obj, 'init'));
        add_filter('load_textdomain_mofile', array($obj, 'modifyTextDomain'), 10, 2);
        add_action('wp_enqueue_scripts', array($obj, 'prepareScripts'));
        add_action('admin_enqueue_scripts', array($obj, 'prepareAdminScripts'));
        register_uninstall_hook(__FILE__, array('AFormsEatsWrap', 'uninstall'));
    }

    public function init() 
    {
        $this->addEntryPoints();

        if (is_admin() && is_user_logged_in()) {
            add_action('admin_menu', array($this, 'registerAdminPages'));
            $this->registerAdminAjaxes();
            add_filter('map_meta_cap', array($this, 'mapMetaCap'), 1, 4);
        }
        $this->registerShortcodes();
        $this->registerAjaxes();
        load_plugin_textdomain('aforms-eats', false, dirname(plugin_basename(__FILE__)) . '/src/template');
    }

    public static function install($networkwide) 
    {
        // fail if on a network activation.
        $errorMarker = get_option('aforms-eats-activation-error', false);
        if ($networkwide || $errorMarker) {
            if ($networkwide && ! $errorMarker) {
                // Called first time. Registers the marker.
                update_option('aforms-eats-activation-error', true);
            } else if (! $networkwide && $errorMarker) {
                // Called again with $networkwide === false. Deletes the marker.
                delete_option('aforms-eats-activation-error');
            } else {  // $networkwide && $errorMarker
                // This should not be the case. As a backup, restore the state to the initial.
                delete_option('aforms-eats-activation-error');
            }
            die('AForms Eats cannot be network activated. Instead, please activate it on a site-by-site basis.');
        }

        self::$instance->plugin->install();
    }

    public function modifyTextDomain($mofile, $domain) 
    {
        $lang = get_locale();
        if ($domain == 'aforms-eats' && $lang == 'ja') {
            $mofile = WP_PLUGIN_DIR . '/'. dirname(plugin_basename(__FILE__)) . '/src/template/aforms-eats-' . $lang . '.mo';
        }
        return $mofile;
    }

    public function prepareScripts() 
    {
        //$style = $this->plugin->getOptions()->getSiteStylesheet();
        //wp_register_style('front-css', $style);
        
        $script = plugins_url('', __FILE__).'/asset/front.js';
        wp_register_script('aforms-eats-front-js', $script, array('jquery'), self::VERSION, true);
    }

    public function prepareAdminScripts() 
    {
        // this is required for preview forms.
        $this->prepareScripts();
    }

    public function addEntryPoints() 
    {
        $p = $this->plugin;
        $p->addAdmin('wqe-form','formList',null,'AFormsEats\App\Admin\FormList');
        $p->addAdmin('wqe-form','form',array('edit','int'),'AFormsEats\App\Admin\FormRef');
        $p->addAdmin('wqe-form','form',array('new','int'),'AFormsEats\App\Admin\FormRef');
        $p->addAjax( 'wqe-form-set',array('edit','int'),'AFormsEats\App\Admin\FormSet');
        $p->addAjax( 'wqe-form-del',array('del','int'),'AFormsEats\App\Admin\FormDel');
        $p->addAjax( 'wqe-form-dup',array('dup','int'),'AFormsEats\App\Admin\FormDup');
        $p->addAdmin('wqe-form','preview',array('preview','int'), 'AFormsEats\App\Admin\Preview');
        $p->addAdmin('wqe-settings','settings',null,'AFormsEats\App\Admin\SettingsRef');
        $p->addAjax( 'wqe-settings-set',null,'AFormsEats\App\Admin\SettingsSet');
        $p->addAdmin('wqe-order','orderList',null,'AFormsEats\App\Admin\OrderList');
        $p->addAjax( 'wqe-order',array('int'),'AFormsEats\App\Admin\OrderListPage');
        $p->addAjax( 'wqe-order-del',array('del','int'),'AFormsEats\App\Admin\OrderDel');
        $p->addAjax( 'wqe-capsys-set',null,'AFormsEats\App\Admin\CapSysSet');
        $p->addShort('aforms-eats-form','form',null,'AFormsEats\App\Front\FormRef');
        $p->addAjax( 'wqe-confirm',null,'AFormsEats\App\Front\Confirm');
        $p->addAjax( 'wqe-order-new',null,'AFormsEats\App\Front\OrderNew');
    }

    public function registerAdminPages() 
    {
        $newcapsys = get_option(self::NEACAPSYS_KEY, false);
        if ($newcapsys) {
            add_menu_page(__('Forms', 'aforms-eats'), __('Forms', 'aforms-eats'), 'aformseats_read_forms', 'wqe-form', array($this->plugin, 'adminPage'), 'dashicons-carrot', 57.00002);
            add_menu_page(__('Order List', 'aforms-eats'), __('Orders', 'aforms-eats'), 'aformseats_read_orders', 'wqe-order', array($this->plugin, 'adminPage'), 'dashicons-carrot', 58.201);
            add_submenu_page('options-general.php', __('Form Settings', 'aforms-eats'), __('Form Settings', 'aforms-eats'), 'manage_options', 'wqe-settings', array($this->plugin, 'adminPage'));
        } else {
            add_menu_page(__('Forms', 'aforms-eats'), __('Forms', 'aforms-eats'), 'read', 'wqe-form', array($this->plugin, 'adminPage'), 'dashicons-carrot', 58.00002);
            add_submenu_page('wqe-form', __('Order List', 'aforms-eats'), __('Orders', 'aforms-eats'), 'read', 'wqe-order', array($this->plugin, 'adminPage'));
            add_submenu_page('wqe-form', __('Form Settings', 'aforms-eats'), __('Form Settings', 'aforms-eats'), 'read', 'wqe-settings', array($this->plugin, 'adminPage'));
        }
    }
    
    public function registerShortcodes() 
    {
        add_shortcode('aforms-eats-form', array($this->plugin, 'shortcode'));
    }
    
    public function registerAjaxes() 
    {
        add_action('wp_ajax_nopriv_wqe-confirm', array($this->plugin, 'ajax'));
        add_action('wp_ajax_nopriv_wqe-order-new', array($this->plugin, 'ajax'));
    }

    public function registerAdminAjaxes() 
    {
        add_action('wp_ajax_wqe-settings-set', array($this->plugin, 'ajax'));
        add_action('wp_ajax_wqe-form-set', array($this->plugin, 'ajax'));
        add_action('wp_ajax_wqe-form-del', array($this->plugin, 'ajax'));
        add_action('wp_ajax_wqe-form-dup', array($this->plugin, 'ajax'));
        add_action('wp_ajax_wqe-order', array($this->plugin, 'ajax'));
        add_action('wp_ajax_wqe-order-del', array($this->plugin, 'ajax'));
        add_action('wp_ajax_wqe-capsys-set', array($this->plugin, 'ajax'));
        add_action('wp_ajax_wqe-confirm', array($this->plugin, 'ajax'));
        add_action('wp_ajax_wqe-order-new', array($this->plugin, 'ajax'));
    }

    public function mapMetaCap($caps, $cap, $userId, $args) 
    {
        return $this->plugin->getSession()->mapMetaCap($caps, $cap, $userId, $args);
    }

    public static function uninstall() 
    {
        global $wpdb;

        // drop form table
        $table = $wpdb->prefix . 'wqeforms';
        $sql = "DROP TABLE $table";
        $wpdb->query($sql);
        
        // drop order table
        $table = $wpdb->prefix . 'wqeorders';
        $sql = "DROP TABLE $table";
        $wpdb->query($sql);

        // TODO: uninstall .mo, .po
    }
}

register_activation_hook(__FILE__, array('AFormsEatsWrap', 'install'));