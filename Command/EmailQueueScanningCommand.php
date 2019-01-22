<?php

namespace FAC\EmailBundle\Command;

use FAC\EmailBundle\Entity\Email;
use FAC\EmailBundle\Service\EmailService;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\TranslatorInterface;

class EmailQueueScanningCommand extends ContainerAwareCommand {

    private $emailService;

    /** @var TranslatorInterface $translator */
    private $translator;

    public function __construct(EmailService $emailService, TranslatorInterface $translator) {
        $this->emailService = $emailService;
        $this->translator   = $translator;

        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('email:queue-scanning')
            ->setDescription('Start to process email queue.')
            ->setHelp('Start to process email queue.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        /** @var EmailService $emailService */
        $emailService = $this->emailService;

        $output->writeln("START");

        try {
            $queue_mail_list = $emailService->getNotSent();

            if(is_null($queue_mail_list)) {
                $params['message'] = $this->translator->trans("NO MAILS FOUND");
                $output->writeln("NO MAILS FOUND");
                return;
            }

            $mail_error_counter = 0;
            $db_error_counter = 0;
            $sent_counter = 0;
            $counter = 0;

            /** @var Email $queueMail */
            foreach ($queue_mail_list as $queueMail) {
                $counter++;

                $output->writeln("MAIL IN CHARGE");
                $sent = $emailService->processQueueEmail($queueMail->getSubject(), $queueMail->getRecipient(), $queueMail->getBody());
                $creation = new DateTime();
                $creation->setTimestamp(time());
                if($sent) {
                    $sent_counter++;

                    $queueMail->setSendOn($creation);
                    $queueMail->setStatus($queueMail::STATUS_SENDED);
                    $output->writeln("SENT");
                } else {
                    $mail_error_counter++;

                    $queueMail->setFailedOn($creation);
                    $queueMail->setStatus($queueMail::STATUS_FAILED);
                    $output->writeln("NOT SENT");
                }

                if(!$emailService->save($queueMail)) {
                    $db_error_counter++;

                    $output->writeln("ERROR");
                }
                sleep(1);
                if($counter >= 25)
                    break;
            }

        } catch (Exception $e) {
            $output->writeln("ERROR");
        }

        $output->writeln("DONE");

    }
}