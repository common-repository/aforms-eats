<?php

namespace AFormsEats\App\Admin;

use Aura\Payload_Interface\PayloadStatus as Status;

class FormDup 
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
    public function __invoke($_dup, $id, $_inputs, $payload) 
    {
        // authentication
        if (! $this->session->isLoggedIn()) {
            return $payload->setStatus(Status::NOT_AUTHENTICATED);
        }

        $form = $this->formRepo->findById($id);
        if (! $form) {
            return $payload->setStatus(Status::NOT_FOUND);
        }

        // authorization
        if (! $this->session->canReadForm($form) || ! $this->session->canWriteForms(false)) {
            return $payload->setStatus(Status::NOT_AUTHORIZED);
        }

        $form->id = -1;
        $form->title = sprintf($this->options->translate('%s Copy', -1), $form->title);
        $form->author = $this->session->getUser();
        $form->modified = time();
        
        $this->formRepo->add($form);

        return $payload->setStatus(Status::SUCCESS)->setOutput(array('form' => $form));
    }
}