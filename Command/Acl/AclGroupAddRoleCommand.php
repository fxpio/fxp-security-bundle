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
class AclGroupAddRoleCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('acl:group:add:role')
        ->setDescription('Add role in group')
        ->setDefinition(array(
                new InputArgument('group-name', InputArgument::REQUIRED, 'The group name'),
                new InputArgument('role-name', InputArgument::REQUIRED, 'The role name to associated in group'),
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

            // find role
            $roleClass = str_replace('/', '\\', $this->getContainer()->getParameter('sonatra_security.role_class'));
            $roleName = $input->getArgument('role-name');
            $em = $this->getContainer()->get('doctrine')->getManagerForClass($roleClass);
            $repo = $em->getRepository($roleClass);
            $role = $repo->findOneBy(array('name' => $roleName));

            if (null === $role) {
                throw new \InvalidArgumentException("The role '$roleName' does not exist");
            }

            $group->addRole($role);
            $em->persist($group);
            $em->flush();

            $output->writeln(array('', "The role <info>$roleName</info> was successfully associated with group <info>$groupName</info>"));

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
