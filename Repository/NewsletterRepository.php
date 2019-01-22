<?php

namespace FAC\EmailBundle\Repository;

use DateTime;
use FAC\EmailBundle\Entity\Newsletter;
use LogBundle\Document\LogMonitor;
use LogBundle\Service\LogMonitorService;
use Schema\SchemaEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Schema\Entity;
use Utils\LogUtils;

class NewsletterRepository extends SchemaEntityRepository {

    ///////////////////////////////////////////
    /// CONSTRUCTOR
    private $logMonitorService;

    /**
     * @param ManagerRegistry $registry
     * @param LogMonitorService $logMonitorService
     */
    public function __construct(ManagerRegistry $registry, LogMonitorService $logMonitorService) {
        $this->logMonitorService = $logMonitorService;
        parent::__construct($registry, Newsletter::class);
    }

    /**
     * get Queue Email not sent
     * @param DateTime $dateBefore
     * @param DateTime $dateAfter
     * @return array
     */
    public function findNotQueuing($dateBefore, $dateAfter) {
        $qb = $this->createQueryBuilder('newsletter')
            ->orWhere('newsletter.sendNow = 1 OR (newsletter.sendOn >= :date_before AND newsletter.sendOn <= :date_after)')
            ->andWhere('newsletter.queuing = 0')
            ->andWhere('newsletter.isDraft = 0')
            ->setParameter('date_before' , date('Y-m-d H:i:s', $dateBefore->getTimestamp()))
            ->setParameter('date_after' ,  date('Y-m-d H:i:s', $dateAfter->getTimestamp()))
            ->orderBy('newsletter.sendNow');

        $list = array();

        try {
            $list = $qb->getQuery()->getResult();
        } catch (\Exception $e) {
            $exception = LogUtils::getFormattedExceptions($e);
            $this->logMonitorService->trace(LogMonitor::LOG_CHANNEL_QUERY, 500, "query.error", $exception);
        }

        return $list;
    }
}