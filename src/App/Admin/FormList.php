<?php

namespace AFormsEats\App\Admin;

use Aura\Payload_Interface\PayloadStatus as Status;

class FormList 
{
    protected $formRepo;
    protected $session;

    public function __construct($formRepo, $session) 
    {
        $this->formRepo = $formRepo;
        $this->session = $session;
    }

    // $_form is not sanitized/validated but not used.
    public function __invoke($_form, $payload) 
    {
        if (! $this->session->isLoggedIn()) {
            return $payload->setStatus(Status::NOT_AUTHENTICATED);
        }
        $user = $this->session->getUser();
        
        if ($this->session->canReadForms(true)) {
            $forms = $this->formRepo->getList();
        } else if ($this->session->canReadForms(false)) {
            $forms = $this->formRepo->getListFor($user->id);
        } else {
            return $payload->setStatus(Status::NOT_AUTHORIZED);
        }

        return $payload->setStatus(Status::SUCCESS)
                       ->setOutput(array('forms' => $forms));
    }
}