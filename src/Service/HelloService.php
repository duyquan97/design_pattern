<?php
// src/Service/HelloService.php
namespace App\Service;

class HelloService
{
    private $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function hello(string $name)
    {
        $message = new \Swift_Message('Hello Service');
        $message->setTo('quandd@smartosc.com');
        $message->setBody($name.' says hi!');

        $this->mailer->send($message);

        return 'Hello, '.$name;
    }
}
