<?php

namespace AFormsEats\App\Admin;

use Aura\Payload_Interface\PayloadStatus as Status;

class SettingsRef 
{
    protected $ruleRepository;
    protected $wordRepository;
    protected $behaviorRepository;
    protected $session;

    public function __construct($ruleRepository, $wordRepository, $behaviorRepository, $session) 
    {
        $this->ruleRepository = $ruleRepository;
        $this->wordRepository = $wordRepository;
        $this->behaviorRepository = $behaviorRepository;
        $this->session = $session;
    }

    // $_form is not sanitized/validated but not used.
    public function __invoke($_form, $payload) 
    {
        if (! $this->session->isLoggedIn()) {
            return $payload->setStatus(Status::NOT_AUTHENTICATED);
        }
        
        if (! $this->session->isAdmin()) {
            return $payload->setStatus(Status::NOT_AUTHORIZED);
        }

        return $payload->setOutput(array(
            'rule' => $this->ruleRepository->load(), 
            'word' => $this->wordRepository->load(), 
            'behavior' => $this->behaviorRepository->load(), 
            'ruleSchema' => SettingsSet::getRuleSchema(), 
            'wordSchema' => SettingsSet::getWordSchema(), 
            'behaviorSchema' => SettingsSet::getBehaviorSchema()
        ))->setStatus(Status::SUCCESS);
    }
}