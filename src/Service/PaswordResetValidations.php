<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class PaswordResetValidations
{
    /**
     * @var FlashBagInterface
     */
    private $flashBag;

    public function __construct(FlashBagInterface $flashBag)
    {
        $this->flashBag = $flashBag;
    }

    public function validateFields($status,$newPassword,$confirmPassword):string{
        if(!$status)
        {
           $flash= $this->flashBag->add('error','current password is wrong');
        }
        elseif (strlen($newPassword)<7 || strlen($confirmPassword)<7)
        {
            $flash= $this->flashBag->add('error','new password cant be shorter then length 7');
        }
        elseif(empty($newPassword) || empty($confirmPassword ))
        {
            $flash=   $this->flashBag->add('error','new password fields cant be empty');

        }
        elseif ($newPassword!==$confirmPassword )
        {
            $flash=  $this->flashBag->add('error','passwords does not match');
        }
        return $flash;
    }

}