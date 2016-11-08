<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Command\User;

use Sonatra\Component\Security\Model\GroupInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class RemoveGroupUserCommand extends AbstractGroupUserCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('security:user:group:remove')
            ->setDescription('Remove a group in user')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(OutputInterface $output, $user, GroupInterface $group)
    {
        if (!$user->hasGroup($group->getName())) {
            $output->writeln(sprintf('User "%s" didn\'t have "%s" group.', $user->getUsername(), $group->getName()));

            return false;
        }

        $user = $this->validateObject($user, 'FOS\UserBundle\Model\GroupableInterface');
        $group = $this->validateObject($group, 'FOS\UserBundle\Model\GroupInterface');
        $user->removeGroup($group);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFinishMessage()
    {
        return 'Group "%s" has been removed from user "%s".';
    }
}
