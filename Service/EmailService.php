<?php

namespace FAC\EmailBundle\Service;

use FAC\EmailBundle\Entity\Email;
use FAC\EmailBundle\Repository\EmailRepository;
use FAC\EmailBundle\Utils\Utils;
use Swift_Mailer;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use DateTime;
use Exception;
use Symfony\Component\Translation\TranslatorInterface;
use FAC\UserBundle\Entity\User;

class EmailService {

    private $administration_address;

    private $sender_name;

    private $swiftMailer;

    private $outlookSwiftMailer;

    /** @var TranslatorInterface $translator */
    private $translator;

    private $mxDomainsOutlook = array(
        'live.it',
        'live.com',
        'hotmail.it',
        'hotmail.com',
        'outlook.it',
        'outloook.com',
        'msn.it',
        'msn.com'
    );

    ///////////////////////////////////////////
    /// CONSTRUCTOR

    /**
     * EmailService constructor.
     * @param EmailRepository $repository
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param string $log_dir
     * @param string $mailer_user
     * @param string $sender_name
     * @param Swift_Mailer $swift_mailer
     * @param Swift_Mailer $second_swift_mailer
     * @param TranslatorInterface $translator
     */
    public function __construct(EmailRepository $repository,
                                string $mailer_user,
                                string $sender_name,
                                Swift_Mailer $swift_mailer,
                                Swift_Mailer $second_swift_mailer,
                                TranslatorInterface $translator) {
        $this->repository = $repository;
        $this->administration_address = $mailer_user;
        $this->sender_name = $sender_name;
        $this->swiftMailer = $swift_mailer;
        $this->outlookSwiftMailer = $second_swift_mailer;
        $this->translator = $translator;
    }

    /**
     * Finalize and save the creation of the entity.
     * Returns NULL if some error occurs otherwise it returns the persisted object.
     * @param Email $entity
     * @param User|null $user
     * @param bool $update
     * @return object|bool
     * @throws \Exception
     */
    public function save(Email $entity, User $user = null, $update = false) {
        if(!is_null($user)) {
            $current_time = new \DateTime();
            $current_time->setTimestamp(time());
        }

        $writing = $this->repository->write($entity, $update);
        if(is_array($writing)) {
            return false;
        }

        return $entity;
    }

    /**
     * @param null $recipient
     * @param $subject
     * @param $body
     * @param null $user
     * @param null $when
     * @param null $type
     * @param null $sendOn
     * @return null|object
     * @throws Exception
     */
    public function emailEnqueue($recipient = null, $subject, $body, $user = null, $when = null, $type = null, $sendOn = null) {
        if(is_null($when)) {
            $creation = new DateTime();
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
        $queueMail->setType($type);

        return $this->save($queueMail);
    }

    /**
     * get Queue Email not sent
     * @return array|null
     * @throws Exception
     */
    public function getNotSent() {
        $date = new DateTime();
        $date->setTimestamp(time());
        $list = $this->repository->findNotSent($date);

        if(count($list) > 0) {
            return $list;
        }

        return null;
    }

    /**
     * @param string $subject
     * @param string $email
     * @param string $body
     * @return bool
     */
    public function processQueueEmail(string $subject, string $email, string $body) {

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom([$this->administration_address => $this->sender_name])
            ->setTo($email)
            ->setContentType('text/html')
            ->setBody($body)
        ;

        try {

            if(!Utils::checkEmailString($email)) {
                return false;
            }

            $domain = explode("@", $email);
            if(in_array($domain[1], $this->mxDomainsOutlook)) {
                if(!$this->outlookSwiftMailer->send($message))
                    return false;
            }
            else {
                if(!$this->swiftMailer->send($message))
                    return false;
            }

        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Get just sent confirmation
     * @param User $user
     * @return bool
     * @throws Exception
     */
    public function getJustSentConfirmation(User $user){
        $current_date = new DateTime();
        $current_date->setTimestamp(time());
        $day_less_date = (new \DateTime())->modify('-2 hours');
        $list = $this->repository->findJustSentConfirmation($user, $current_date, $day_less_date);

        if(count($list) > 3) {
            return true;
        }

        return false;
    }

    /**
     * Get just sent reset
     * @param User $user
     * @return bool
     * @throws Exception
     */
    public function getJustSentReset(User $user){
        $current_date = new DateTime();
        $current_date->setTimestamp(time());
        $day_less_date = (new \DateTime())->modify('-2 hours');
        $list = $this->repository->findJustSentReset($user, $current_date, $day_less_date);

        if(count($list) > 3) {
            return true;
        }

        return false;
    }
}