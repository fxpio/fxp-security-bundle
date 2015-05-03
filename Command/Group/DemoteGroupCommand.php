<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Command\Group;

use Sonatra\Bundle\SecurityBundle\Model\GroupInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DemoteGroupCommand extends AbstractAclGroupCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('security:group:demote')
            ->setDescription('Demote a group by removing a role')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(OutputInterface $output, GroupInterface $group, $roleName)
    {
        if (!$group->hasRole($roleName)) {
            $output->writeln(sprintf('Group "%s" didn\'t have "%s" role.', $group->getName(), $roleName));

            return false;
        }

        $group->removeRole($roleName);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFinishMessage()
    {
        return 'Role "%s" has been removed from group "%s".';
    }
}
