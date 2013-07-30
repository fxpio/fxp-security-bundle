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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sonatra\Bundle\SecurityBundle\Model\RoleHierarchisableInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class RemoveChildCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('security:role:child:remove')
        ->setDescription('Remove child role')
        ->setDefinition(array(
                new InputArgument('role', InputArgument::OPTIONAL, 'The role'),
                new InputArgument('child', InputArgument::OPTIONAL, 'The child role'),
         ));
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // find role
        $roleClass = str_replace('/', '\\', $this->getContainer()->getParameter('sonatra_security.role_class'));
        $roleName = $input->getArgument('role');
        $childName = $input->getArgument('child');
        $em = $this->getContainer()->get('doctrine')->getManagerForClass($roleClass);
        $repo = $em->getRepository($roleClass);
        $role = $repo->findOneBy(array('name' => $roleName));
        $child = $repo->findOneBy(array('name' => $childName));

        if (null === $role) {
            throw new \InvalidArgumentException(sprintf('The role "%s" does not exist', $roleName));
        }

        if (null === $child) {
            throw new \InvalidArgumentException(sprintf('The child "%s" does not exist', $childName));
        }

        if (!($role instanceof RoleHierarchisableInterface)) {
            $hierarchyInterface = 'Sonatra\Bundle\SecurityBundle\Model\RoleHierarchisableInterface';

            throw new \RuntimeException(sprintf('The role "%s" must have a "%s" interface', $roleName, $hierarchyInterface));
        }

        if (!$role->hasChild($childName)) {
            $output->writeln(sprintf('Role "%s" didn\'t have "%s" child.', $roleName, $childName));

            return;
        }

        $role->removeChild($child);

        $errorList = $this->getContainer()->get('validator')->validate($role);

        if (count($errorList) > 0) {
            $msg = sprintf('Validation errors for "%s":%s', get_class($role), PHP_EOL);

            foreach ($errorList as $error) {
                $msg = sprintf('%s%s: %s', PHP_EOL, $error->getPropertyPath(), $error->getMessage());
            }

            throw new \Exception($msg);
        }

        $em->persist($role);
        $em->flush();

        $output->writeln(sprintf('Child Role "%s" has been removed from role "%s".', $childName, $roleName));
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('role')) {
            $role = $this->getHelper('dialog')->askAndValidate(
                    $output,
                    'Please choose a role:',
                    function($role) {
                        if (empty($role)) {
                            throw new \Exception('Role can not be empty');
                        }

                        return $role;
                    }
            );

            $input->setArgument('role', $role);
        }

        if (!$input->getArgument('child')) {
            $child = $this->getHelper('dialog')->askAndValidate(
                    $output,
                    'Please choose a child:',
                    function($child) {
                        if (empty($child)) {
                            throw new \Exception('Child role can not be empty');
                        }

                        return $child;
                    }
            );

            $input->setArgument('child', $child);
        }
    }
}
