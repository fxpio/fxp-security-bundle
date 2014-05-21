<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Command\Role;

use Sonatra\Bundle\SecurityBundle\Command\CreateCommand as BaseCreateCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class CreateCommand extends BaseCreateCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('security:role:create')
            ->setDescription('Create a role');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityClass = $this->getContainer()->getParameter('sonatra_security.role_class');
        $entityName = $input->getArgument('name');
        $fields = $input->getOption('field');

        $this->doExecute($output, $entityClass, $entityName, $fields);
    }
}
