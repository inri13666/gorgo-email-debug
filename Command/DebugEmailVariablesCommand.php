<?php

namespace Gorgo\Bundle\EmailDebugBundle\Command;

use Oro\Bundle\EmailBundle\Provider\EmailRenderer;
use Oro\Bundle\EmailBundle\Provider\VariablesProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DebugEmailVariablesCommand extends Command
{
    const NAME = 'gorgo:debug:email:variable';

    /** @var EmailRenderer */
    protected $emailRenderer;

    /** @var VariablesProvider */
    protected $variablesProvider;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /**
     * @param EmailRenderer $emailRenderer
     * @param VariablesProvider $variablesProvider
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        EmailRenderer $emailRenderer,
        VariablesProvider $variablesProvider,
        DoctrineHelper $doctrineHelper
    ) {
        parent::__construct(self::NAME);

        $this->emailRenderer = $emailRenderer;
        $this->variablesProvider = $variablesProvider;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@internaldoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Displays current email template variables for an application')
            ->addOption(
                'entity-class',
                null,
                InputOption::VALUE_OPTIONAL,
                'Entity class.'
            )
            ->addOption(
                'entity-id',
                null,
                InputOption::VALUE_OPTIONAL,
                'Entity ID.'
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('System Variables');
        $this->processSystemVariables($output);

        if ($input->getOption('entity-class')) {
            $output->writeln('');
            $output->writeln('Entity Variables');
            $this->processEntityVariables($output, $input->getOption('entity-class'), $input->getOption('entity-id'));
        }

        return 0;
    }

    /**
     * @param OutputInterface $output
     */
    private function processSystemVariables(OutputInterface $output)
    {
        $table = new Table($output);
        $headers = [
            'Name',
            'Title',
            'Type',
            'Value',
        ];

        $table->setHeaders($headers)->setRows([]);
        foreach ($this->getVariableProvider()->getSystemVariableDefinitions() as $variable => $definition) {
            $data = [
                'system.' . $variable,
                isset($definition['label']) ? $definition['label'] : 'N/A',
                isset($definition['type']) ? $definition['type'] : 'mixed',
            ];
            $data[] = $this->getEmailRenderer()->renderWithDefaultFilters(sprintf('{{ system.%s }}', $variable));

            $table->addRow($data);
        }
        $table->render();
    }

    /**
     * @return object|EmailRenderer
     */
    private function getEmailRenderer()
    {
        return $this->emailRenderer;
    }

    /**
     * @param OutputInterface $output
     * @param string $entityClass
     * @param null|mixed $entityId
     */
    private function processEntityVariables(OutputInterface $output, $entityClass, $entityId = null)
    {
        $entityClass = $this->getEntityClass($entityClass);
        $entity = $entityId ? $this->getEntity($entityClass, $entityId) : null;

        $table = new Table($output);
        $headers = [
            'Name',
            'Title',
            'Type',
        ];

        if ($entity) {
            $headers[] = 'Value';
        }

        $table->setHeaders($headers)->setRows([]);
        $variables = $this->getVariableProvider()->getEntityVariableDefinitions($entityClass);

        foreach ($variables as $variable => $definition) {
            $data = [
                'entity.' . $variable,
                $definition['label'],
                $definition['type'],
            ];

            if ($entity) {
                if (!in_array($definition['type'], ['image'], true)) {
                    $data[] = $this->getEmailRenderer()->renderWithDefaultFilters(
                        sprintf('{{ entity.%s }}', $variable),
                        ['entity' => $entity]
                    );
                } else {
                    $data[] = sprintf('<info>Type "%s" skipped for CLI</info>', $definition['type']);
                }
            }
            $table->addRow($data);
        }

        $table->render();
    }

    /**
     * @param string $entityClass
     * @param null|mixed $entityId
     *
     * @return object
     */
    private function getEntity($entityClass, $entityId = null)
    {
        /** @var DoctrineHelper $dh */
        $dh = $this->doctrineHelper;
        $entity = $dh->createEntityInstance($entityClass);
        if ($entityId) {
            $entity = $dh->getEntity($entityClass, $entityId) ?: $entity;
        }

        return $entity;
    }

    /**
     * @param string $entityClass
     *
     * @return string
     */
    private function getEntityClass($entityClass = null)
    {
        return $this->doctrineHelper->getEntityClass($entityClass);
    }

    /**
     * @return object|VariablesProvider
     */
    private function getVariableProvider()
    {
        return $this->variablesProvider;
    }
}
