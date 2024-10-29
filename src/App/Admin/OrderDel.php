<?php

namespace AFormsEats\App\Admin;

use Aura\Payload_Interface\PayloadStatus as Status;

class OrderDel 
{
    protected $orderRepo;
    protected $session;
    protected $formRepo;

    public function __construct($orderRepo, $session, $formRepo) 
    {
        $this->orderRepo = $orderRepo;
        $this->session = $session;
        $this->formRepo = $formRepo;
    }

    // $_inputs is not sanitized/validated but not used.
    public function __invoke($_del, $id, $_inputs, $payload) 
    {
        // authentication
        if (! $this->session->isLoggedIn()) {
            return $payload->setStatus(Status::NOT_AUTHENTICATED);
        }

        $order = $this->orderRepo->findById($id);
        if (! $order) {
            return $payload->setStatus(Status::NOT_FOUND);
        }
        
        $form = $this->formRepo->findById($order->formId);  // can be null

        // authorization
        if (! $this->session->canWriteOrder($order, $form)) {
            return $payload->setStatus(Status::NOT_AUTHORIZED);
        }

        $this->orderRepo->remove($order);

        return $payload->setStatus(Status::SUCCESS)->setOutput(null);
    }
}