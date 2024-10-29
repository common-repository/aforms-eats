<?php

namespace AFormsEats\App\Admin;

use Aura\Payload_Interface\PayloadStatus as Status;

class Preview 
{
    // $_inputs is not sanitized/validated but not used.
    public function __invoke($_preview, $id, $_inputs, $payload) 
    {
        return $payload->setStatus(Status::SUCCESS)->setOutput(array('id' => $id));
    }

}