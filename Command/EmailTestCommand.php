<?php

namespace FAC\EmailBundle\Command;

use FAC\EmailBundle\Entity\Email;
use FAC\EmailBundle\Service\EmailService;
use DateTime;
use Exception;
use LogBundle\Service\LogService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Utils\LogUtils;

class EmailTestCommand extends ContainerAwareCommand {

    /** @var EmailService $emailService */
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
            ->setName('email:send-test')
            ->setDescription('Send email of test.')
            ->addArgument('email', InputArgument::REQUIRED, 'Set email to send')
            ->setHelp('i.e.: php bin/console email:send-test <email>')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $log_dir = $this->getContainer()->getParameter('log_dir');

        $report_file = 'email-test-report';
        $log_file    = 'email-test-queue_process';
        $log_error   = 'email-test-errors';

        $params = LogUtils::getLogParams(null,$this->translator, 0, "START COMMAND");
        $this->logService->createByCommand($params,$log_dir,"email",$log_file);

        set_time_limit(0);
        $output->writeln("START");

        try {

            $mail_error_counter = 0;
            $db_error_counter = 0;
            $sent_counter = 0;
            $counter = 0;

            $emailTo = $input->getArgument('email');

            /** @var Email $queueMail */
            $queueMail = new Email();
            $queueMail->setSubject("Test subject");
            $queueMail->setRecipient($emailTo);
            $queueMail->setBody("example");

            $counter++;

            $params['message'] = $this->translator->trans("MAIL IN CHARGE ".$queueMail->getRecipient());
            $this->logService->createByCommand($params,$log_dir,"email",$log_file);

            $output->writeln("MAIL IN CHARGE");
            $sent = $this->emailService->processQueueEmail($log_error, $queueMail->getSubject(), $queueMail->getRecipient(), $queueMail->getBody());
            $creation = new DateTime();
            $creation->setTimestamp(time());
            if($sent) {
                $sent_counter++;

                $params['message'] = $this->translator->trans(strftime('%Y-%m-%d %H:%M:%S', $creation->getTimestamp())." - SENT MAIL TO ".$queueMail->getRecipient());
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

            sleep(1);


            $params['message'] = $this->translator->trans("Email Queue Process Command");
            $this->logService->createByCommand($params,$log_dir,"email",$report_file);
            $params['message'] = $this->translator->trans("Number of email to process: 1");
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