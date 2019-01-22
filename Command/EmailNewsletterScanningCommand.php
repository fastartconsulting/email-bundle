<?php

namespace FAC\EmailBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use FAC\EmailBundle\Entity\EmailNewsletterCheck;
use FAC\EmailBundle\Service\EmailNewsletterCheckService;
use DateTime;
use LogBundle\Service\LogService;
use ProfileBundle\Document\Profile;
use ProfileBundle\Entity\Setting;
use ProfileBundle\Service\ProfileService;
use ProfileBundle\Service\ProfileSettingService;
use ProfileBundle\Service\SettingService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Utils\LogUtils;

class EmailNewsletterScanningCommand extends ContainerAwareCommand {
    private $settingService;
    private $profileService;
    private $emailNewsletterCheckService;
    private $profileSettingService;
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var TranslatorInterface $translator */
    private $translator;

    /** @var LogService $logService */
    private $logService;

    public function __construct(SettingService $settingService,
                                ProfileService $profileService,
                                EmailNewsletterCheckService $emailNewsletterCheckService,
                                ProfileSettingService $profileSettingService,
                                EntityManagerInterface $entityManager,
                                TranslatorInterface $translator,
                                LogService $logService
    ) {
        $this->settingService               = $settingService;
        $this->profileService               = $profileService;
        $this->profileSettingService        = $profileSettingService;
        $this->emailNewsletterCheckService  = $emailNewsletterCheckService;
        $this->entityManager        = $entityManager;
        $this->translator   = $translator;
        $this->logService   = $logService;
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('email-newsletter:scanning')
            ->setDescription('Start to scanning emails newsletter.')
            ->setHelp('Start to scanning emails newsletter.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $log_dir = $this->getContainer()->getParameter('log_dir');

        $log_file = 'email-newsletter-scanning';

        $params = LogUtils::getLogParams(null,$this->translator, 0, "START COMMAND");
        $this->logService->createByCommand($params,$log_dir,"email",$log_file);

        set_time_limit(0);
        $output->writeln("START");

        $emailNewsletterChecks = $this->emailNewsletterCheckService->getListByAttributes();

        if(empty($emailNewsletterChecks)) {
            $output->writeln("DONE");
            return;
        }

        $idProfiles = array();
        /** @var EmailNewsletterCheck $emailNewsletterCheck */
        foreach($emailNewsletterChecks as $emailNewsletterCheck) {
            $idProfiles[] = $emailNewsletterCheck->getIdProfile();
        }

        $newProfiles = $this->profileService->getAllNotIdsEscaped($idProfiles);

        $creation = new DateTime();
        $creation->setTimestamp(time());
        $i = 0;
        $batchSize = 200;

        $allows_email_source = "email.update";
        /** @var Setting $setting */
        $setting = $this->settingService->getOneByAttributes(array('isDisable' => 0, 'source' => $allows_email_source));
        if(is_null($setting)) {
            return;
        }

        if(empty($newProfiles)) {
            $output->writeln("DONE");
            return;
        }

        //ADD EMAIL
        /** @var Profile $profile */
        foreach($newProfiles as $profile) {
            $i++;
            $profileSettings = $profile->getProfileSettings()->getValues();

            if($this->profileSettingService->settingAllowsBySource($allows_email_source, $profileSettings, $setting)) {
                try {
                    $emailNewsletterCheck = $this->emailNewsletterCheckService->saveEmailNewsletterCheck($profile, true);
                } catch (\Exception $e) {

                    $params['message'] = $this->translator->trans("EMAIL NEWSLETTER CHECK CREATE ERROR");
                    $this->logService->createByCommand($params,$log_dir,"email",$log_file);

                    $output->writeln("NOT CREATE");
                }

                $this->entityManager->merge($emailNewsletterCheck);

                //if (($i % 20) === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                //}
            }
        }

        $oldProfiles = $this->profileService->getAllByIds($idProfiles);

        if(empty($oldProfiles)) {
            $output->writeln("DONE");
            return;
        }

        //REMOVE EMAIL
        $i = 0;
        /** @var Profile $profile */
        foreach($oldProfiles as $profile) {
            $profileSettings = $profile->getProfileSettings()->getValues();

            if(!$this->profileSettingService->settingAllowsBySource($allows_email_source, $profileSettings, $setting)) {
                /** @var EmailNewsletterCheck $emailNewsletterCheck */
                foreach($emailNewsletterChecks as $emailNewsletterCheck) {
                    if($emailNewsletterCheck->getIdProfile() === $profile->getId()) {
                        $i++;
                        try {
                            if (($i % $batchSize) === 0) {
                                $this->emailNewsletterCheckService->delete($emailNewsletterCheck);
                            }
                        } catch (\Exception $e) {

                            $params['message'] = $this->translator->trans("EMAIL NEWSLETTER CHECK ERROR");
                            $this->logService->createByCommand($params,$log_dir,"email",$log_file);

                            $output->writeln("NOT DELETE");
                        }
                    }
                }
            }
        }


        $params['message'] = $this->translator->trans("STOP COMMAND");
        $this->logService->createByCommand($params,$log_dir,"email",$log_file);

        $output->writeln("DONE");
    }
}