<?php

namespace AFormsEats\App\Admin;

use Aura\Payload_Interface\PayloadStatus as Status;

class OrderList 
{
    protected $session;
    protected $orderRepo;
    protected $validator;
    protected $formRepo;
    const LIMIT = 20;

    public function __construct($session, $orderRepo, $validator, $formRepo) 
    {
        $this->session = $session;
        $this->orderRepo = $orderRepo;
        $this->validator = $validator;
        $this->formRepo = $formRepo;
    }

    // $_inputs is not sanitized/validated but not used.
    public function __invoke($_inputs, $payload) 
    {
        // authentication
        if (! $this->session->isLoggedIn()) {
            return $payload->setStatus(Status::NOT_AUTHENTICATED);
        }
        
        $user = $this->session->getUser();
        
        if ($this->session->canReadOrders(true)) {
            $orders = $this->orderRepo->slice(0, self::LIMIT);
            $count = $this->orderRepo->count();
        } else if ($this->session->canReadOrders(false)) {
            $formIds = $this->formRepo->listIdsFor($user->id);
            $orders = $this->orderRepo->sliceFor($formIds, 0, self::LIMIT);
            $count = $this->orderRepo->countFor($formIds);
        } else {
            return $payload->setStatus(Status::NOT_AUTHORIZED);
        }
        if ($this->session->canWriteOrders(true)) {
            $writableForms = $this->formRepo->listIds();
        } else if ($this->session->canWriteOrders(false)) {
            $writableForms = $this->formRepo->listIdsFor($user->id);
        } else {
            $writableForms = array();
        }
        
        $paging = new \stdClass();
        $paging->lastPage = ceil($count / self::LIMIT);
        $paging->firstPage = 1;
        $paging->page = 1;
        $paging->total = $count;

        $output = array(
            'orders' => $orders, 
            'paging' => $paging, 
            'writableForms' => $writableForms
        );

        return $payload->setStatus(Status::SUCCESS)
                       ->setOutput($output);
    }
}