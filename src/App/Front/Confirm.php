<?php

namespace AFormsEats\App\Front;

use AFormsEats\Domain\InputProcessor;
use AFormsEats\Domain\MailComposer;
use AFormsEats\Domain\OrderException;
use Aura\Payload_Interface\PayloadStatus as Status;

class Confirm 
{
    protected $formRepo;
    protected $ruleRepo;
    protected $session;
    protected $options;
    protected $scorer;
    protected $wordRepo;

    public function __construct($formRepo, $ruleRepo, $session, $options, $scorer, $wordRepo) 
    {
        $this->formRepo = $formRepo;
        $this->ruleRepo = $ruleRepo;
        $this->session = $session;
        $this->options = $options;
        $this->scorer = $scorer;
        $this->wordRepo = $wordRepo;
    }

    // $inputs is not sanitized/validated. So validated as follows:
    // - careful handling in L36, L39.
    // - $proc() in L48.
    public function __invoke($inputs, $payload) 
    {
        // no authentication

        // no authorization

        if (! $inputs->formId || ! intval($inputs->formId)) {
            return $payload->setStatus(Status::NOT_VALID);
        }
        $form = $this->formRepo->findById(intval($inputs->formId));
        if (! $form) {
            return $payload->setStatus(Status::NOT_VALID);
        }

        $rule = $this->ruleRepo->load();
        $word = $this->wordRepo->load();

        $proc = new InputProcessor($this->scorer, $this->options, $rule, $word);
        try {
            $order = $proc($form, $inputs);
        } catch (OrderException $e) {
            error_log($e->getTraceAsString());
            return $payload->setStatus(Status::NOT_AUTHORIZED);
        } catch (\Exception $e) {
            error_log($e->getTraceAsString());
            return $payload->setStatus(Status::NOT_VALID);
        }

        $output = $order;
        //$output = $this->options->extendValue('aforms-eats-confirm', $output, $form);

        return $payload->setStatus(Status::SUCCESS)
                       ->setOutput($output);
    }
}