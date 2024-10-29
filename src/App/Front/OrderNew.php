<?php

namespace AFormsEats\App\Front;

use AFormsEats\Domain\InputProcessor;
use AFormsEats\Domain\MailComposer;
use AFormsEats\Domain\OrderException;
use Aura\Payload_Interface\PayloadStatus as Status;

class OrderNew 
{
    protected $formRepo;
    protected $ruleRepo;
    protected $orderRepo;
    protected $mailer;
    protected $session;
    protected $options;
    protected $scorer;
    protected $wordRepo;

    public function __construct($formRepo, $ruleRepo, $orderRepo, $mailer, $session, $options, $scorer, $wordRepo) 
    {
        $this->formRepo = $formRepo;
        $this->ruleRepo = $ruleRepo;
        $this->orderRepo = $orderRepo;
        $this->mailer = $mailer;
        $this->session = $session;
        $this->options = $options;
        $this->scorer = $scorer;
        $this->wordRepo = $wordRepo;
    }

    protected function notify($form, $order, $rule, $word) 
    {
        $compose = new MailComposer($this->session, $this->options, $rule, $word);
        $to = $compose->findAttrByType($order, 'Email');

        if ($to) {
            $mail = (object)array(
                'to' => $to, 
                'fromName' => $form->mail->fromName, 
                'fromAddress' => $form->mail->fromAddress, 
                'alignReturnPath' => $form->mail->alignReturnPath, 
                'subject' => $form->mail->subject, 
                'body' => $compose($order, $form->mail->textBody, true)
            );
            $mail = $this->options->extendThanksMail($mail, $form);
            $this->mailer->setTo($mail->to)
                         ->setFrom($mail->fromName, $mail->fromAddress)
                         ->setReturnPath($mail->alignReturnPath ? $mail->fromAddress : null)
                         ->setSubject($mail->subject)
                         ->setTextBody($mail->body)
                         ->send()
                         ->clear();
        }

        if ($form->mail->notifyTo && !property_exists($order->condition, 'quiet')) {
            $mail = (object)array(
                'to' => $form->mail->notifyTo, 
                'fromName' => $form->mail->fromName, 
                'fromAddress' => $form->mail->fromAddress, 
                'alignReturnPath' => $form->mail->alignReturnPath, 
                'subject' => $form->mail->subject, 
                'body' => $compose($order, $form->mail->textBody, false)
            );
            $mail = $this->options->extendReportMail($mail, $form);
            $this->mailer->setTo($mail->to)
                         ->setFrom($mail->fromName, $mail->fromAddress)
                         ->setReturnPath($mail->alignReturnPath ? $mail->fromAddress : null)
                         ->setSubject($mail->subject)
                         ->setTextBody($mail->body)
                         ->send()
                         ->clear();
        }
    }

    // $inputs is not sanitized/validated. So validated as follows:
    // - careful handling in L66, L69.
    // - $proc() in L78.
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

        $this->orderRepo->add($order);

        $this->notify($form, $order, $rule, $word);

        return $payload->setStatus(Status::SUCCESS)
                       ->setOutput($order);
    }
}