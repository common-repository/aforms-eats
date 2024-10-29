<?php

namespace AFormsEats\Shell;

use Aura\Payload\Payload;
use Aura\Di\ContainerBuilder;

class Dispatcher 
{
    protected $container;
    protected $urlHelper;
    protected $adminPages;  // page -> {pointer, types, template}[]
    protected $shortcodes;  // name -> {pointer, types, template}[]
    protected $ajaxes;      // action -> {pointer, types}[]
    protected $raws;        // name -> {pointer, types, template}[]
    protected $atts;  // Temporarily stored atts of shortcode-calling
    protected $query;  // Temporarily stored query string of raw-calling

    public static function newInstance($configs) 
    {
        $builder = new ContainerBuilder();
        $container = $builder->newConfiguredInstance($configs);
        return new Dispatcher($container, $container->get('urlHelper'));
    }

    public function __construct($container, $urlHelper) 
    {
        $this->container = $container;
        $this->urlHelper = $urlHelper;
        $this->adminPages = array();
        $this->shortcodes = array();
        $this->ajaxes = array();
        $this->raws = array();
        $this->atts = null;
        $this->query = null;
    }

    public function getOptions() 
    {
        return $this->container->get('options');
    }

    public function getSession() 
    {
        return $this->container->get('session');
    }

    public function addAdmin($page, $template, $types = null, $pointer = null) 
    {
        if (is_null($types)) {
            $types = array();
        }
        if (! isset($this->adminPages[$page])) {
            $this->adminPages[$page] = array();
        }
        $this->adminPages[$page][] = (object)array('pointer' => $pointer, 'types' => $types, 'template' => 'admin/'.$template);
    }

    public function addShort($name, $template, $types = null, $pointer = null) 
    {
        if (is_null($types)) {
            $types = array();
        }
        if (! isset($this->shortcodes[$name])) {
            $this->shortcodes[$name] = array();
        }
        $this->shortcodes[$name][] = (object)array('pointer' => $pointer, 'template' => 'front/'.$template, 'types' => $types);
    }

    public function addAjax($action, $types = null, $pointer = null) 
    {
        if (is_null($types)) {
            $types = array();
        }
        if (! isset($this->ajaxes[$action])) {
            $this->ajaxes[$action] = array();
        }
        $this->ajaxes[$action][] = (object)array('pointer' => $pointer, 'types' => $types);
    }

    public function addRaw($name, $template, $types = null, $pointer = null) 
    {
        if (is_null($types)) {
            $types = array();
        }
        if (! isset($this->raws[$name])) {
            $this->raws[$name] = array();
        }
        $this->raws[$name][] = (object)array('pointer' => $pointer, 'template' => 'front/'.$template, 'types' => $types);
    }

    public function install() 
    {
        $args = array();
        return $this->call('AFormsEats\App\Admin\Install', $args, 'empty');
    }

    protected function sanitizeByKses($any) 
    {
        if (is_array($any)) {
            $len = count($any);
            for ($i = 0; $i < $len; $i++) {
                $any[$i] = $this->sanitizeByKses($any[$i]);
            }
            return $any;
        } else if (is_object($any)) {
            $copy = new \stdClass();
            foreach (get_object_vars($any) as $p => $v) {
                $p = wp_kses_post($p);
                $copy->{$p} = $this->sanitizeByKses($v);
            }
            return $copy;
        } else if (is_string($any)) {
            return wp_kses_post($any);
        } else {
            // null, bool, number
            return $any;
        }
    }

    protected function call($pointer, $params, $callType) 
    {
        if (! $pointer) {
            return null;
        }

        // --------
        // step1. Retrieve input
        if ($callType == 'shortcode') {
            $atts = $this->atts;
            unset($atts['path']);
            $input = (object)$atts;
        } else if ($callType == 'adminPage') {
            $input = null;
        } else if ($callType == 'ajax') {
            // Retrieved input data will be passed as is to __invoke() of App\* classes.
            // No sanitization/validation is executed here.
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'POST': 
                case 'PUT': 
                case 'PATCH': 
                    if ($_SERVER['CONTENT_TYPE'] == 'application/json') {
                        $input = json_decode(file_get_contents('php://input'));
                    } else {
                        $input = null;
                    }
                    break;
                case 'GET': 
                case 'HEAD': 
                case 'DELETE': 
                    $arr = $_GET;
                    unset($arr['action']);
                    unset($arr['path']);
                    $input = json_decode(json_encode($arr), false);
                    break;
                default: 
                    $input = null;
            }
        } else if ($callType == 'raw') {
            $input = json_decode(json_encode($_GET), false);
        } else if ($callType == 'empty') {
            $input = null;
        } else {
            $this->abort('invalid callType');
        }

        // --------
        // step2. Prepare parameters
        // $input is the only dirty data that goes out to the App layer.
        // This dirty parameter is positioned second from the back.
        $args = $params;
        $args[] = $this->sanitizeByKses($input);
        $args[] = new Payload();

        // --------
        // step3. Call
        $object = $this->container->newInstance($pointer);
        return call_user_func_array($object, $args);
    }

    // browser GET.  there params, no inputs, outputs html
    public function adminPage() 
    {
        $page = $_REQUEST['page'];
        if (! isset($this->adminPages[$page])) {  // Validating $page is executed here.
            $this->abort('No matching page');
        }
        list($adminPage, $args) = $this->route($this->adminPages[$page], 'adminPage');

        $payload = $this->call($adminPage->pointer, $args, 'adminPage');

        $r = $this->container->newInstance('AFormsEats\Shell\HtmlResponder');
        $r->setEcho(true);
        $r($adminPage->template, $payload);
    }

    // browser GET.  there params, no inputs, outputs any
    public function raw() 
    {
        foreach ($this->raws as $name => $raws) {
            $val = get_query_var($name);
            if (! $val) continue;

            $this->query = $val;
            list($raw, $args) = $this->route($raws, 'raw');

            $payload = $this->call($raw->pointer, $args, 'raw');

            $path = dirname(dirname(__FILE__)).'/template/'.$raw->template.'.php';
            include($path);
            exit;
        }
    }

    // browser GET.  there params, there inputs, outputs html
    public function shortcode($atts, $content, $name) 
    {
        $this->atts = $atts;
        list($shortcode, $args) = $this->route($this->shortcodes[$name], 'shortcode');

        $payload = $this->call($shortcode->pointer, $args, 'shortcode');

        $r = $this->container->newInstance('AFormsEats\Shell\HtmlResponder');
        $r->setEcho(false);
        $out = $r($shortcode->template, $payload);
        return $out;
    }

    // ajax.  there params, there inputs, outputs json
    public function ajax() 
    {
        $action = $_REQUEST['action'];
        if (! isset($this->ajaxes[$action])) {  // Validating $action is executed here.
            $this->abort('No matching action');
        }
        check_ajax_referer($action, $this->urlHelper->getNonceName());
        list($ajax, $args) = $this->route($this->ajaxes[$action], 'ajax');

        $payload = $this->call($ajax->pointer, $args, 'ajax');

        $r = $this->container->newInstance('AFormsEats\Shell\JsonResponder');
        $r->setEcho(true);
        $r($payload);
    }

    protected function route($specs, $pathType) 
    {
        foreach ($specs as $spec) {
            $params = $this->matchPath($spec->types, $pathType);
            if (! is_null($params)) {
                return array($spec, $params);
            }
        }
        $this->abort('no matching handler');
    }

    protected function matchPath($types, $pathType) 
    {
        if ($pathType == 'shortcode') {
            $path = isset($this->atts['path']) ? $this->atts['path'] : null;
        } else if ($pathType == 'adminPage' || $pathType == 'ajax') {
            $path = isset($_REQUEST['path']) ? $_REQUEST['path'] : null;
        } else if ($pathType == 'raw') {
            $path = $this->query;
        } else {
            $path = null;
        }

        if (! $path) {
            $params = array();
        } else {
            $params = explode('_', $path);
        }
        if (count($params) != count($types)) {
            // parameter count mismatch
            return null;
        }
        $len = count($params);
        for ($i = 0; $i < $len; $i++) {
            switch ($types[$i]) {
                case 'int': 
                    $params[$i] = intval($params[$i]);
                    break;
                case 'bool': 
                    $params[$i] = ($params[$i] == 'T') ? true : false;
                    break;
                default: 
                    if ($params[$i] != $types[$i]) {
                        // keyword mismatch
                        return null;
                    }
                    break;
            }
        }
        return $params;
    }

    protected function abort($message) 
    {
        echo "ERROR: ".$message;
        wp_die();
    }
}