<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Command\Role;

use Symfony\Component\Console\Output\OutputInterface;
use Sonatra\Bundle\SecurityBundle\Model\RoleHierarchisableInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AddRoleParentCommand extends AbstractActionParentCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('security:role:parent:add')
            ->setDescription('Add role parent')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(OutputInterface $output, RoleHierarchisableInterface $role, RoleHierarchisableInterface $parent)
    {
        if ($role->hasParent($parent->getRole())) {
            $output->writeln(sprintf('Role "%s" did already have "%s" parent role.', $role->getRole(), $parent->getRole()));

            return false;
        }

        $role->addParent($parent);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFinishMessage()
    {
        return 'Parent role "%s" has been added to role "%s".';
    }
}
