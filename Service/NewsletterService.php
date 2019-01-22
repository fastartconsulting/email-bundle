<?php

namespace FAC\EmailBundle\Service;

use DateInterval;
use DateTime;
use FAC\EmailBundle\Repository\NewsletterRepository;
use Schema\Entity;
use Schema\EntityService;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class NewsletterService extends EntityService {

    ///////////////////////////////////////////
    /// CONSTRUCTOR

    /**
     * @param NewsletterRepository $repository
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(NewsletterRepository $repository, AuthorizationCheckerInterface $authorizationChecker) {
        parent::__construct($repository, $authorizationChecker);
        $this->repository = $repository;
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
     * get newsletter not queuing
     * @return array|null
     * @throws \Exception
     */
    public function getNotQueuing() {
        $dateTimeBefore = new DateTime();
        $interval = new DateInterval("PT15M");
        $interval->invert = 1;
        $dateTimeBefore->sub($interval);
        $dateTimeBefore->setTimestamp(time());

        $dateTimeAfter = new DateTime();
        $interval = new DateInterval("PT15M");
        $interval->invert = 1;
        $dateTimeAfter->add($interval);
        $dateTimeAfter->setTimestamp(time());


        $list = $this->repository->findNotQueuing($dateTimeBefore, $dateTimeAfter);

        if(count($list) > 0) {
            return $list;
        }

        return null;
    }
}