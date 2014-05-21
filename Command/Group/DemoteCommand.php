<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Command\Group;

use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Sonatra\Bundle\SecurityBundle\Exception\InvalidArgumentException;
use Sonatra\Bundle\SecurityBundle\Exception\LogicException;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DemoteCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('security:group:demote')
        ->setDescription('Demote a group by removing a role')
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

        if (null === $em) {
            throw new InvalidConfigurationException(sprintf('The class "%s" is not supported by the doctrine manager. Change the "sonatra_security.group_class" config', $groupClass));
        }

        /* @var EntityRepository $repo */
        $repo = $em->getRepository($groupClass);
        $group = $repo->findOneBy(array('name' => $groupName));

        if (null === $group) {
            throw new InvalidArgumentException(sprintf('The group "%s" does not exist', $groupName));
        }

        if (!$group->hasRole($roleName)) {
            $output->writeln(sprintf('Group "%s" didn\'t have "%s" role.', $groupName, $roleName));

            return;
        }

        $group->removeRole($roleName);

        $errorList = $this->getContainer()->get('validator')->validate($group);

        if (count($errorList) > 0) {
            $msg = sprintf('Validation errors for "%s":%s', get_class($group), PHP_EOL);

            /* @var ConstraintViolationInterface $error */
            foreach ($errorList as $error) {
                $msg = sprintf('%s%s: %s', PHP_EOL, $error->getPropertyPath(), $error->getMessage());
            }

            throw new LogicException($msg);
        }

        $em->persist($group);
        $em->flush();

        $output->writeln(sprintf('Role "%s" has been removed from group "%s".', $roleName, $groupName));
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
