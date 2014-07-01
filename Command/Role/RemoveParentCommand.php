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

use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sonatra\Bundle\SecurityBundle\Exception\InvalidArgumentException;
use Sonatra\Bundle\SecurityBundle\Exception\LogicException;
use Sonatra\Bundle\SecurityBundle\Exception\RuntimeException;
use Sonatra\Bundle\SecurityBundle\Model\RoleHierarchisableInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class RemoveParentCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('security:role:parent:remove')
        ->setDescription('Remove parent role')
        ->setDefinition(array(
                new InputArgument('role', InputArgument::OPTIONAL, 'The role'),
                new InputArgument('parent', InputArgument::OPTIONAL, 'The parent role'),
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
        $parentName = $input->getArgument('parent');
        $em = $this->getContainer()->get('doctrine')->getManagerForClass($roleClass);
        /* @var EntityRepository $repo */
        $repo = $em->getRepository($roleClass);
        $role = $repo->findOneBy(array('name' => $roleName));
        /* @var RoleHierarchisableInterface $parent */
        $parent = $repo->findOneBy(array('name' => $parentName));

        if (null === $role) {
            throw new InvalidArgumentException(sprintf('The role "%s" does not exist', $roleName));
        }

        if (null === $parent) {
            throw new InvalidArgumentException(sprintf('The parent "%s" does not exist', $parentName));
        }

        if (!($role instanceof RoleHierarchisableInterface)) {
            $hierarchyInterface = 'Sonatra\Bundle\SecurityBundle\Model\RoleHierarchisableInterface';

            throw new RuntimeException(sprintf('The role "%s" must have a "%s" interface', $roleName, $hierarchyInterface));
        }

        if (!$role->hasParent($parentName)) {
            $output->writeln(sprintf('Role "%s" didn\'t have "%s" parent.', $roleName, $parentName));

            return;
        }

        $role->removeParent($parent);

        $errorList = $this->getContainer()->get('validator')->validate($role);

        if (count($errorList) > 0) {
            $msg = sprintf('Validation errors for "%s":%s', get_class($role), PHP_EOL);

            /* @var ConstraintViolationInterface $error */
            foreach ($errorList as $error) {
                $msg = sprintf('%s%s: %s', PHP_EOL, $error->getPropertyPath(), $error->getMessage());
            }

            throw new LogicException($msg);
        }

        $em->persist($role);
        $em->flush();

        $output->writeln(sprintf('Parent role "%s" has been removed from role "%s".', $parentName, $roleName));
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
                    function ($role) {
                        if (empty($role)) {
                            throw new LogicException('Role can not be empty');
                        }

                        return $role;
                    }
            );

            $input->setArgument('role', $role);
        }

        if (!$input->getArgument('parent')) {
            $parent = $this->getHelper('dialog')->askAndValidate(
                    $output,
                    'Please choose a parent:',
                    function ($parent) {
                        if (empty($parent)) {
                            throw new LogicException('Parent role can not be empty');
                        }

                        return $parent;
                    }
            );

            $input->setArgument('parent', $parent);
        }
    }
}
