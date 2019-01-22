<?php

namespace FAC\EmailBundle\Repository;

use FAC\EmailBundle\Entity\TemplateNewsletter;
use Schema\SchemaEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Schema\Entity;

class TemplateNewsletterRepository extends SchemaEntityRepository {

    ///////////////////////////////////////////
    /// CONSTRUCTOR

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, TemplateNewsletter::class);
    }
}