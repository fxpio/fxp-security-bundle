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
class AclRoleRemoveCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('acl:role:remove')
        ->setDescription('Removes a Role')
        ->setDefinition(array(
                new InputArgument('role-name', InputArgument::REQUIRED, 'The role name to delete'),
         ));
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $roleClass = str_replace('/', '\\', $this->getContainer()->getParameter('sonatra_security.role_class'));
            $roleName = $input->getArgument('role-name');

            $em = $this->getContainer()->get('doctrine')->getManagerForClass($roleClass);
            $repo = $em->getRepository($roleClass);
            $role = $repo->findOneBy(array('name' => $roleName));

            if (null === $role) {
                throw new \InvalidArgumentException("The role '$roleName' does not exist");
            }

            $em->remove($role);
            $em->flush();

            $output->writeln(array('', "The role <info>$roleName</info> was successfully removed"));

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
