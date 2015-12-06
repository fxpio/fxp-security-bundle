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
use Sonatra\Bundle\SecurityBundle\Command\AbstractInfoCommand;
use Sonatra\Bundle\SecurityBundle\Core\Token\ConsoleToken;
use Sonatra\Bundle\SecurityBundle\Exception\InvalidArgumentException;
use Sonatra\Bundle\SecurityBundle\Model\GroupInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class InfoUserCommand extends AbstractInfoCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('security:user:info')
            ->setDescription('Security infos of user');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $identityClass = str_replace('/', '\\', $this->getContainer()->getParameter('sonatra_security.user_class'));
        $identityName = $input->getArgument('name');
        /* @var EntityManagerInterface $em */
        $em = $doctrine->getManagerForClass($identityClass);

        if (null === $em) {
            throw new InvalidConfigurationException(sprintf('The class "%s" is not supported by the doctrine manager. Change the "sonatra_security.user_class" config', $identityClass));
        }

        /* @var EntityRepository $identityRepo */
        $identityRepo = $em->getRepository($identityClass);

        $identity = $identityRepo->findOneBy(array('username' => $identityName));
        $noHost = $input->getOption('no-host');
        $host = $noHost ? null : $input->getOption('host');
        $calculated = $input->getOption('calc');

        if (null === $identity) {
            throw new InvalidArgumentException(sprintf('User instance "%s" on "%s" not found', $identityName, $identityClass));
        }

        $output->writeln(array('', sprintf('Security context for <info>%s</info> user:', $identity->getUsername())));
        $this->displayInfos($output, $identity, $calculated, $host);
    }

    /**
     * Display infos directly attached on the identity.
     *
     * @param OutputInterface $output
     * @param object          $identity
     * @param bool            $calculated
     * @param string          $host
     */
    protected function displayInfos(OutputInterface $output, $identity, $calculated = false, $host = null)
    {
        // get all roles
        $allRoles = $this->getHostRoles($host);
        $groups = array();
        $groupRoles = array();

        if (method_exists($identity, 'getGroups')) {
            foreach ($identity->getGroups() as $child) {
                if ($child instanceof GroupInterface) {
                    $groupRoles = array_merge($groupRoles, $child->getRoles());
                    $child = ($child instanceof GroupInterface) ? $child->getName() : $child;
                }

                $groups[$child] = 'direct';
            }
        }

        if (method_exists($identity, 'getRoles')) {
            $directRoles = $identity->getRoles();

            foreach ($directRoles as $child) {
                $child = ($child instanceof RoleInterface) ? $child->getRole() : $child;
                $allRoles[$child] = 'direct';

                if (in_array($child, $groupRoles)) {
                    $allRoles[$child] = 'role group';

                    continue;
                }
            }
        }

        if ($calculated) {
            $tokenRoles = array_keys($allRoles);
            $token = new ConsoleToken('key', 'console.', $tokenRoles);
            $roles = $this->getContainer()->get('security.role_hierarchy')->getReachableRoles($token->getRoles());

            foreach ($roles as $role) {
                if ($role instanceof RoleInterface) {
                    if (!array_key_exists($role->getRole(), $allRoles)) {
                        $allRoles[$role->getRole()] = 'role hierarchy';
                    }
                }
            }
        }

        // prepare render
        $width = 0;
        $roles = array();

        foreach ($allRoles as $name => $status) {
            $width = strlen($name) > $width ? strlen($name) : $width;
            $roles[] = $name;
        }

        foreach ($groups as $name => $status) {
            $width = strlen($name) > $width ? strlen($name) : $width;
        }

        $roles = $this->sortRecords($roles, $allRoles);

        // render
        $this->renderInfos($output, $roles, 'Roles', 'Contains no associated role', $width, true);
        $this->renderInfos($output, $groups, 'Groups', 'Contains no associated group', $width, true);
    }
}
