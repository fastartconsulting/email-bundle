<?php

namespace FAC\EmailBundle\Command;

use FAC\EmailBundle\Entity\Email;
use FAC\EmailBundle\Service\EmailService;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;
use LogBundle\Service\LogService;
use Utils\LogUtils;

class EmailQueueScanningCommand extends ContainerAwareCommand {

    private $emailService;

    /** @var TranslatorInterface $translator */
    private $translator;

    /** @var LogService $logService */
    private $logService;

    public function __construct(EmailService $emailService, TranslatorInterface $translator, LogService $logService) {
        $this->emailService = $emailService;
        $this->translator   = $translator;
        $this->logService   = $logService;

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
        $log_dir = $this->getContainer()->getParameter('log_dir');

        $report_file    = 'report';
        $log_file       = 'email-queue_process';
        $log_error      = 'email-errors';

        /** @var EmailService $emailService */
        $emailService = $this->emailService;


        $params = LogUtils::getLogParams(null,$this->translator, 0, "START COMMAND");
        $this->logService->createByCommand($params,$log_dir,"email",$log_file);

        $output->writeln("START");

        try {
            $queue_mail_list = $emailService->getNotSent();

            if(is_null($queue_mail_list)) {
                $params['message'] = $this->translator->trans("NO MAILS FOUND");
                $this->logService->createByCommand($params,$log_dir,"email",$log_file);
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

                $params['message'] = $this->translator->trans("MAIL IN CHARGE ".$queueMail->getRecipient()." (ID: ".$queueMail->getId().")");
                $this->logService->createByCommand($params,$log_dir,"email",$log_file);

                $output->writeln("MAIL IN CHARGE");
                $sent = $emailService->processQueueEmail($log_error, $queueMail->getSubject(), $queueMail->getRecipient(), $queueMail->getBody());
                $creation = new DateTime();
                $creation->setTimestamp(time());
                if($sent) {
                    $sent_counter++;

                    $params['message'] = $this->translator->trans(strftime('%Y-%m-%d %H:%M:%S', $creation->getTimestamp())." - SENT MAIL TO ".$queueMail->getRecipient()." (ID: ".$queueMail->getId().")");
                    $this->logService->createByCommand($params,$log_dir,"email",$log_file);

                    $queueMail->setSendOn($creation);
                    $queueMail->setStatus($queueMail::STATUS_SENDED);
                    $output->writeln("SENT");
                } else {
                    $mail_error_counter++;

                    $params['message'] = $this->translator->trans("SENDING ERROR");
                    $this->logService->createByCommand($params,$log_dir,"email",$log_file);

                    $queueMail->setFailedOn($creation);
                    $queueMail->setStatus($queueMail::STATUS_FAILED);
                    $output->writeln("NOT SENT");
                }

                if(!$emailService->save($queueMail)) {
                    $db_error_counter++;

                    $params['message'] = $this->translator->trans("DB NOT UPDATED CORRECTLY (ID: ".$queueMail->getId().")");
                    $this->logService->createByCommand($params,$log_dir,"email",$log_file);

                    $output->writeln("ERROR");
                }
                sleep(1);
                if($counter >= 25)
                    break;
            }

            $params['message'] = $this->translator->trans("Email Queue Process Command");
            $this->logService->createByCommand($params,$log_dir,"email",$report_file);
            $params['message'] = $this->translator->trans("Number of email to process: ".count($queue_mail_list));
            $this->logService->createByCommand($params,$log_dir,"email",$report_file);
            $params['message'] = $this->translator->trans("Number of email processed: $counter");
            $this->logService->createByCommand($params,$log_dir,"email",$report_file);
            $params['message'] = $this->translator->trans("Number of email sent: $sent_counter");
            $this->logService->createByCommand($params,$log_dir,"email",$report_file);
            $params['message'] = $this->translator->trans("Number of sending errors: $mail_error_counter");
            $this->logService->createByCommand($params,$log_dir,"email",$report_file);
            $params['message'] = $this->translator->trans("Number of database errors: $db_error_counter");
            $this->logService->createByCommand($params,$log_dir,"email",$report_file);


            $params['message'] = $this->translator->trans("STOP COMMAND");
            $this->logService->createByCommand($params,$log_dir,"email",$log_file);
        } catch (Exception $e) {
            $params['message'] = $this->translator->trans("EMAIL FATAL ERROR: ".json_encode($e));
            $this->logService->createByCommand($params,$log_dir,"email",$report_file);
        }

        $output->writeln("DONE");

    }
}