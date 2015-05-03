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
class PromoteGroupCommand extends AbstractAclGroupCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('security:group:promote')
            ->setDescription('Promotes a group by adding a role')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(OutputInterface $output, GroupInterface $group, $roleName)
    {
        if ($group->hasRole($roleName)) {
            $output->writeln(sprintf('Group "%s" did already have "%s" role.', $group->getName(), $roleName));

            return false;
        }

        $group->addRole($roleName);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFinishMessage()
    {
        return 'Role "%s" has been added to group "%s".';
    }
}
