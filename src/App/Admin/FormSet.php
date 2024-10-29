<?php

namespace AFormsEats\App\Admin;

use Aura\Payload_Interface\PayloadStatus as Status;
use AFormsEats\Domain\FormProcessor;

class FormSet 
{
    protected $formRepo;
    protected $session;
    protected $validator;

    const LABEL_PATTERN = '^([a-zA-Z0-9-_]+)?( *, *[a-zA-Z0-9-_]+)*$';
    const EMAIL_PATTERN = '^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$';
    const EMAIL_LIST_PATTERN = '^' . '([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+' 
                             . '( *, *' . '([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+' . ')*' . '$';

    public static function getGeneralSchema() 
    {
        return (object)array(
            'type' => 'object', 
            'properties' => (object)array(
                'title' => (object)array('type' => 'string'), 
                'navigator' => (object)array(
                    'type' => 'string', 
                    'enum' => array('horizontal', 'wizard')
                ), 
                'doConfirm' => (object)array('type' => 'boolean'), 
                'thanksUrl' => (object)array(
                    'anyOf' => array(
                        (object)array('type' => 'string', 'format' => 'uri'), 
                        (object)array('type' => 'string', 'maxLength' => 0)
                    )
                )
            ), 
            'required' => array('title', 'navigator', 'doConfirm', 'thanksUrl')
        );
    }

    public static function getMailSchema() 
    {
        return (object)array(
            'type' => 'object', 
            'properties' => (object)array(
                'subject' => (object)array('type' => 'string', 'minLength' => 1), 
                'fromAddress' => (object)array('type' => 'string', 'minLength' => 1, 'pattern' => self::EMAIL_PATTERN), 
                'fromName' => (object)array('type' => 'string'), 
                'alignReturnPath' => (object)array('type' => 'boolean'), 
                'notifyTo' => (object)array(
                    'anyOf' => array(
                        (object)array('type' => 'string', 'maxLength' => 0), 
                        (object)array('type' => 'string', 'pattern' => self::EMAIL_LIST_PATTERN)
                    )
                ), 
                'textBody' => (object)array('type' => 'string')
            ), 
            'required' => array('subject', 'fromAddress', 'fromName', 'alignReturnPath', 'notifyTo', 'textBody')
        );
    }

    public static function getAutoSchema() 
    {
        return (object)array(
            'type' => 'object', 
            'properties' => (object)array(
                'id' => (object)array('type' => 'integer'), 
                'type' => (object)array('type' => 'string', 'pattern' => '^Auto$'), 
                'name' => (object)array('type' => 'string', 'minLength' => 1), 
                'category' => (object)array('type' => 'string'), 
                'price' => (object)array('type' => 'number'), 
                'taxRate' => (object)array('type' => array('number', 'null'), 'minimum' => 0), 
                'quantity' => (object)array('type' => 'integer'), 
                'depends' => (object)array('type' => 'string', 'pattern' => self::LABEL_PATTERN)
            ), 
            'required' => array('id', 'type', 'name', 'category', 'price', 'quantity', 'taxRate', 'depends')
        );
    }

    public static function getStopSchema() 
    {
        return (object)array(
            'type' => 'object', 
            'properties' => (object)array(
                'id' => (object)array('type' => 'integer'), 
                'type' => (object)array('type' => 'string', 'pattern' => '^Stop$'), 
                'message' => (object)array('type' => 'string', 'minLength' => 1), 
                'depends' => (object)array('type' => 'string', 'pattern' => self::LABEL_PATTERN)
            ), 
            'required' => array('id', 'type', 'message', 'depends')
        );
    }

    public static function getGroupSchema() 
    {
        return (object)array(
            'type' => 'object', 
            'properties' => (object)array(
                'id' => (object)array('type' => 'integer'), 
                'type' => (object)array('type' => 'string', 'pattern' => '^Group$'), 
                'image' => (object)array('type' => 'string'), 
                'name' => (object)array('type' => 'string', 'minLength' => 1), 
                'visible' => (object)array('type' => 'boolean'), 
                'note' => (object)array('type' => 'string')
            ), 
            'required' => array('id', 'type', 'name', 'visible')
        );
    }

    public static function getProductSchema() 
    {
        return (object)array(
            'type' => 'object', 
            'properties' => (object)array(
                'id' => (object)array('type' => 'integer'), 
                'type' => (object)array('type' => 'string', 'pattern' => '^Product$'), 
                'image' => (object)array('type' => 'string'), 
                'name' => (object)array('type' => 'string', 'minLength' => 1), 
                'state' => (object)array(
                    'type' => 'string', 
                    'enum' => array('effective', 'disabled', 'hidden')
                ), 
                'note' => (object)array('type' => 'string'), 
                'price' => (object)array('type' => 'number'), 
                'taxRate' => (object)array('type' => array('number', 'null'), 'minimum' => 0), 
                'ribbons' => (object)array(
                    'type' => 'object', 
                    'properties' => (object)array(
                        'ribbon1' => (object)array('const' => true), 
                        'ribbon2' => (object)array('const' => true)
                    )
                )
            ), 
            'required' => array('id', 'type', 'image', 'name', 'state', 'note', 'price', 'taxRate', 'ribbons')
        );
    }

    public static function getQuantitySchema() 
    {
        return (object)array(
            'type' => 'object', 
            'properties' => (object)array(
                'id' => (object)array('type' => 'integer'), 
                'type' => (object)array('type' => 'string', 'pattern' => '^Quantity$'), 
                'image' => (object)array('type' => 'string'), 
                'name' => (object)array('type' => 'string', 'minLength' => 1), 
                'allowFraction' => (object)array('type' => 'boolean'), 
                'initial' => (object)array('type' => 'number'), 
                'minimum' => (object)array(
                    'anyOf' => array(
                        (object)array('type' => 'null'), 
                        (object)array('type' => 'number')
                    )
                ), 
                'maximum' => (object)array(
                    'anyOf' => array(
                        (object)array('type' => 'null'), 
                        (object)array('type' => 'number')
                    )
                ), 
                'suffix' => (object)array('type' => 'string'), 
                'note' => (object)array('type' => 'string'), 
                'depends' => (object)array('type' => 'string', 'pattern' => self::LABEL_PATTERN)
            ), 
            'required' => array('id', 'type', 'name', 'allowFraction', 'initial', 'minimum', 'maximum', 'suffix', 'note', 'depends')
        );
    }

    public static function getPriceCheckerSchema() 
    {
        return (object)array(
            'type' => 'object', 
            'properties' => (object)array(
                'id' => (object)array('type' => 'integer'), 
                'type' => (object)array('type' => 'string', 'pattern' => '^PriceChecker$'), 
                'threshold' => (object)array('type' => 'number'), 
                'equation' => (object)array(
                    'type' => 'string', 
                    'enum' => array('equal', 'notEqual', 'greaterThan', 'greaterEqual', 'lessThan', 'lessEqual')
                ), 
                'labels' => (object)array('type' => 'string', 'pattern' => self::LABEL_PATTERN)
            ), 
            'required' => array('id', 'type', 'threshold', 'equation', 'labels')
        );
    }

    public static function getPriceWatcherSchema() 
    {
        return (object)array(
            'type' => 'object', 
            'properties' => (object)array(
                'id' => (object)array('type' => 'integer'), 
                'type' => (object)array('type' => 'string', 'pattern' => '^PriceWatcher$'), 
                'lower' => (object)array('type' => array('number', 'null')), 
                'lowerIncluded' => (object)array('type' => 'boolean'), 
                'higher' => (object)array('type' => array('number', 'null')), 
                'higherIncluded' => (object)array('type' => 'boolean'), 
                'labels' => (object)array('type' => 'string', 'pattern' => self::LABEL_PATTERN)
            ), 
            'required' => array('id', 'type', 'lower', 'lowerIncluded', 'higher', 'higherIncluded', 'labels')
        );
    }

    public static function getSelectorSchema() 
    {
        return (object)array(
            'type' => 'object', 
            'properties' => (object)array(
                'id' => (object)array('type' => 'integer'), 
                'type' => (object)array('type' => 'string', 'pattern' => '^Selector$'), 
                'image' => (object)array('type' => 'string'), 
                'name' => (object)array('type' => 'string', 'minLength' => 1), 
                'note' => (object)array('type' => 'string'), 
                'multiple' => (object)array('type' => 'boolean'), 
                'quantity' => (object)array('type' => 'integer')
            ), 
            'required' => array('id', 'type', 'image', 'name', 'note', 'multiple', 'quantity')
        );
    }

    public static function getOptionSchema() 
    {
        return (object)array(
            'type' => 'object', 
            'properties' => (object)array(
                'id' => (object)array('type' => 'integer'), 
                'type' => (object)array('type' => 'string', 'pattern' => '^Option$'), 
                'image' => (object)array('type' => 'string'), 
                'name' => (object)array('type' => 'string', 'minLength' => 1), 
                'note' => (object)array('type' => 'string'), 
                'normalPrice' => (object)array(
                    'anyOf' => array(
                        (object)array('type' => 'number'), 
                        (object)array('type' => 'null'), 
                        (object)array('type' => 'string', 'maxLength' => 0)
                    )
                ), 
                'price' => (object)array('type' => 'number'), 
                'ribbons' => (object)array(
                    'type' => 'object', 
                    'properties' => (object)array(
                        'ribbon1' => (object)array('const' => true), 
                        'ribbon2' => (object)array('const' => true), 
                        'ribbon3' => (object)array('const' => true)
                    )
                ), 
                'labels' => (object)array('type' => 'string', 'pattern' => self::LABEL_PATTERN), 
                'depends' => (object)array('type' => 'string', 'pattern' => self::LABEL_PATTERN)
            ), 
            'required' => array('id', 'type', 'image', 'name', 'note', 'normalPrice', 'price', 'ribbons', 'labels', 'depends')
        );
    }

    public static function getAllAttributes() 
    {
        return array(
            'Name', 'Email', 'Tel', 'Address', 
            'Checkbox', 'Radio', 'Dropdown', 'Text', 
            'MultiCheckbox', 'reCAPTCHA3'
        );
    }

    public static function getAttrSchema($type) 
    {
        if ($type == 'reCAPTCHA3') {
            return (object)array(
                'type' => 'object', 
                'properties' => (object)array(
                    'id' => (object)array('type' => 'integer'), 
                    'type' => (object)array('type' => 'string', 'pattern' => '^'.$type.'$'), 
                    'siteKey' => (object)array('type' => 'string'), 
                    'secretKey' => (object)array('type' => 'string'), 
                    'action' => (object)array('type' => 'string'), 
                    'threshold1' => (object)array('type' => 'number'), 
                    'threshold2' => (object)array('type' => 'number')
                ), 
                'required' => array('id', 'type', 'siteKey', 'secretKey', 'action', 'threshold1', 'threshold2')
            );
        }
        $rv = (object)array(
            'type' => 'object', 
            'properties' => (object)array(
                'id' => (object)array('type' => 'integer'), 
                'type' => (object)array('type' => 'string', 'pattern' => '^'.$type.'$'), 
                'name' => (object)array('type' => 'string', 'minLength' => 1), 
                'required' => (object)array('type' => 'boolean'), 
                'note' => (object)array('type' => 'string')
            ), 
            'required' => array('id', 'type', 'name', 'required')
        );

        if ($type == 'Name' || $type == 'Tel') {
            $rv->properties->divided = (object)array('type' => 'boolean');
            $rv->required[] = 'divided';
        }
        if ($type == 'Name') {
            $rv->properties->pattern = (object)array(
                'type' => 'string', 
                'enum' => array('none', 'katakana', 'hiragana')
            );
            $rv->required[] = 'pattern';
        }
        if ($type == 'Email') {
            $rv->properties->repeated = (object)array('type' => 'boolean');
            $rv->required[] = 'repeated';
        }
        if ($type == 'AutoFill') {
            $rv->properties->autoFill = (object)array(
                'type' => 'string', 
                'enum' => array('none', 'yubinbango')
            );
            $rv->required[] = 'autoFill';
        }
        if ($type == 'Radio' || $type == 'Dropdown' || $type == 'MultiCheckbox') {
            $rv->properties->options = (object)array('type' => 'string', 'minLength' => 1);
            $rv->required[] = 'options';
        }
        if ($type == 'Radio' || $type == 'Checkbox' || $type == 'Dropdown' || $type == 'MultiCheckbox') {
            $rv->properties->initialValue = (object)array('type' => 'string');
        }
        if ($type == 'Text') {
            $rv->properties->size = (object)array(
                'type' => 'string', 
                'enum' => array('nano', 'mini', 'small', 'normal', 'full')
            );
            $rv->properties->multiline = (object)array('type' => 'boolean');
            $rv->properties->placeholder = (object)array('type' => 'string');
            $rv->required[] = 'size';
            $rv->required[] = 'multiline';
            $rv->required[] = 'placeholder';
        }

        return $rv;
    }

    public function __construct($formRepo, $session, $validator) 
    {
        $this->formRepo = $formRepo;
        $this->session = $session;
        $this->validator = $validator;
    }

    protected function getValidationError($payload, $tag, $errors) 
    {
        $messages = array();
        foreach ($errors as $error) {
            $messages[] = $tag.": ".$error['property'].": ".$error['message'];
        }
        //var_dump($messages);exit;
        return $payload->setStatus(Status::NOT_VALID)->setMessages($messages);
    }

    protected function validateForm($form, $payload) 
    {
        // general
        $general = new \stdClass();
        $general->title = $form->title;
        $general->navigator = $form->navigator;
        $general->doConfirm = $form->doConfirm;
        $general->thanksUrl = $form->thanksUrl;
        $this->validator->coerce($general, self::getGeneralSchema());
        if (! $this->validator->isValid()) {
            return $this->getValidationError($payload, 'general', $this->validator->getErrors());
        }

        // detailItems
        $selectorSchema = self::getSelectorSchema();
        $autoSchema = self::getAutoSchema();
        $optionSchema = self::getOptionSchema();
        $groupSchema = self::getGroupSchema();
        $productSchema = self::getProductSchema();
        $priceCheckerSchema = self::getPriceCheckerSchema();
        $priceWatcherSchema = self::getPriceWatcherSchema();
        $quantitySchema = self::getQuantitySchema();
        $stopSchema = self::getStopSchema();
        foreach ($form->detailItems as $item) {
            switch ($item->type) {
                case 'Auto': 
                    $this->validator->coerce($item, $autoSchema);
                    break;
                case 'Selector': 
                    $this->validator->coerce($item, $selectorSchema);
                    break;
                case 'Group': 
                    $this->validator->coerce($item, $groupSchema);
                    break;
                case 'PriceChecker': 
                    $this->validator->coerce($item, $priceCheckerSchema);
                    break;
                case 'PriceWatcher': 
                    $this->validator->coerce($item, $priceWatcherSchema);
                    break;
                case 'Quantity': 
                    $this->validator->coerce($item, $quantitySchema);
                    break;
                case 'Stop': 
                    $this->validator->coerce($item, $stopSchema);
                    break;
                default: 
                    return false;
            }
            if (! $this->validator->isValid()) {
                return $this->getValidationError($payload, "item[".$item->id."]", $this->validator->getErrors());
            }

            if ($item->type == "Selector") {
                foreach ($item->options as $option) {
                    $this->validator->coerce($option, $optionSchema);
                    if (! $this->validator->isValid()) {
                        return $this->getValidationError($payload, "option[".$item->id."][".$option->id."]", $this->validator->getErrors());
                    }
                }
            } else if ($item->type == "Group") {
                foreach ($item->products as $product) {
                    $this->validator->coerce($product, $productSchema);
                    if (! $this->validator->isValid()) {
                        return $this->getValidationError($payload, "product[".$item->id."][".$product->id."]", $this->validator->getErrors());
                    }
                }
            }
        }

        // attrItems
        foreach (self::getAllAttributes() as $attr) {
            $attrSchemas[$attr] = self::getAttrSchema($attr);
        }
        foreach ($form->attrItems as $item) {
            $this->validator->coerce($item, $attrSchemas[$item->type]);
            if (! $this->validator->isValid()) {
                return $this->getValidationError($payload, "attr[".$item->id."]", $this->validator->getErrors());
            }
        }

        // mail
        $this->validator->coerce($form->mail, self::getMailSchema());
        if (! $this->validator->isValid()) {
            return $this->getValidationError($payload, 'mail', $this->validator->getErrors());
        }

        return $payload;
    }

    // $inputs is not sanitized/validated. So validated by $this->validateForm().
    public function __invoke($_edit, $id, $inputs, $payload) 
    {
        // authentication
        if (! $this->session->isLoggedIn()) {
            return $payload->setStatus(Status::NOT_AUTHENTICATED);
        }

        // authorization
        if ($id < 1) {
            // new
            if (! $this->session->canWriteForms(false)) {
                return $payload->setStatus(Status::NOT_AUTHORIZED);
            }
        } else {
            // set
            $form = $this->formRepo->findById($id);
            if (! $this->session->canWriteForm($form)) {
                return $payload->setStatus(Status::NOT_AUTHORIZED);
            }
        }

        $proc = new FormProcessor();

        // preprocess
        $proc->preprocess($inputs);

        // validation
        $this->validateForm($inputs, $payload);
        if ($payload->getStatus() == Status::NOT_VALID) {
            return $payload;
        }

        // command
        $proc->compile($inputs);
        $inputs->modified = time();
        if ($id < 1) {
            // new
            $inputs->author = $this->session->getUser();
            $this->formRepo->add($inputs);
        } else {
            // edit
            $inputs->author = $form->author;
            $this->formRepo->sync($inputs);
        }

        return $payload->setStatus(Status::SUCCESS)
                       ->setOutput(array('form' => $inputs));
    }
}