<?php

namespace AFormsEats\App\Front;

use Aura\Payload_Interface\PayloadStatus as Status;

class FormRef 
{
    protected $formRepository;
    protected $session;

    public function __construct($formRepository, $session) 
    {
        $this->formRepository = $formRepository;
        $this->session = $session;
    }

    protected function getId($inputs) 
    {
        if (! isset($inputs->id)) {
            return null;
        }
        $id = intval($inputs->id);
        if ($inputs->id != "".$id) {
            return null;
        }
        
        return $id;
    }

    protected function getMode($inputs) 
    {
        if (isset($inputs->mode) && $inputs->mode == 'preview') {
            return 'preview';
        } else {
            return 'execute';
        }
    }

    protected function filter($form) 
    {
        foreach ($form->attrItems as $ai) {
            if ($ai->type == 'reCAPTCHA3') {
                // Erase secret properties.
                $ai->secretKey = '';
                $ai->threshold1 = 0;
                $ai->threshold2 = 0;
            }
        }
        $form->mail = (object)array();
    }

    // $inputs is not sanitized/validated. So validated by $this->getId() and $this->getMode().
    public function __invoke($inputs, $payload) 
    {
        // no authentication
        // no authorization

        $id = $this->getId($inputs);
        if (is_null($id)) {
            return $payload->setStatus(Status::NOT_VALID);
        }

        $form = $this->formRepository->findById($id);
        if (! $form) {
            return $payload->setStatus(Status::NOT_FOUND);
        }

        $this->filter($form);

        $output = array(
            'form' => $form, 
            'mode' => $this->getMode($inputs)
        );
        return $payload->setStatus(Status::SUCCESS)
                       ->setOutput($output);
    }
}