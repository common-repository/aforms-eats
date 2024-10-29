<?php

namespace AFormsEats\Infra;

class BehaviorMapper 
{
    const KEY = 'wp_aforms_eats_behavior_settings';

    protected $wpdb;

    public function __construct($wpdb) 
    {
        $this->wpdb = $wpdb;
    }

    protected function getDefault() 
    {
        return json_encode((object)array(
            'smoothScroll' => true, 
            'scrollOnGroupSelection' => true
        ));
    }

    public function load() 
    {
        $rule0 = get_option(self::KEY, $this->getDefault());
        $rule = json_decode($rule0, false);

        // migration
        if (! property_exists($rule, 'scrollOnGroupSelection')) {
            $rule->scrollOnGroupSelection = true;
        }

        return $rule;
    }

    public function save($rule) 
    {
        update_option(self::KEY, json_encode($rule));
    }
}