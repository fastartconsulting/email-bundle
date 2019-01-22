<?php

namespace FAC\EmailBundle\Command;

use BadgeBundle\Document\Badge;
use BadgeBundle\Service\BadgeService;
use DateTime;
use FAC\EmailBundle\Entity\Email;
use FAC\EmailBundle\Entity\EmailNewsletterCheck;
use FAC\EmailBundle\Entity\Newsletter;
use FAC\EmailBundle\Entity\ProfileNewsletter;
use FAC\EmailBundle\Service\EmailNewsletterCheckService;
use FAC\EmailBundle\Service\EmailService;
use FAC\EmailBundle\Service\NewsletterService;
use FAC\EmailBundle\Service\ProfileNewsletterService;
use Exception;
use LogBundle\Service\LogService;
use ProfileBundle\Entity\Setting;
use ProfileBundle\Service\ProfileService;
use ProfileBundle\Service\SettingService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Environment;
use Utils\LogUtils;

class QueueEmailsNewsletterScanningCommand extends ContainerAwareCommand {
    private $emailService;
    private $newsletterService;
    private $settingService;
    private $profileNewsletterService;
    private $profileService;
    private $emailNewsletterCheckService;
    private $templating;
    private $badgeService;

    /** @var TranslatorInterface $translator */
    private $translator;

    /** @var LogService $logService */
    private $logService;

    public function __construct(NewsletterService $newsletterService,
                                EmailService $emailService,
                                SettingService $settingService,
                                ProfileNewsletterService $profileNewsletterService,
                                ProfileService $profileService,
                                EmailNewsletterCheckService $emailNewsletterCheckService,
                                Twig_Environment $templating,
                                BadgeService $badgeService,
                                TranslatorInterface $translator,
                                LogService $logService
    ) {
        $this->newsletterService            = $newsletterService;
        $this->emailService                 = $emailService;
        $this->settingService               = $settingService;
        $this->profileNewsletterService     = $profileNewsletterService;
        $this->profileService               = $profileService;
        $this->emailNewsletterCheckService  = $emailNewsletterCheckService;
        $this->badgeService  = $badgeService;
        $this->templating = $templating;
        $this->translator   = $translator;
        $this->logService   = $logService;
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('email-newsletter:queue-emails')
            ->setDescription('Start to queue email newsletter.')
            ->setHelp('Start to queue email newsletter.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $log_dir = $this->getContainer()->getParameter('log_dir');

        $report_file = 'report';
        $log_file    = 'newsletter-scanning';

        $params = LogUtils::getLogParams(null,$this->translator, 0, "START COMMAND");
        $this->logService->createByCommand($params,$log_dir,"email",$log_file);

        set_time_limit(0);
        $output->writeln("START");

        $sourceSetting = 'email.update';
        /** @var Setting $settingNewsletter */
        $settingNewsletter = $this->settingService->getBySources(array('source' => $sourceSetting));
        if(is_null(($settingNewsletter))){
            return;
        }

        try {
            $newsletter_list = $this->newsletterService->getNotQueuing();

            if(is_null($newsletter_list)) {

                $params['message'] = $this->translator->trans("NO NEWSLETTERS FOUND");
                $this->logService->createByCommand($params,$log_dir,"email",$log_file);

                $output->writeln("NO NEWSLETTERS FOUND");
                return;
            }


            $emailNewsletterChecks = $this->emailNewsletterCheckService->getListByAttributes(array('isDisable' => 0));

            //Get ids newsletter for check profiles who just sent newsletter
            $newsletters = array();
            /** @var Newsletter $newsletter */
            foreach ($newsletter_list as $newsletter) {
                $newsletters[] = $newsletter;
            }

            $profileNewsletters = $this->profileNewsletterService->getAllByNewsletters($newsletters);

            $profileNewslettersMatch = array();
            /** @var ProfileNewsletter $profileNewsletter */
            foreach($profileNewsletters as $profileNewsletter) {
                $profileNewslettersMatch[$profileNewsletter->getNewsletter()->getId()][] = $profileNewsletter->getEmail();
            }

            $newsletter_error_counter = 0;
            $db_error_counter = 0;
            $sent_counter = 0;
            $counter = 0;

            $ids_profiles = array();
            /** @var EmailNewsletterCheck $emailNewsletterCheck */
            foreach($emailNewsletterChecks as $emailNewsletterCheck) {
                $ids_profiles[] = $emailNewsletterCheck->getIdProfile();
            }

            $emails = array();
            $badges = null;
            /** @var Newsletter $newsletter */
            foreach ($newsletter_list as $newsletter) {
                $counter++;

                $params['message'] = $this->translator->trans("NEWSLETTER IN CHARGE ".$newsletter->getId());
                $this->logService->createByCommand($params,$log_dir,"email",$log_file);

                $output->writeln("NEWSLETTER IN CHARGE");

                /** @var EmailNewsletterCheck $emailNewsletterCheck */
                foreach($emailNewsletterChecks as $emailNewsletterCheck) {

                    if(!empty($profileNewslettersMatch)) {
                        //Check if just send
                        if(in_array($emailNewsletterCheck->getEmail(), $profileNewslettersMatch[$newsletter->getId()])) {
                            continue;
                        }
                    }

                    //Get List
                    $mailingListType = $newsletter->getMailingListType();
                    switch($mailingListType){
                        case Newsletter::MAILING_LIST_ALL:
                            $emails[] = array(
                                'email'     => $emailNewsletterCheck->getEmail(),
                                'subject'   => $newsletter->getTemplateNewsletter()->getSubject(),
                                'body'      => $newsletter->getTemplateNewsletter()->getBody(),
                                'sendOn'    => $newsletter->getSendOn(),
                                'idProfile' => $emailNewsletterCheck->getIdProfile(),
                                'newsletter'=> $newsletter
                            );
                            break;
                        case Newsletter::MAILING_LIST_PATIENTS:
                            if(!$emailNewsletterCheck->getIsBadged() && !is_null($emailNewsletterCheck->getIdProfile())) {
                                $emails[] = array(
                                    'email' => $emailNewsletterCheck->getEmail(),
                                    'subject' => $newsletter->getTemplateNewsletter()->getSubject(),
                                    'body' => $newsletter->getTemplateNewsletter()->getBody(),
                                    'sendOn' => $newsletter->getSendOn(),
                                    'idProfile' => $emailNewsletterCheck->getIdProfile(),
                                    'newsletter'=> $newsletter
                                );
                            }
                            break;
                        case Newsletter::MAILING_LIST_BADGES:
                            if($emailNewsletterCheck->getIsBadged() && !is_null($emailNewsletterCheck->getIdProfile())) {
                                $emails[] = array(
                                    'email' => $emailNewsletterCheck->getEmail(),
                                    'subject' => $newsletter->getTemplateNewsletter()->getSubject(),
                                    'body' => $newsletter->getTemplateNewsletter()->getBody(),
                                    'sendOn' => $newsletter->getSendOn(),
                                    'idProfile' => $emailNewsletterCheck->getIdProfile(),
                                    'newsletter'=> $newsletter
                                );
                            }
                            break;
                        case Newsletter::MAILING_LIST_SPECIFIC_BADGE:
                            if(is_null($badges)) {
                                $badges = $this->badgeService->getByProfileIds($ids_profiles);
                            }

                            if(!empty($badges)) {
                                $idBadgeStructureMailingList = $newsletter->getIdBadgeStructureMailingList();

                                /** @var Badge $badge */
                                foreach ($badges as $badge) {
                                    if ($badge->getProfile()->getId() == $emailNewsletterCheck->getIdProfile()
                                    && $badge->getIdBadgeStructure()  == $idBadgeStructureMailingList
                                    && !is_null($emailNewsletterCheck->getIdProfile())) {
                                        $emails[] = array(
                                            'email' => $emailNewsletterCheck->getEmail(),
                                            'subject' => $newsletter->getTemplateNewsletter()->getSubject(),
                                            'body' => $newsletter->getTemplateNewsletter()->getBody(),
                                            'sendOn' => $newsletter->getSendOn(),
                                            'idProfile' => $emailNewsletterCheck->getIdProfile(),
                                            'newsletter'=> $newsletter
                                        );
                                    }

                                }
                            }
                            break;
                    }
                }
            }

            if(empty($emails)) {
                /** @var Newsletter $newsletter */
                foreach($newsletters as $newsletter){
                    $newsletter->setQueuing(1);
                    $this->newsletterService->save($newsletter);
                }
            }

            //queue emails and register the profileNewsletter
            $creation = new DateTime();
            $creation->setTimestamp(time());
            foreach($emails as $email) {
                try {
                    if (!$this->emailService->emailEnqueue(
                        $email['email'],
                        $email['subject'],
                        $this->templating->render(
                            "email/newsletter.template.email.twig",
                            array(
                                'subject'    => $email['subject'],
                                'html_body'  => $email['body']
                            )
                        ),
                        null,
                        null,
                        Email::TYPE_GENERIC_NEWSLETTER,
                        $email['sendOn']
                    )) {
                        $counter++;
                        $newsletter_error_counter++;
                        $db_error_counter++;
                        continue;
                    }
                    else{
                        $counter++;
                        $profileNewsletter = new ProfileNewsletter();
                        $profileNewsletter->setIdProfile($email['idProfile']);
                        $profileNewsletter->setEmail($email['email']);
                        $profileNewsletter->setNewsletter($email['newsletter']);
                        $profileNewsletter->setCreatedOn($creation);

                        if(!$this->profileNewsletterService->save($profileNewsletter)) {
                            $newsletter_error_counter++;
                            $db_error_counter++;
                        }
                        else{
                            $sent_counter++;
                        }
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }


            $params['message'] = $this->translator->trans("Newsletter Scanning Command");
            $this->logService->createByCommand($params,$log_dir,"email",$report_file);
            $params['message'] = $this->translator->trans("Number of Newsletter to scann: ".count($newsletter_list));
            $this->logService->createByCommand($params,$log_dir,"email",$report_file);
            $params['message'] = $this->translator->trans("Number of Newsletter processed: $counter");
            $this->logService->createByCommand($params,$log_dir,"email",$report_file);
            $params['message'] = $this->translator->trans("Number of Newsletter sent: $sent_counter");
            $this->logService->createByCommand($params,$log_dir,"email",$report_file);
            $params['message'] = $this->translator->trans("Number of sending errors: $newsletter_error_counter");
            $this->logService->createByCommand($params,$log_dir,"email",$report_file);
            $params['message'] = $this->translator->trans("Number of database errors: $db_error_counter");
            $this->logService->createByCommand($params,$log_dir,"email",$report_file);


            $params['message'] = $this->translator->trans("STOP COMMAND");
            $this->logService->createByCommand($params,$log_dir,"email",$log_file);

        } catch (Exception $e) {
            $params['message'] = $this->translator->trans("NEWSLETTER FATAL ERROR: ".json_encode($e));
            $this->logService->createByCommand($params,$log_dir,"email",$report_file);
        }

        $output->writeln("DONE");

    }
}