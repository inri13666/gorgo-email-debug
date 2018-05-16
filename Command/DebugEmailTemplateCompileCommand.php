<?php

namespace Gorgo\Bundle\EmailDebugBundle\Command;

use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EmailBundle\Entity\Repository\EmailTemplateRepository;
use Oro\Bundle\EmailBundle\Mailer\Processor;
use Oro\Bundle\EmailBundle\Provider\EmailRenderer;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class DebugEmailTemplateCompileCommand extends Command
{
    const NAME = 'gorgo:debug:email:template:compile';

    /** @var Processor */
    protected $mailerProcessor;

    /** @var EmailRenderer */
    protected $emailRenderer;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /**
     * @param Processor $mailerProcessor
     * @param EmailRenderer $emailRenderer
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        Processor $mailerProcessor,
        EmailRenderer $emailRenderer,
        DoctrineHelper $doctrineHelper
    ) {
        parent::__construct(self::NAME);

        $this->mailerProcessor = $mailerProcessor;
        $this->emailRenderer = $emailRenderer;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@internaldoc}
     */
    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Renders given email template')
            ->addOption(
                'template',
                null,
                InputOption::VALUE_OPTIONAL,
                'The name of email template to be compiled.'
            )
            ->addOption(
                'params-file',
                null,
                InputOption::VALUE_OPTIONAL,
                'Path to YML file with required params for compilation.'
            )
            ->addOption(
                'entity-id',
                null,
                InputOption::VALUE_OPTIONAL,
                'An entity ID.'
            )
            ->addOption(
                'recipient',
                null,
                InputOption::VALUE_OPTIONAL,
                'Recipient email address. [Default: null]',
                null
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $templateName = $input->getOption('template');
        $template = $this->getRepository()->findByName($templateName);
        if (!$template) {
            $output->writeln(sprintf('Template "%s" not found', $templateName));

            return 1;
        }
        $params = $this->getNormalizedParams($input->getOption('params-file'));

        if ($template->getEntityName()) {
            $params['entity'] = $this->getEntity($template->getEntityName(), $input->getOption('entity-id'));
        }

        $subject = $this->getEmailRenderer()->renderWithDefaultFilters($template->getSubject(), $params);
        $body = $this->getEmailRenderer()->renderWithDefaultFilters($template->getContent(), $params);

        if (!$input->getOption('recipient')) {
            $output->writeln(sprintf('SUBJECT: %s', $subject));
            $output->writeln('');
            $output->writeln('BODY:');
            $output->writeln($body);
        } else {
            $emailMessage = new \Swift_Message(
                $subject,
                $body,
                $template->getType() === 'html' ? 'text/html' : null
            );

            $emailMessage->setFrom($input->getOption('recipient'));
            $emailMessage->setTo($input->getOption('recipient'));

            $this->getMailer()->processSend($emailMessage, null);
            $output->writeln(sprintf('Message successfully send to "%s"', $input->getOption('recipient')));
        }

        return 0;
    }

    /**
     * @return ObjectRepository|EmailTemplateRepository
     */
    private function getRepository()
    {
        return $this->doctrineHelper->getEntityRepositoryForClass(EmailTemplate::class);
    }

    /**
     * @return object|Processor
     */
    private function getMailer()
    {
        return $this->mailerProcessor;
    }

    /**
     * @return object|EmailRenderer
     */
    private function getEmailRenderer()
    {
        return $this->emailRenderer;
    }

    /**
     * @param string $paramsFile
     *
     * @return array
     */
    private function getNormalizedParams($paramsFile)
    {
        if (is_file($paramsFile) && is_readable($paramsFile)) {
            return Yaml::parse(file_get_contents($paramsFile));
        }

        return [];
    }

    /**
     * @param string $entityClass
     * @param null|mixed $entityId
     *
     * @return object
     */
    private function getEntity($entityClass, $entityId = null)
    {
        $entity = $this->doctrineHelper->createEntityInstance($entityClass);
        if ($entityId) {
            $entity = $this->doctrineHelper->getEntity($entityClass, $entityId) ?: $entity;
        }

        return $entity;
    }
}
