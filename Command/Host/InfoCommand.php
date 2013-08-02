<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Command\Host;

use Sonatra\Bundle\SecurityBundle\Command\InfoCommand as BaseInfoCommand;
use Sonatra\Bundle\SecurityBundle\Core\Token\ConsoleToken;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Core\Role\Role;

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

        $this->setName('security:host:info')
            ->setDescription('Security infos of host role')
            ->setDefinition(array(
                new InputArgument('name', InputArgument::OPTIONAL, 'The name', 'localhost'),
                new InputOption('calc', 'c', InputOption::VALUE_NONE, 'Get all roles of hierarchical role (calculated)')
         ));
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $hostname = $input->getArgument('name');
        $calculated = $input->getOption('calc');
        $allRoles = $this->getHostRoles($hostname);

        // get calculated roles
        if ($calculated) {
            $tokenRoles = array_keys($allRoles);
            $token = new ConsoleToken('key', 'console.', $tokenRoles);
            $identities = $this->getContainer()->get('sonatra.acl.manager')->getSecurityIdentities($token);

            foreach ($identities as $child) {
                if ($child instanceof RoleSecurityIdentity) {
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
        $output->writeln(array('', sprintf('Security context for <info>%s</info> host:', $hostname)));
        $this->renderInfos($output, $roles, 'Roles', 'Contains no associated role', $width, true);
        $this->renderInfos($output, $authorizations, 'Authorizations', 'Contains no associated authorization', $width);
    }
}
