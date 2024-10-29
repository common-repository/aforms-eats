<?php

namespace AFormsEats\App\Admin;

use Aura\Payload_Interface\PayloadStatus as Status;
use AFormsEats\Domain\FormProcessor;

class FormRef 
{
    protected $formRepo;
    protected $session;
    protected $options;

    public function __construct($formRepo, $session, $options) 
    {
        $this->formRepo = $formRepo;
        $this->session = $session;
        $this->options = $options;
    }

    // $_inputs is not sanitized/validated but not used.
    public function __invoke($cmd, $id, $_inputs, $payload) 
    {
        if (! $this->session->isLoggedIn()) {
            return $payload->setStatus(Status::NOT_AUTHENTICATED);
        }
        
        if ($cmd == 'edit') {
            $form = $this->formRepo->findById($id);
            if (! $form) {
                return $payload->setStatus(Status::NOT_FOUND);
            }
            (new FormProcessor())->decompile($form);

            if (! $this->session->canReadForm($form)) {
                return $payload->setStatus(Status::NOT_AUTHORIZED);
            }
        } else { // new
            // no authorization

            $form = new \stdClass();
            $form->id = -1;
            $form->title = $this->options->getDefaultFormTitle(-1);
            $form->navigator = 'horizontal';
            $form->doConfirm = true;
            $form->thanksUrl = '';
            $form->author = $this->session->getUser();
            $form->modified = 0;
            $form->detailItems = array();
            $form->attrItems = array();
            $form->mail = $this->options->getDefaultMail(-1);
        }
        //var_dump($form);exit;

        $schemas = array(
            'General' => FormSet::getGeneralSchema(), 
            'Auto' => FormSet::getAutoSchema(), 
            //'Selector' => FormSet::getSelectorSchema(), 
            //'Option' => FormSet::getOptionSchema(), 
            'Group' => FormSet::getGroupSchema(), 
            'Product' => FormSet::getProductSchema(), 
            //'PriceChecker' => FormSet::getPriceCheckerSchema(), 
            'PriceWatcher' => FormSet::getPriceWatcherSchema(), 
            'Stop' => FormSet::getStopSchema(), 
            //'Quantity' => FormSet::getQuantitySchema(), 
            'Mail' => FormSet::getMailSchema()
        );
        foreach (FormSet::getAllAttributes() as $attr) {
            $schemas[$attr] = FormSet::getAttrSchema($attr);
        }

        return $payload->setStatus(Status::SUCCESS)
                       ->setOutput(array('form' => $form, 'schemas' => $schemas));
    }
}