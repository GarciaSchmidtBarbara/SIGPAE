<?php

namespace App\Mail;

use Brevo\Brevo;
use Brevo\TransactionalEmails\Requests\SendTransacEmailRequest;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestSender;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestToItem;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class BrevoTransport extends AbstractTransport
{
    protected string $apiKey;

    public function __construct(string $apiKey)
    {
        parent::__construct();
        $this->apiKey = $apiKey;
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $to = [];
        foreach ($email->getTo() as $address) {
            $to[] = new SendTransacEmailRequestToItem([
                'email' => $address->getAddress(),
                'name'  => $address->getName() ?: null,
            ]);
        }

        $sender = new SendTransacEmailRequestSender([
            'email' => $email->getFrom()[0]->getAddress(),
            'name'  => $email->getFrom()[0]->getName() ?: null,
        ]);

        $request = new SendTransacEmailRequest([
            'sender'      => $sender,
            'to'          => $to,
            'subject'     => $email->getSubject(),
            'htmlContent' => $email->getHtmlBody() ?? $email->getTextBody(),
        ]);
        $brevo = new Brevo($this->apiKey); 
        try {
            $brevo->transactionalEmails->sendTransacEmail($request);
        } catch (\Brevo\Exceptions\BrevoApiException $e) {
            throw new \Exception('Brevo Error ' . $e->getCode() . ': ' . print_r($e->getBody(), true));
        }
    }

    public function __toString(): string
    {
        return 'brevo';
    }
}