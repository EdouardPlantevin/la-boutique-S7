<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private Security $security,
        private EntityManagerInterface $manager
    ){}


    public static function getSubscribedEvents()
    {
        return [
            LoginSuccessEvent::class => 'onLogin',
        ];
    }

    public function onLogin()
    {
        $user = $this->security->getUser();
        if ($user instanceof User) {
            $user->setLastLoginAt(new \DateTime());
            $this->manager->flush();
        }
    }
}