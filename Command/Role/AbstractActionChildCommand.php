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
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Sonatra\Bundle\SecurityBundle\Exception\InvalidArgumentException;
use Sonatra\Bundle\SecurityBundle\Exception\LogicException;
use Sonatra\Bundle\SecurityBundle\Exception\RuntimeException;
use Sonatra\Bundle\SecurityBundle\Model\RoleHierarchisableInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractActionChildCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('role', InputArgument::OPTIONAL, 'The role'),
                new InputArgument('child', InputArgument::OPTIONAL, 'The role child'),
            ))
        ;
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

        if (null === $em) {
            throw new InvalidConfigurationException(sprintf('The class "%s" is not supported by the doctrine manager. Change the "sonatra_security.role_class" config', $roleClass));
        }

        /* @var EntityRepository $repo */
        $repo = $em->getRepository($roleClass);
        $role = $repo->findOneBy(array('name' => $roleName));
        /* @var RoleHierarchisableInterface $child */
        $child = $repo->findOneBy(array('name' => $childName));

        if (null === $role) {
            throw new InvalidArgumentException(sprintf('The role "%s" does not exist', $roleName));
        }

        if (null === $child) {
            throw new InvalidArgumentException(sprintf('The child "%s" does not exist', $childName));
        }

        if (!($role instanceof RoleHierarchisableInterface)) {
            $hierarchyInterface = 'Sonatra\Bundle\SecurityBundle\Model\RoleHierarchisableInterface';

            throw new RuntimeException(sprintf('The role "%s" must have a "%s" interface', $roleName, $hierarchyInterface));
        }

        if (!$this->doExecute($output, $role, $child)) {
            return;
        }

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

        $output->writeln(sprintf($this->getFinishMessage(), $childName, $roleName));
    }

    /**
     * Do execute.
     *
     * @param OutputInterface             $output
     * @param RoleHierarchisableInterface $role
     * @param RoleHierarchisableInterface $child
     *
     * @return bool
     */
    abstract protected function doExecute(OutputInterface $output, RoleHierarchisableInterface $role, RoleHierarchisableInterface$child);

    /**
     * Gets the finish message.
     *
     * @return string
     */
    abstract protected function getFinishMessage();

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

        if (!$input->getArgument('child')) {
            $child = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please choose a child:',
                function ($child) {
                    if (empty($child)) {
                        throw new LogicException('Child role can not be empty');
                    }

                    return $child;
                }
            );

            $input->setArgument('child', $child);
        }
    }
}
