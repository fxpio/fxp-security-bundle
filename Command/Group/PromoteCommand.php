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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sonatra\Bundle\SecurityBundle\Exception\InvalidArgumentException;
use Sonatra\Bundle\SecurityBundle\Exception\LogicException;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PromoteCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('security:group:promote')
        ->setDescription('Promotes a group by adding a role')
        ->setDefinition(array(
                new InputArgument('group', InputArgument::OPTIONAL, 'The group'),
                new InputArgument('role', InputArgument::OPTIONAL, 'The role'),
         ));
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // find group
        $groupClass = str_replace('/', '\\', $this->getContainer()->getParameter('sonatra_security.group_class'));
        $groupName = $input->getArgument('group');
        $roleName = $input->getArgument('role');
        $em = $this->getContainer()->get('doctrine')->getManagerForClass($groupClass);
        $repo = $em->getRepository($groupClass);
        $group = $repo->findOneBy(array('name' => $groupName));

        if (null === $group) {
            throw new InvalidArgumentException(sprintf('The group "%s" does not exist', $groupName));
        }

        if ($group->hasRole($roleName)) {
            $output->writeln(sprintf('Group "%s" did already have "%s" role.', $groupName, $roleName));

            return;
        }

        $group->addRole($roleName);

        $errorList = $this->getContainer()->get('validator')->validate($group);

        if (count($errorList) > 0) {
            $msg = sprintf('Validation errors for "%s":%s', get_class($group), PHP_EOL);

            foreach ($errorList as $error) {
                $msg = sprintf('%s%s: %s', PHP_EOL, $error->getPropertyPath(), $error->getMessage());
            }

            throw new LogicException($msg);
        }

        $em->persist($group);
        $em->flush();

        $output->writeln(sprintf('Role "%s" has been added to group "%s".', $roleName, $groupName));
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('group')) {
            $group = $this->getHelper('dialog')->askAndValidate(
                    $output,
                    'Please choose a group:',
                    function ($group) {
                        if (empty($group)) {
                            throw new LogicException('Group can not be empty');
                        }

                        return $group;
                    }
            );

            $input->setArgument('group', $group);
        }

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
    }
}
