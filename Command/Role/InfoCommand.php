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

use Sonatra\Bundle\SecurityBundle\Command\InfoCommand as BaseInfoCommand;
use Sonatra\Bundle\SecurityBundle\Model\RoleHierarchisableInterface;
use Sonatra\Bundle\SecurityBundle\Core\Token\ConsoleToken;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleInterface;

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

        $this->setName('security:role:info')
            ->setDescription('Security infos of role');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $identityClass = str_replace('/', '\\', $this->getContainer()->getParameter('sonatra_security.role_class'));
        $identityName = $input->getArgument('name');
        $identityRepo = $doctrine->getManagerForClass($identityClass)->getRepository($identityClass);
        $identity = $identityRepo->findOneBy(array('name' => $identityName));
        $noHost = $input->getOption('no-host');
        $host = $noHost ? null : $input->getOption('host');
        $calculated = $input->getOption('calc');

        if (null === $identity) {
            $identity = new Role($identityName);
        }

        $output->writeln(array('', sprintf('Security context for <info>%s</info> role:', $identity->getRole())));
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
        $children = array();
        $parents = array();

        if ($identity instanceof RoleHierarchisableInterface) {
            foreach ($identity->getChildren() as $child) {
                $child = ($child instanceof RoleInterface) ? $child->getRole() : $child;
                $children[$child] = 'direct';
            }

            foreach ($identity->getParents() as $parent) {
                $parent = ($parent instanceof RoleInterface) ? $parent->getRole() : $parent;
                $parents[$parent] = 'direct';
            }
        }

        if ($calculated) {
            $tokenRoles = array_keys($allRoles);

            if ($identity instanceof RoleInterface) {
                $tokenRoles = array_merge($tokenRoles, array($identity));
            }

            $token = new ConsoleToken('key', 'console.', $tokenRoles);
            $identities = $this->getContainer()->get('sonatra.acl.manager')->getSecurityIdentities($token);

            foreach ($identities as $child) {
                if ($child instanceof RoleSecurityIdentity
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

        $roles = $this->sortRecords($roles, $allRoles);
        $authorizations = $this->sortRecords($authorizations, $allRoles);

        // render
        $this->renderInfos($output, $roles, 'Roles', 'Contains no associated role', $width, true);
        $this->renderInfos($output, $authorizations, 'Authorizations', 'Contains no associated authorization', $width);

        if ($identity instanceof RoleHierarchisableInterface) {
            $width = 0;

            foreach ($children as $name => $status) {
                $width = strlen($name) > $width ? strlen($name) : $width;
            }

            foreach ($parents as $name => $status) {
                $width = strlen($name) > $width ? strlen($name) : $width;
            }

            if (count($children) > 0 || count($parents) > 0) {
                $output->writeln(array('', '', sprintf('Hierarchy informations for <info>%s</info> role:', $identity->getRole())));
            }

            $this->renderInfos($output, $children, 'Children role', null, $width);
            $this->renderInfos($output, $parents, 'Parents role', null, $width);
        }
    }
}
