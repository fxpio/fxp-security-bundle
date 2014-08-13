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
class AddRoleChildCommand extends AbstractActionChildCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('security:role:child:add')
            ->setDescription('Add role child')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(OutputInterface $output, RoleHierarchisableInterface $role, RoleHierarchisableInterface $child)
    {
        if ($role->hasChild($child->getRole())) {
            $output->writeln(sprintf('Role "%s" did already have "%s" child role.', $role->getRole(), $child->getRole()));

            return false;
        }

        $role->addChild($child);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFinishMessage()
    {
        return 'Child role "%s" has been added to role "%s".';
    }
}
