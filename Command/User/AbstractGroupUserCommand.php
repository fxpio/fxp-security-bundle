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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Sonatra\Bundle\SecurityBundle\Exception\UnexpectedTypeException;
use Sonatra\Component\Security\Model\GroupInterface;
use Sonatra\Component\Security\Model\Traits\GroupableInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Sonatra\Component\Security\Exception\InvalidArgumentException;
use Sonatra\Component\Security\Exception\LogicException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractGroupUserCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('username', InputArgument::OPTIONAL, 'The username'),
                new InputArgument('group', InputArgument::OPTIONAL, 'The group'),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // find user
        $userClass = str_replace('/', '\\', $this->getContainer()->getParameter('sonatra_security.user_class'));
        $userName = $input->getArgument('username');
        /* @var EntityManagerInterface $emUser */
        $emUser = $this->getContainer()->get('doctrine')->getManagerForClass($userClass);

        if (null === $emUser) {
            throw new InvalidConfigurationException(sprintf('The class "%s" is not supported by the doctrine manager. Change the "sonatra_security.user_class" config', $userClass));
        }

        /* @var EntityRepository $repoUser */
        $repoUser = $emUser->getRepository($userClass);
        /* @var GroupableInterface $user */
        $user = $repoUser->findOneBy(array('username' => $userName));

        if (null === $user) {
            throw new InvalidArgumentException(sprintf('The user "%s" does not exist', $userName));
        }

        // find group
        $groupClass = str_replace('/', '\\', $this->getContainer()->getParameter('sonatra_security.group_class'));
        $groupName = $input->getArgument('group');
        /* @var EntityManagerInterface $emGroup */
        $emGroup = $this->getContainer()->get('doctrine')->getManagerForClass($groupClass);
        /* @var EntityRepository $repoGroup */
        $repoGroup = $emGroup->getRepository($groupClass);
        /* @var GroupInterface $group */
        $group = $repoGroup->findOneBy(array('name' => $groupName));

        if (null === $group) {
            throw new InvalidArgumentException(sprintf('The group "%s" does not exist', $groupName));
        }

        if (!$this->doExecute($output, $user, $group)) {
            return;
        }

        $errorList = $this->getContainer()->get('validator')->validate($user);

        if (count($errorList) > 0) {
            $msg = sprintf('Validation errors for "%s":%s', get_class($user), PHP_EOL);

            /* @var ConstraintViolationInterface $error */
            foreach ($errorList as $error) {
                $msg = sprintf('%s%s: %s', PHP_EOL, $error->getPropertyPath(), $error->getMessage());
            }

            throw new LogicException($msg);
        }

        $emUser->persist($user);
        $emUser->flush();

        $output->writeln(sprintf($this->getFinishMessage(), $groupName, $userName));
    }

    /**
     * Do execute.
     *
     * @param OutputInterface                  $output
     * @param UserInterface|GroupableInterface $user
     * @param GroupInterface                   $group
     *
     * @return bool
     */
    abstract protected function doExecute(OutputInterface $output, $user, GroupInterface $group);

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
        if (!$input->getArgument('username')) {
            $username = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please choose a username:',
                function ($username) {
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
                function ($group) {
                    if (empty($group)) {
                        throw new LogicException('Group can not be empty');
                    }

                    return $group;
                }
            );

            $input->setArgument('group', $group);
        }
    }

    /**
     * Validate the object.
     *
     * @param object $object            The object instance
     * @param string $expectedInterface The expected interface
     *
     * @return object
     *
     * @throws UnexpectedTypeException When the object does not implement the expected interface
     */
    protected function validateObject($object, $expectedInterface)
    {
        if (is_object($object) && class_exists($expectedInterface)
                && in_array($expectedInterface, class_implements($object))) {
            return $object;
        }

        throw new UnexpectedTypeException($object, $expectedInterface);
    }
}
