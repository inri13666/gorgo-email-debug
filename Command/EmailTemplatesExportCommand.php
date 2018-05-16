<?php

namespace Gorgo\Bundle\EmailDebugBundle\Command;

use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class EmailTemplatesExportCommand extends Command
{
    const NAME = 'gorgo:email:template:export';

    /** @var User */
    protected $adminUser;

    /** @var Organization */
    protected $organization;

    /** @var KernelInterface */
    protected $kernel;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /**
     * @param KernelInterface $kernel
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(KernelInterface $kernel, DoctrineHelper $doctrineHelper)
    {
        parent::__construct(self::NAME);

        $this->kernel = $kernel;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->addArgument('destination', InputArgument::REQUIRED, "Folder to export")
            ->addOption('template', null, InputOption::VALUE_OPTIONAL, "template name")
            ->setDescription('Exports email templates');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $destination = $input->getArgument('destination');
        try {
            $destination = $this->kernel->locateResource($destination);
        } catch (\InvalidArgumentException $e) {
        }

        if (!is_dir($destination) || !is_writable($destination)) {
            $output->writeln(sprintf('<error>Destination path "%s" should be writable folder</error>', $destination));

            return 1;
        }

        $templates = $this->getEmailTemplates($input->getOption('template'));
        $output->writeln(sprintf('Found %d templates for export', count($templates)));

        /** @var EmailTemplate $template */
        foreach ($templates as $template) {
            $content = sprintf(
                "@name = %s\n@entityName = %s\n@subject = %s\n@isSystem = %d\n@isEditable = %d\n@locale = %s\n\n%s",
                $template->getName(),
                $template->getEntityName(),
                $template->getSubject(),
                $template->getIsSystem(),
                $template->getIsEditable(),
                $template->getLocale(),
                $template->getContent()
            );

            $filename = sprintf(
                "%s.%s.twig",
                preg_replace('/[^a-z0-9\._-]+/i', '', $template->getName()),
                $template->getType() ?: 'html'
            );

            file_put_contents(
                $destination . DIRECTORY_SEPARATOR . $filename,
                $content
            );
        }
    }

    /**
     * @param null $templateName
     *
     * @return EmailTemplate[]
     * @throws \UnexpectedValueException
     */
    private function getEmailTemplates($templateName = null)
    {
        $criterion = [];
        if ($templateName) {
            $criterion = ['name' => $templateName];
        }

        return $this->doctrineHelper->getEntityRepositoryForClass(EmailTemplate::class)->findBy($criterion);
    }
}
