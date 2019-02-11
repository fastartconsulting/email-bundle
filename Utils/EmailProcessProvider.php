<?php

namespace FAC\EmailBundle\Utils;

use FAC\EmailBundle\Entity\Email;
use FAC\EmailBundle\Service\EmailService;
use FAC\UserBundle\Entity\User;
use FAC\UserBundle\Utils\EmailProcess;
use Swift_Mailer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Environment;

class EmailProcessProvider extends EmailProcess
{
    private $administration_address = 'admin@no-reply-facemailbundle.it';
    private $container;
    private $swiftMailer;
    private $templating;
    private $em;

    /**
     * EmailProcessProvider constructor.
     * @param ContainerInterface $container
     * @param Swift_Mailer $swiftMailer
     * @param Twig_Environment $templating
     */
    public function __construct(ContainerInterface $container, Swift_Mailer $swiftMailer, Twig_Environment $templating)
    {
        $this->container = $container;
        $this->swiftMailer = $swiftMailer;
        $this->templating = $templating;
        $this->em = $container->get('doctrine.orm.entity_manager');
        parent::__construct($container, $swiftMailer, $templating);
    }

    /**
     * @param null $recipient
     * @param $subject
     * @param $body
     * @param User|null $user
     * @param null $when
     * @param null $sendOn
     * @return bool
     * @throws \Exception
     */
    public function emailProcess($recipient, $subject, $body, User $user = null, $when = null, $sendOn = null)
    {
        if(is_null($when)) {
            $creation = new \DateTime();
            $creation->setTimestamp(time());
        } else {
            $creation = $when;
        }

        $queueMail = new Email();
        $queueMail->setRecipient(is_null($recipient)?$this->administration_address:$recipient);
        $queueMail->setUser($user);
        $queueMail->setSubject($subject);
        $queueMail->setBody($body);
        $queueMail->setQueueOn($creation);
        $queueMail->setSendOn($sendOn);
        $queueMail->setStatus(false);

        $this->em->persist($queueMail);
        $this->em->flush();

        return true;
    }
}
