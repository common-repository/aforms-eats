<?php

namespace AFormsEats\Domain;

class InputProcessor 
{
    use Lib;
    
    const EMAIL_PATTERN = '/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/';
    const TEL_PATTERN = '/^[0-9-]+$/';

    protected $scorer;
    protected $options;
    protected $rule;
    protected $word;

    public function __construct($scorer, $options, $rule, $word) 
    {
        $this->scorer = $scorer;
        $this->options = $options;
        $this->rule = $rule;
        $this->word = $word;
    }

    protected function error($message) 
    {
        // TODO: throw domain exception
        throw new \RuntimeException($message);
    }

    protected function notAuthorized($score, $token)
    {
        throw new OrderException('score: '.$score.' '.$token);
    }

    protected function subsetOf($set, $target) 
    {
        foreach ($target as $name => $value) {
            if (! property_exists($set, $name)) {
                return false;
            }
        }
        return true;
    }

    protected function union($that, $set) 
    {
        foreach ($set as $name => $value) {
            $that->{$name} = $value;
        }
    }

    protected function compare($price, $equation, $threshold) 
    {
        switch ($equation) {
            case 'equal': 
                return $price == $threshold;
            case 'notEqual': 
                return $price != $threshold;
            case 'greaterThan': 
                return $price > $threshold;
            case 'greaterEqual': 
                return $price >= $threshold;
            case 'lessThan': 
                return $price < $threshold;
            case 'lessEqual': 
                return $price <= $threshold;
        }
    }

    protected function compare2($price, $lower, $lowerIncluded, $higher, $higherIncluded) 
    {
        if (! is_null($lower)) {
            if ($lowerIncluded) {
                if ($price < $lower) {
                    return false;
                }
            } else {
                if ($price <= $lower) {
                    return false;
                }
            }
        }
        if (! is_null($higher)) {
            if ($higherIncluded) {
                if ($price > $higher) {
                    return false;
                }
            } else {
                if ($price >= $higher) {
                    return false;
                }
            }
        }
        return true;
    }

    protected function findQuantity($data, $qid) 
    {
        return ($qid == -1) ? 1 : $data->{$qid};
    }

    protected function findByProp($prop, $val, $arr) 
    {
        foreach ($arr as $el) {
            if ($el->{$prop} == $val) {
                return $el;
            }
        }
        return null;
    }

    protected function createDetail($category, $name, $quantity, $unitPrice, $taxRate = null) 
    {
        $detail = new \stdClass();
        $detail->category = $category;
        $detail->name = $name;
        $detail->quantity = $quantity;
        $detail->unitPrice = $unitPrice;
        $detail->taxRate = $taxRate;
        $detail->price = $this->normalizePrice($this->rule, $unitPrice * $quantity);
        return $detail;
    }

    public function calculateDetails($items, $data, $attrs) 
    {
        $labels = new \stdClass();
        $details = array();
        $total = 0;
        $refTotal = 0;

        foreach ($items as $item) {
            if ($item->type == 'Selector') {
                if (! property_exists($data, $item->id)) {
                    // the item is not selected
                    if (! $item->multiple) {
                        // that is bad
                        $this->error('item not selected: '.$item->id);
                    } else {
                        // that is ok. no details, no labels
                        continue;
                    }
                }
                $selectedOptions = $data->{$item->id};
                foreach ($item->options as $option) {
                    if (! property_exists($selectedOptions, $option->id)) {
                        continue;
                    }
                    if (! $this->subsetOf($labels, $option->depends)) {
                        // the option is selected, but its dependency is not met
                        $this->error('inconsistent selection: '.$option->id);
                    }
                    $this->union($labels, $option->labels);
                    $quantity = $this->findQuantity($data, $item->quantity);
                    $details[] = $this->createDetail($item->name, $option->name, $quantity, $option->price);
                    $total += $this->normalizePrice($this->rule, $option->price * $quantity);
                }
            } else if ($item->type == 'Group') {
                if (! property_exists($data, $item->id)) {
                    continue;
                }
                if (! $item->visible) {
                    $this->error('invisible group: '.$item->id);
                }
                $selectedProducts = $data->{$item->id};
                foreach ($item->products as $product) {
                    if (! property_exists($selectedProducts, $product->id)) {
                        continue;
                    }
                    if ($product->state != 'effective') {
                        $this->error('ineffective product: '.$product->id);
                    }
                    $quantity = $selectedProducts->{$product->id};
                    if (! is_int($quantity)) {
                        $this->error('invalid quantity: '.$product->id);
                    }
                    if ($quantity > 0) {
                        $details[] = $this->createDetail('', $product->name, $quantity, $product->price, $product->taxRate);
                        $nprice = $this->normalizePrice($this->rule, $product->price * $quantity);
                        $total += $nprice;
                        $refTotal += $nprice;
                    }
                }
            } else if ($item->type == 'Auto') {
                if (! $this->subsetOf($labels, $item->depends)) {
                    // auto is not available
                    continue;
                }
                $quantity = $this->findQuantity($data, $item->quantity);
                $details[] = $this->createDetail($item->category, $item->name, $quantity, $item->price, $item->taxRate);
                $nprice = $this->normalizePrice($this->rule, $item->price * $quantity);
                $total += $nprice;
                $refTotal += $nprice;
            } else if ($item->type == 'PriceChecker') {
                if (! $this->compare($total, $item->equation, $item->threshold)) {
                    // pricechecker is not available
                    continue;
                }
                $this->union($labels, $item->labels);
            } else if ($item->type == 'PriceWatcher') {
                if (! $this->compare2($total, $item->lower, $item->lowerIncluded, $item->higher, $item->higherIncluded)) {
                    // pricewatcher is not available
                    continue;
                }
                $this->union($labels, $item->labels);
            } else if ($item->type == 'Quantity') {
                if (! $this->subsetOf($labels, $item->depends)) {
                    // quantity is not available
                    continue;
                }
                $quantity = $data->{$item->id};
                if (! $item->allowFraction && ! is_int($quantity)) {
                    // quantity not-int
                    $this->error('quantity not-int: ', $item->id);
                }
                if ($item->minimum !== null && $item->minimum !== "" && $quantity < $item->minimum || 
                    $item->maximum !== null && $item->maximum !== "" && $quantity > $item->maximum) {
                    // quantity is out of range
                    $this->error('quantity out-of-range: ', $item->id);
                }
            } else if ($item->type == 'Stop') {
                if ($this->subsetOf($labels, $item->depends)) {
                    // stop
                    $this->error('stop: ', $item->id);
                }
            }
        }

        return array($labels, $details, $total);
    }

    public function checkAttrs($items, $data, $formId) 
    {
        $condition = new \stdClass();

        foreach ($items as $item) {
            $val = $data->{$item->id};
            if ($item->type == 'reCAPTCHA3') {
                $scorer = $this->scorer;
                $score = $scorer($val, $item->secretKey, $item->action);
                if ($score === false) {
                    $this->error('invalid captcha: '.$item->id);
                }
                if ($item->threshold2 > $score) {
                    $this->notAuthorized($score, $val);
                }
                if ($item->threshold1 > $score) {
                    $data->{$item->id} = sprintf('Soft-Pass (%01.1f)', $score);
                    $condition->softPass = true;
                } else {
                    $data->{$item->id} = sprintf('Pass (%01.1f)', $score);
                }
                continue;
            }
            if ($item->required && $val == "") {
                $this->error('required but empty: '.$item->id);
            } else if (! $item->required && $val == "") {
                continue;
            }
            switch ($item->type) {
                case "Name": 
                    // no extra validation
                    break;
                case "Email": 
                    if (! preg_match(self::EMAIL_PATTERN, $val)) {
                        $this->error('invalid email: '.$item->id);
                    }
                    break;
                case "Tel": 
                    // no localized validation because I am lazy.
                    if (! preg_match(self::TEL_PATTERN, $val)) {
                        $this->error('invalid tel: '.$item->id);
                    }
                    break;
                case "Address": 
                    // no extra validation
                    // no zip validation because I am lazy.
                    break;
                case "Checkbox": 
                    if ($val != $this->word['Checked']) {
                        $this->error('invalid checkbox: '.$item->id);
                    }
                    break;
                case "Radio": 
                    if (! in_array($val, $item->options)) {
                        $this->error('invalid radio: '.$item->id);
                    }
                    break;
                case "Dropdown": 
                    if (! in_array($val, $item->options)) {
                        $this->error('invalid dropdown: '.$item->id);
                    }
                    break;
                case 'MultiCheckbox': 
                    foreach ($val as $v) {
                        if (! in_array($v, $item->options)) {
                            $this->error('invalid multicheckbox: '.$item->id);
                        }
                    }
                    break;
                case "Text": 
                    // no extra validation
                    break;
            }
        }
        return $condition;
    }

    public function calculateAttrs($items, $data, $formId) 
    {
        $attrs = array();

        foreach ($items as $item) {
            $attr = new \stdClass();
            $attr->type = $item->type;
            if ($item->type == 'reCAPTCHA3') {
                $attr->name = $this->options->translate('reCAPTCHA Result', $formId);
            } else {
                $attr->name = $item->name;
            }
            $attr->value = $data->{$item->id};
            $attrs[] = $attr;
        }

        return $attrs;
    }

    protected function fillTotal($order, $details) 
    {
        $subtotal = 0;
        $subtotals = array();

        foreach ($details as $detail) {
            $price = $detail->price;
            $subtotal += $price;
            if (! $this->rule->taxIncluded) {
                $key = $detail->taxRate === null ? "" : "".$detail->taxRate;
                if (isset($subtotals[$key])) {
                    $subtotals[$key] += $price;
                } else {
                    $subtotals[$key] = $price;
                }
            }
        }
        
        if ($this->rule->taxIncluded) {
            $order->total = $subtotal;
        } else {
            $taxes = array();
            $total = $subtotal;
            foreach ($subtotals as $key => $st) {
                $taxRate = $key === "" ? $this->rule->taxRate : $key;
                $tax = $this->normalizePrice($this->rule, $st * $taxRate * 0.01);
                $taxes[$key] = $tax;
                $total += $tax;
            }
            
            $order->subtotal = $subtotal;
            $order->defaultTaxRate = $this->rule->taxRate;
            $order->taxes = $taxes;
            $order->total = $total;
        }

        return $order;
    }

    protected function fillCurrency($order) 
    {
        list($pricePrefix, $priceSuffix) = explode('%s', $this->word['$%s']);
        $order->currency = (object)array(
            'taxPrecision' => $this->rule->taxPrecision, 
            'pricePrefix' => $pricePrefix, 
            'priceSuffix' => $priceSuffix, 
            'decPoint' => $this->word['.'], 
            'thousandsSep' => $this->word[',']
        );
    }

    public function __invoke($form, $data) 
    {
        // one-path validation and calculation
        list($labels, $details, $total) = $this->calculateDetails($form->detailItems, $data->details, $data->attrs);

        // validation
        $condition = $this->checkAttrs($form->attrItems, $data->attrs, $form->id);

        // create order
        $attrs = $this->calculateAttrs($form->attrItems, $data->attrs, $form->id);

        $order = new \stdClass();
        $order->id = null;
        $order->formId = $form->id;
        $order->formTitle = $form->title;
        $order->customer = null;
        $order->created = time();
        $order->details = $details;
        $order->attrs = $attrs;
        $order->condition = $condition;
        $this->fillTotal($order, $details);
        $this->fillCurrency($order);
        
        return $order;
    }
}