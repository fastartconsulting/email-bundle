<?php

namespace FAC\EmailBundle\Service;

use DateTime;
use FAC\EmailBundle\Entity\EmailNewsletterCheck;
use FAC\EmailBundle\Repository\EmailNewsletterCheckRepository;
use ProfileBundle\Document\Profile;
use Schema\Entity;
use Schema\EntityService;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EmailNewsletterCheckService extends EntityService {

    ///////////////////////////////////////////
    /// CONSTRUCTOR

    /**
     * @param EmailNewsletterCheckRepository $repository
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(EmailNewsletterCheckRepository $repository, AuthorizationCheckerInterface $authorizationChecker) {
        parent::__construct($repository, $authorizationChecker);
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
     * @param Profile $profile
     * @param bool $manualFlush
     * @param EmailNewsletterCheck|null $emailNewsletterCheck
     * @return EmailNewsletterCheck|null
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function saveEmailNewsletterCheck(Profile $profile, $manualFlush = false, EmailNewsletterCheck $emailNewsletterCheck = null) {
        $creation = new DateTime();
        $creation->setTimestamp(time());

        if(is_null($emailNewsletterCheck)) {
            $emailNewsletterCheck = new EmailNewsletterCheck();
            $emailNewsletterCheck->setCreatedOn($creation);
        }
        $emailNewsletterCheck->setIdProfile($profile->getId());
        $emailNewsletterCheck->setEmail($profile->getEmail());
        $emailNewsletterCheck->setIsBadged($profile->serializedIsBadged());

        if(!$manualFlush) {
            if(!$this->save($emailNewsletterCheck)) {
                return null;
            }
        }

        return $emailNewsletterCheck;
    }
}