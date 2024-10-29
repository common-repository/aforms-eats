<?php

namespace AFormsEats\App\Admin;

use Aura\Payload_Interface\PayloadStatus as Status;

class FormDel 
{
    protected $formRepo;
    protected $session;

    public function __construct($formRepo, $session) 
    {
        $this->formRepo = $formRepo;
        $this->session = $session;
    }

    // $_inputs is not sanitized/validated but not used.
    public function __invoke($_del, $id, $_inputs, $payload) 
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
        if (! $this->session->canWriteForm($form)) {
            return $payload->setStatus(Status::NOT_AUTHORIZED);
        }

        $this->formRepo->remove($form);

        return $payload->setStatus(Status::SUCCESS)->setOutput(null);
    }
}