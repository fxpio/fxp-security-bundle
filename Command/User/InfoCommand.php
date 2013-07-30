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

use FOS\UserBundle\Model\GroupInterface;

use Sonatra\Bundle\SecurityBundle\Command\InfoCommand as BaseInfoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class InfoCommand extends BaseInfoCommand
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
        $identityRepo = $doctrine->getManagerForClass($identityClass)->getRepository($identityClass);
        $identity = $identityRepo->findOneBy(array('username' => $identityName));
        $noHost = $input->getOption('no-host');
        $host = $noHost ? null : $input->getOption('host');
        $calculated = $input->getOption('calc');

        if (null === $identity) {
            $identity = new Role($identityName);
        }

        $output->writeln(array('', sprintf('Security context for <info>%s</info> user:', $identity->getUsername())));
        $this->displayInfos($output, $identity, $calculated, $host);
    }

    /**
     * Display infos directly attached on the identity.
     *
     * @param OutputInterface $output
     * @param object          $identity
     * @param boolean         $calculated
     * @param string          $host
     */
    protected function displayInfos(OutputInterface $output, $identity, $calculated = false, $host = null)
    {
        // get all roles
        $allRoles = $this->getHostRoles($host);
        $directRoles = array();
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

            foreach ($identity->getRoles() as $child) {
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

            if ($identity instanceof RoleInterface) {
                $tokenRoles = array_merge($tokenRoles, array($identity));
            }

            $token = new AnonymousToken('key', 'console.', $tokenRoles);
            $identities = $this->getContainer()->get('sonatra.acl.manager')->getIdentities($token);

            foreach ($identities as $child) {
                if ($child instanceof RoleInterface
                        && (!($identity instanceof RoleInterface)
                                || $child->getRole() !== $identity->getRole())) {
                    if (!array_key_exists($child->getRole(), $allRoles)) {
                        $allRoles[$child->getRole()] = 'role hierarchy';
                    }
                }

            }
        }

        // prepare render
        $width = 0;
        $roles = array();
        $authorizations = array();

        foreach ($allRoles as $name => $status) {
            $width = strlen($name) > $width ? strlen($name) : $width;

            if (0 === strpos($name, 'ROLE_')) {
                $roles[] = $name;

                continue;
            }

            $authorizations[] = $name;
        }

        foreach ($groups as $name => $status) {
            $width = strlen($name) > $width ? strlen($name) : $width;
        }

        $roles = $this->sortRecords($roles, $allRoles);
        $authorizations = $this->sortRecords($authorizations, $allRoles);

        // render
        $this->renderInfos($output, $roles, 'Roles', 'Contains no associated role', $width, true);
        $this->renderInfos($output, $authorizations, 'Authorizations', 'Contains no associated authorization', $width);

        $this->renderInfos($output, $groups, 'Groups', 'Contains no associated group', $width, true);
    }
}
