<?php

namespace AFormsEats\Infra;

class RuleMapper 
{
    const KEY = 'wp_aforms_eats_settings';

    protected $wpdb;

    public function __construct($wpdb) 
    {
        $this->wpdb = $wpdb;
    }

    protected function getDefault() 
    {
        return json_encode((object)array(
            'taxIncluded' => false, 
            'taxRate' => 8, 
            'taxNormalizer' => 'trunc', 
            'taxPrecision' => 0
        ));
    }

    public function load() 
    {
        $rule0 = get_option(self::KEY, $this->getDefault());
        return json_decode($rule0, false);
    }

    public function save($rule) 
    {
        update_option(self::KEY, json_encode($rule));
    }
}