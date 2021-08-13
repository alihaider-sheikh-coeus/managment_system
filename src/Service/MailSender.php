<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Twig\Environment;

class MailSender
{

    private $mailer;

    /**
     * @var FlashBagInterface
     */
    private $flashBag;
    /**
     * @var Container
     */
    private $container;

    public function __construct(\Swift_Mailer $mailer,Environment $container,FlashBagInterface $flashBag)
    {
        $this->mailer     = $mailer;

        $this->flashBag = $flashBag;
        $this->container = $container;
    }

    public function sendMail($data,$recipients,$userName,$shopName)
    {
        $message = new \Swift_Message();
        $message->setSubject('Review  created');
        $message->setFrom('ali.haider6713@gmail.com');
        $message->setTo($recipients);
        foreach ($recipients as $recipient)
        {
            $message->setBody(
                $this->container->render('emails/mailToSuperAdmin.html.twig',
                    [
                        'recipient'=>strstr($recipient, '@', true),
                        'reviewContent'=>$data->getContent(),
                        'reviewStatus'=>$data->getStatus(),
                        'userName'=>$userName->getName(),
                        'shopName'=>$shopName->getName(),

                ]),'text/html'
        );
        }

        $this->mailer->send($message);

    }
}