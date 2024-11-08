<?php

namespace AFormsEats\App\Admin;

use Aura\Payload_Interface\PayloadStatus as Status;

class Install 
{
    protected $formRepo;
    protected $orderRepo;
    protected $session;
    protected $options;

    public function __construct($formRepo, $orderRepo, $session, $options) 
    {
        $this->formRepo = $formRepo;
        $this->orderRepo = $orderRepo;
        $this->session = $session;
        $this->options = $options;
    }

    public function __invoke($_input, $payload) 
    {
        // no authentication
        //if (! $this->session->isLoggedIn()) {
        //    return $payload->setStatus(Status::NOT_AUTHENTICATED);
        //}

        // no authorization
        //if (! $this->session->isAdmin()) {
        //    return $payload->setStatus(Status::NOT_AUTHORIZED);
        //}

        // setup new capabilities system
        $this->session->setupNewcapsys();

        // create tables
        $this->formRepo->createTable();
        $this->orderRepo->createTable();

        // insert a sample form
        $form = $this->formRepo->getSampleForm($this->session->getUser());
        $this->formRepo->add($form);

        // install language files
        //$this->options->installLanguages();

        return $payload->setStatus(Status::SUCCESS);
    }
}