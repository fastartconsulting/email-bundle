<?php

namespace FAC\EmailBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use FAC\EmailBundle\Entity\Email;
use Doctrine\Common\Persistence\ManagerRegistry;
use DateTime;
use FAC\EmailBundle\Utils\Utils;
use FAC\UserBundle\Entity\User;

class EmailRepository extends ServiceEntityRepository {

    ///////////////////////////////////////////
    /// CONSTRUCTOR

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Email::class);
    }

    /**
     * Saves a given entity.
     * @param  Email $entity
     * @param  bool $update
     * @return bool|array
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function write(Email $entity, $update = false) {
        $this->getEntityManager()->getConnection()->beginTransaction();

        try {
            if(!$update) {
                $this->getEntityManager()->persist($entity);
            }
            $this->getEntityManager()->flush();
            $this->getEntityManager()->getConnection()->commit();
        } catch (\Exception $e) {
            $exception = Utils::getFormattedExceptions($e);
            $this->getEntityManager()->getConnection()->rollBack();
            return $exception;
        }

        return true;
    }

    /**
     * get Queue Email not sent
     * @param DateTime $date
     * @return array|null
     */
    public function findNotSent($date) {
        $qb = $this->createQueryBuilder('email')
            ->where('email.sendOn <= :date_compare OR email.sendOn IS NULL')
            ->andWhere('email.status = :status_pending')
            ->setParameter('date_compare' , date('Y-m-d H:i:s', $date->getTimestamp()))
            ->setParameter('status_pending' , Email::STATUS_PENDING)
            ->orderBy('email.sendOn', 'ASC')
            ->setFirstResult(0)
            ->setMaxResults(20);
        $list = $qb->getQuery()->getResult();

        return $list;
    }

    /**
     * @param User $user
     * @param DateTime $current_date
     * @param DateTime $day_less_date
     * @return array|null
     */
    public function findJustSentConfirmation(User $user, DateTime $current_date, DateTime $day_less_date) {
        $qb = $this->createQueryBuilder('email')
            ->where('email.type = :type')
            ->andWhere('email.user = :user')
            ->andWhere('email.queueOn BETWEEN :initialDate AND :finalDate')
            ->setParameter('type' , Email::TYPE_REGISTRATION_CONFIRM)
            ->setParameter('user' , $user)
            ->setParameter('initialDate' ,  date('Y-m-d H:i:s', $day_less_date->getTimestamp()))
            ->setParameter('finalDate' ,    date('Y-m-d H:i:s', $current_date->getTimestamp()));
        $list = $qb->getQuery()->getResult();

        return $list;
    }

    /**
     * @param User $user
     * @param DateTime $current_date
     * @param DateTime $day_less_date
     * @return array|null
     */
    public function findJustSentReset(User $user, DateTime $current_date, DateTime $day_less_date) {
        $qb = $this->createQueryBuilder('email')
            ->where('email.type = :type')
            ->andWhere('email.user = :user')
            ->andWhere('email.queueOn BETWEEN :initialDate AND :finalDate')
            ->setParameter('type' , Email::TYPE_PASSWORD_RESETTING)
            ->setParameter('user' , $user)
            ->setParameter('initialDate' ,  date('Y-m-d H:i:s', $day_less_date->getTimestamp()))
            ->setParameter('finalDate' ,    date('Y-m-d H:i:s', $current_date->getTimestamp()));
        $list = $qb->getQuery()->getResult();

        return $list;
    }
}