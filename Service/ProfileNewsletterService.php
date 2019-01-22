<?php

namespace FAC\EmailBundle\Service;

use FAC\EmailBundle\Repository\ProfileNewsletterRepository;
use Schema\Entity;
use Schema\EntityService;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProfileNewsletterService extends EntityService {

    ///////////////////////////////////////////
    /// CONSTRUCTOR

    /**
     * @param ProfileNewsletterRepository $repository
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(ProfileNewsletterRepository $repository, AuthorizationCheckerInterface $authorizationChecker) {
        $this->repository = $repository;
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
     * get all by newsletters
     * @param array $newsletters
     * @return array|null
     */
    public function getAllByNewsletters($newsletters) {
        return $this->repository->findAllByIdsNewsletters($newsletters);
    }
}