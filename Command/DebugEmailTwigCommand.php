<?php

namespace Gorgo\Bundle\EmailDebugBundle\Command;

use Symfony\Bridge\Twig\Command\DebugCommand;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class DebugEmailTwigCommand extends DebugCommand implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    const NAME = 'gorgo:debug:email:twig';

    /**
     * {@internaldoc}
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName(self::NAME)
            ->setDescription('Shows a list of twig functions, filters, globals and tests for Email Sandbox');
    }

    /**
     * {@inheritDoc}
     */
    protected function getTwigEnvironment()
    {
        return $this->container->get('oro_email.email_renderer');
    }
}
