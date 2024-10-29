<?php

namespace AFormsEats\Infra;

class WordMapper 
{
    const KEY = 'wp_aforms_eats_word_settings';

    protected $wpdb;
    protected $cache;

    public function __construct($wpdb) 
    {
        $this->wpdb = $wpdb;
        $this->cache = null;
    }

    protected function getDefaultAsArray() 
    {
        return array(
            'No' => __('No', 'aforms-eats'),  // #
            'Category' => __('Category', 'aforms-eats'),  // カテゴリー
            'Entry' => __('Entry', 'aforms-eats'),  // 項目
            'Unit Price' => __('Unit Price', 'aforms-eats'),  // 単価
            'Quantity' => __('Quantity', 'aforms-eats'),  // 数量
            'Price' => __('Price', 'aforms-eats'),  // 金額
            'Subtotal' => __('Subtotal', 'aforms-eats'),  // 小計
            'Tax' => __('Tax', 'aforms-eats'),  // 消費税
            'Total' => __('Total', 'aforms-eats'),  // 合計, 
            'required' => __('required', 'aforms-eats'),  // 必須
            'Input here' => __('Input here', 'aforms-eats'),  // 入力してください
            'Invalid' => __('Invalid', 'aforms-eats'),  // 不正です
            'Check here' => __('Check here', 'aforms-eats'),  // チェックを入れてください
            'Select here' => __('Select here', 'aforms-eats'),  // 選んでください
            'Repeat here' => __('Repeat here', 'aforms-eats'),  // 同じ文字を入力してください
            'Zip' => __('Zip', 'aforms-eats'),  // 郵便番号
            'To Confirmation Screen' => __('To Confirmation Screen', 'aforms-eats'),  // 確認画面へ
            'Your Name' => __('Your Name', 'aforms-eats'), 
            'First Name' => __('First Name', 'aforms-eats'), 
            'Last Name' => __('Last Name', 'aforms-eats'), 
            'info@example.com' => __('info@example.com', 'aforms-eats'), 
            'Confirm again' => __('Confirm again', 'aforms-eats'), 
            '03-1111-2222' => __('03-1111-2222', 'aforms-eats'), 
            '000-0000' => __('000-0000', 'aforms-eats'), 
            'Tokyo' => __('Tokyo', 'aforms-eats'), 
            'Chiyoda-ku' => __('Chiyoda-ku', 'aforms-eats'), 
            '1-1-1, Chiyoda' => __('1-1-1, Chiyoda', 'aforms-eats'), 
            'Chiyoda mansion 8F' => __('Chiyoda mansion 8F', 'aforms-eats'), 
            'Processing stopped due to preview mode.' => __('Processing stopped due to preview mode.', 'aforms-eats'), 
            'Submit' => __('Submit', 'aforms-eats'), 
            'Back' => __('Back', 'aforms-eats'), 
            'Please check your entry.' => __('Please check your entry.', 'aforms-eats'), 
            'Hide Monitor' => __('Hide Monitor', 'aforms-eats'), 
            'Show Monitor' => __('Show Monitor', 'aforms-eats'), 
            'Start Order' => __('Start Order', 'aforms-eats'), 
            'Close' => __('Close', 'aforms-eats'), 
            'Previous' => __('Previous', 'aforms-eats'), 
            'Next' => __('Next', 'aforms-eats'), 
            'There exists uninput item.' => __('There exists uninput item.', 'aforms-eats'), 
            'The form has been successfully submitted.' => __('The form has been successfully submitted.', 'aforms-eats'), 
            '^[0-9]{3}-?[0-9]{4}$' => __('^[0-9]{3}-?[0-9]{4}$', 'aforms-eats'), 
            'Checked' => __('Checked', 'aforms-eats'), 
            'Too small' => __('Too small', 'aforms-eats'), 
            'Too large' => __('Too large', 'aforms-eats'), 
            'Please select' => __('Please select', 'aforms-eats'), 
            'Input in Hiragana' => __('Input in Hiragana', 'aforms-eats'), 
            'Input in Katakana' => __('Input in Katakana', 'aforms-eats'), 
            'optional' => __('optional', 'aforms-eats'), 
            'SALE' => __('SALE', 'aforms-eats'), 
            'RECOMMENDED' => __('RECOMMENDED', 'aforms-eats'), 
            'Operation' => __('Operation', 'aforms-eats'), 
            '-' => __('-', 'aforms-eats'), 
            '+' => __('+', 'aforms-eats'), 
            'Shopping Cart' => __('Shopping Cart', 'aforms-eats'), 
            'Tax Class' => __('Tax Class', 'aforms-eats'), 
            '(%s%% applied)' => __('(%s%% applied)', 'aforms-eats'), 
            'Tax (%s%%)' => __('Tax (%s%%)', 'aforms-eats'), 
            '(common %s%% applied)' => __('(common %s%% applied)', 'aforms-eats'), 
            'Tax (common %s%%)' => __('Tax (common %s%%)', 'aforms-eats'), 
            '%s (x %s) %s %s' => __('%s (x %s) %s %s', 'aforms-eats'), 
            '%s: %s' => __('%s: %s', 'aforms-eats'), 
            "== %s ==\n%s" => __("== %s ==\n%s", 'aforms-eats'), 
            ', ' => __(', ', 'aforms-eats'), 
            '.' => __('.', 'aforms-eats'), 
            ',' => __(',', 'aforms-eats'), 
            '$%s' => __('$%s', 'aforms-eats'), 
            'Sold Out' => __('Sold Out', 'aforms-eats')
        );
    }

    public function merge($base, $ext) 
    {
        $rv = array();
        foreach ($base as $key => $val) {
            $rv[$key] = isset($ext[$key]) ? $ext[$key] : $val;
        }
        return $rv;
    }

    public function load() 
    {
        if ($this->cache) {
            return $this->cache;
        }

        $default = $this->getDefaultAsArray();
        $word0 = get_option(self::KEY, '{}');
        $word = json_decode($word0, true);
        $this->cache = $this->merge($default, $word);
        return $this->cache;
    }

    public function save($word) 
    {
        update_option(self::KEY, json_encode($word));
    }
}