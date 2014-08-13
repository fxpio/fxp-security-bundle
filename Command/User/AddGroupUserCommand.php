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

use FOS\UserBundle\Model\GroupInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AddGroupUserCommand extends AbstractGroupUserCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('security:user:group:add')
            ->setDescription('Add a group in user')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(OutputInterface $output, $user, GroupInterface $group)
    {
        if ($user->hasGroup($group->getName())) {
            $output->writeln(sprintf('User "%s" did already have "%s" Group.', $user->getUsername(), $group->getName()));

            return false;
        }

        $user->addGroup($group);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFinishMessage()
    {
        return 'Group "%s" has been added to user "%s".';
    }
}
