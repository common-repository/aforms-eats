<?php

namespace AFormsEats\App\Admin;

use Aura\Payload_Interface\PayloadStatus as Status;

class OrderListPage 
{
    protected $session;
    protected $orderRepo;
    protected $validator;
    protected $formRepo;

    public function __construct($session, $orderRepo, $validator, $formRepo) 
    {
        $this->session = $session;
        $this->orderRepo = $orderRepo;
        $this->validator = $validator;
        $this->formRepo = $formRepo;
    }

    // $_inputs is not sanitized/validated but not used.
    public function __invoke($page, $_inputs, $payload) 
    {
        // authentication
        if (! $this->session->isLoggedIn()) {
            return $payload->setStatus(Status::NOT_AUTHENTICATED);
        }
        
        $user = $this->session->getUser();
        
        $offset = ($page - 1) * OrderList::LIMIT;
        if ($this->session->canReadOrders(true)) {
            $orders = $this->orderRepo->slice($offset, OrderList::LIMIT);
            $count = $this->orderRepo->count();
        } else if ($this->session->canReadOrders(false)) {
            $formIds = $this->formRepo->listIdsFor($user->id);
            $orders = $this->orderRepo->sliceFor($formIds, $offset, OrderList::LIMIT);
            $count = $this->orderRepo->countFor($formIds);
        } else {
            return $payload->setStatus(Status::NOT_AUTHORIZED);
        }
        
        $paging = new \stdClass();
        $paging->lastPage = ceil($count / OrderList::LIMIT);
        $paging->firstPage = 1;
        $paging->page = $page;
        $paging->total = $count;

        $output = array(
            'orders' => $orders, 
            'paging' => $paging
        );

        return $payload->setStatus(Status::SUCCESS)
                       ->setOutput($output);
    }
}