<?php

namespace FAC\EmailBundle\Service;

use FAC\EmailBundle\Entity\Email;
use FAC\EmailBundle\Repository\EmailRepository;
use LogBundle\Service\LogMonitorService;
use LogBundle\Service\LogService;
use Schema\Entity;
use Schema\EntityService;
use Swift_Mailer;
use Symfony\Component\Form\Util\StringUtil;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use DateTime;
use Exception;
use Symfony\Component\Translation\TranslatorInterface;
use FAC\UserBundle\Entity\User;
use Utils\LogUtils;
use Utils\StringUtils;

class EmailService extends EntityService {

    private $administration_address;

    private $sender_name;

    private $swiftMailer;

    private $outlookSwiftMailer;

    /** @var LogService $logService */
    private $logService;

    /** @var TranslatorInterface $translator */
    private $translator;

    private $log_dir;

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
     * @param LogMonitorService $logMonitorService
     * @param LogService $logService
     * @param TranslatorInterface $translator
     */
    public function __construct(EmailRepository $repository, AuthorizationCheckerInterface $authorizationChecker,
                                string $log_dir,
                                string $mailer_user,
                                string $sender_name,
                                Swift_Mailer $swift_mailer,
                                Swift_Mailer $second_swift_mailer,
                                LogMonitorService $logMonitorService,
                                LogService $logService,
                                TranslatorInterface $translator) {
        $this->repository = $repository;
        $this->administration_address = $mailer_user;
        $this->sender_name = $sender_name;
        $this->swiftMailer = $swift_mailer;
        $this->outlookSwiftMailer = $second_swift_mailer;
        $this->logService = $logService;
        $this->translator = $translator;
        $this->log_dir = $log_dir;
        parent::__construct($repository, $authorizationChecker, $logMonitorService);
    }

    /**
     * Returns true if the logged user is the creator of this entity.
     * @param Entity $entity
     * @return bool
     */
    public function isOwner(Entity $entity)
    {
        // TODO: Implement isOwner() method.
    }

    /**
     * Returns true if the logged user can administrate the entity
     * @param Entity $entity
     * @return bool
     */
    public function canAdmin(Entity $entity)
    {
        // TODO: Implement canAdmin() method.
    }

    /**
     * Returns true if the logged user can POST the entity
     * @return bool
     */
    public function canPost()
    {
        // TODO: Implement canPost() method.
    }

    /**
     * Returns true if the logged user can PUT the entity
     * @param Entity $entity
     * @return bool
     */
    public function canPut(Entity $entity)
    {
        // TODO: Implement canPut() method.
    }

    /**
     * Returns true if the logged user can PATCH the entity
     * @param Entity $entity
     * @return bool
     */
    public function canPatch(Entity $entity)
    {
        // TODO: Implement canPatch() method.
    }

    /**
     * Returns true if the logged user can DELETE the entity
     * @param Entity $entity
     * @return bool
     */
    public function canDelete(Entity $entity)
    {
        // TODO: Implement canDelete() method.
    }

    /**
     * Returns true if the logged user can GET the entity
     * @param Entity $entity
     * @return bool
     */
    public function canGet(Entity $entity)
    {
        // TODO: Implement canGet() method.
    }

    /**
     * Returns true if the logged user can GET a list of this entity
     * @return bool
     */
    public function canGetList()
    {
        // TODO: Implement canGetList() method.
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
     * @throws \Doctrine\DBAL\ConnectionException
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

    public function processQueueEmail($log_file, string $subject, string $email, string $body) {

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom([$this->administration_address => $this->sender_name])
            ->setTo($email)
            ->setContentType('text/html')
            ->setBody($body)
        ;

        try {

            if(!StringUtils::checkEmailString($email)) {
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

            $params = LogUtils::getLogParams(null,$this->translator, 0, json_encode($e));
            $this->logService->createByCommand($params,$this->log_dir,"email",$log_file);

            return false;
        }

        return true;
    }

    /**
     * Get just sent confirmation
     * @param User $user
     * @return bool
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