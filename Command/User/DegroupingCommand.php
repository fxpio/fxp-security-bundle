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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sonatra\Bundle\SecurityBundle\Exception\InvalidArgumentException;
use Sonatra\Bundle\SecurityBundle\Exception\LogicException;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DegroupingCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('security:user:degrouping')
        ->setDescription('Remove a group in user')
        ->setDefinition(array(
                new InputArgument('username', InputArgument::OPTIONAL, 'The username'),
                new InputArgument('group', InputArgument::OPTIONAL, 'The group'),
         ));
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // find user
        $userClass = str_replace('/', '\\', $this->getContainer()->getParameter('sonatra_security.user_class'));
        $userName = $input->getArgument('username');
        $emUser = $this->getContainer()->get('doctrine')->getManagerForClass($userClass);
        $repoUser = $emUser->getRepository($userClass);
        $user = $repoUser->findOneBy(array('username' => $userName));

        if (null === $user) {
            throw new InvalidArgumentException(sprintf('The user "%s" does not exist', $userName));
        }

        // find group
        $groupClass = str_replace('/', '\\', $this->getContainer()->getParameter('sonatra_security.group_class'));
        $groupName = $input->getArgument('group');
        $emGroup = $this->getContainer()->get('doctrine')->getManagerForClass($groupClass);
        $repoGroup = $emGroup->getRepository($groupClass);
        $group = $repoGroup->findOneBy(array('name' => $groupName));

        if (null === $group) {
            throw new InvalidArgumentException(sprintf('The group "%s" does not exist', $groupName));
        }

        if (!$user->hasGroup($groupName)) {
            $output->writeln(sprintf('User "%s" didn\'t have "%s" group.', $userName, $groupName));

            return;
        }

        $user->removeGroup($group);

        $errorList = $this->getContainer()->get('validator')->validate($user);

        if (count($errorList) > 0) {
            $msg = sprintf('Validation errors for "%s":%s', get_class($user), PHP_EOL);

            foreach ($errorList as $error) {
                $msg = sprintf('%s%s: %s', PHP_EOL, $error->getPropertyPath(), $error->getMessage());
            }

            throw new LogicException($msg);
        }

        $emUser->persist($user);
        $emUser->flush();

        $output->writeln(sprintf('Group "%s" has been removed from user "%s".', groupName, $userName));
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('username')) {
            $username = $this->getHelper('dialog')->askAndValidate(
                    $output,
                    'Please choose a username:',
                    function($username) {
                        if (empty($username)) {
                            throw new LogicException('Username can not be empty');
                        }

                        return $username;
                    }
            );

            $input->setArgument('username', $username);
        }

        if (!$input->getArgument('group')) {
            $group = $this->getHelper('dialog')->askAndValidate(
                    $output,
                    'Please choose a group:',
                    function($group) {
                        if (empty($group)) {
                            throw new LogicException('Group can not be empty');
                        }

                        return $group;
                    }
            );

            $input->setArgument('group', $group);
        }
    }
}
