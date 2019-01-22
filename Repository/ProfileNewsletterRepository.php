<?php

namespace FAC\EmailBundle\Repository;

use FAC\EmailBundle\Entity\ProfileNewsletter;
use LogBundle\Document\LogMonitor;
use LogBundle\Service\LogMonitorService;
use Schema\SchemaEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Schema\Entity;
use Utils\LogUtils;

class ProfileNewsletterRepository extends SchemaEntityRepository {

    ///////////////////////////////////////////
    /// CONSTRUCTOR
    private $logMonitorService;

    /**
     * @param ManagerRegistry $registry
     * @param LogMonitorService $logMonitorService
     */
    public function __construct(ManagerRegistry $registry, LogMonitorService $logMonitorService) {
        $this->logMonitorService = $logMonitorService;
        parent::__construct($registry, ProfileNewsletter::class);
    }

    /**
     * find all by newsletters
     * @param array $newsletters
     * @return array|null
     */
    public function findAllByIdsNewsletters($newsletters) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('pn')
            ->from('FACEmailBundle:ProfileNewsletter', 'pn')
            ->where('pn.newsletter IN (:newsletters)')
            ->setParameter('newsletters', $newsletters);

        $results = array();

        try {
            $results = $qb->getQuery()->getResult();
        } catch (\Exception $e) {
            $exception = LogUtils::getFormattedExceptions($e);
            $this->logMonitorService->trace(LogMonitor::LOG_CHANNEL_QUERY, 500, "query.error", $exception);
        }

        return $results;
    }
}