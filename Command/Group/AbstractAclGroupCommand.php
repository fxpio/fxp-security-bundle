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

use Doctrine\ORM\EntityRepository;
use FOS\UserBundle\Model\GroupInterface;
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
abstract class AbstractAclGroupCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('group', InputArgument::OPTIONAL, 'The group'),
                new InputArgument('role', InputArgument::OPTIONAL, 'The role'),
            ))
        ;
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
        /* @var GroupInterface $group */
        $group = $repo->findOneBy(array('name' => $groupName));

        if (null === $group) {
            throw new InvalidArgumentException(sprintf('The group "%s" does not exist', $groupName));
        }

        if (!$this->doExecute($output, $group, $roleName)) {
            return;
        }

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

        $output->writeln(sprintf($this->getFinishMessage(), $roleName, $groupName));
    }

    /**
     * Do execute.
     *
     * @param OutputInterface $output
     * @param GroupInterface  $group
     * @param string          $roleName
     *
     * @return bool
     */
    abstract protected function doExecute(OutputInterface $output, GroupInterface $group, $roleName);

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
