<?php

namespace AFormsEats\App\Admin;

use Aura\Payload_Interface\PayloadStatus as Status;

class CapSysSet 
{
    protected $session;

    public function __construct($session) 
    {
        $this->session = $session;
    }

    // $form is not sanitized/validated. So validated by $this->validator->coerce().
    public function __invoke($input, $payload) 
    {
        if (! $this->session->isLoggedIn()) {
            return $payload->setStatus(Status::NOT_AUTHENTICATED);
        }
        
        if (! $this->session->isAdmin()) {
            return $payload->setStatus(Status::NOT_AUTHORIZED);
        }

        $enabled = ($input) ? true : false;
        $this->session->setNewcapsys($enabled);

        return $payload->setStatus(Status::SUCCESS);
    }
}
