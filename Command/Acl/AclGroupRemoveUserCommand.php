<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Command\Acl;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclGroupRemoveUserCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('acl:group:remove:user')
        ->setDescription('Remove user in group')
        ->setDefinition(array(
                new InputArgument('group-name', InputArgument::REQUIRED, 'The group name'),
                new InputArgument('user-name', InputArgument::REQUIRED, 'The user name to dissociated in group'),
         ));
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            // find group
            $groupClass = str_replace('/', '\\', $this->getContainer()->getParameter('sonatra_security.group_class'));
            $groupName = $input->getArgument('group-name');
            $em = $this->getContainer()->get('doctrine')->getManagerForClass($groupClass);
            $repo = $em->getRepository($groupClass);
            $group = $repo->findOneBy(array('name' => $groupName));

            if (null === $group) {
                throw new \InvalidArgumentException("The group '$groupName' does not exist");
            }

            // find user
            $userClass = str_replace('/', '\\', $this->getContainer()->getParameter('sonatra_security.user_class'));
            $userName = $input->getArgument('user-name');
            $em = $this->getContainer()->get('doctrine')->getManagerForClass($userClass);
            $repo = $em->getRepository($userClass);
            $user = $repo->findOneBy(array('username' => $userName));

            if (null === $user) {
                throw new \InvalidArgumentException("The user '$userName' does not exist");
            }

            if (!in_array($group->getName(), $user->getGroupNames())) {
                throw new \InvalidArgumentException("The user '$userName' is no associated with group '$groupName'");
            }

            $user->removeGroup($group);
            $em->persist($user);
            $em->flush();

            $output->writeln(array('', "The user <info>$userName</info> was successfully dissociated with group <info>$groupName</info>"));

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
